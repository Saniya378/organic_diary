<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmedBuyerMail;
use App\Mail\OrderDeliveredBuyerMail;
use App\Mail\OrderStatusUpdatedBuyerMail;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display seller's orders list with status filtering.
     */
    public function index(Request $request): View
    {
        $sellerId = Auth::id();
        $statusFilter = $request->query('status');

        $query = Order::where('seller_id', $sellerId)
            ->with(['buyer', 'items', 'orderGroup'])
            ->latest();

        if ($statusFilter && in_array($statusFilter, ['placed', 'new', 'confirmed', 'packing', 'in_transit', 'delivered', 'cancelled'])) {
            if ($statusFilter === 'confirmed') {
                $query->whereIn('status', ['new', 'confirmed']);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('seller.orders.index', [
            'orders' => $orders,
            'currentStatus' => $statusFilter,
        ]);
    }

    /**
     * Display single order detail page for seller.
     */
    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load([
            'buyer',
            'orderGroup.payment',
            'items.product.images',
            'items.variant',
            'statusHistories.changedByUser',
        ]);

        $nextStatus = $this->determineNextStatus($order->status);

        return view('seller.orders.show', [
            'order' => $order,
            'nextStatus' => $nextStatus,
        ]);
    }

    /**
     * Dedicated action to confirm an order directly by seller.
     */
    public function confirm(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('update', $order);

        if ($order->status === 'cancelled') {
            return back()->with('error', "Order #{$order->order_no} is cancelled and cannot be confirmed.");
        }

        if (in_array($order->status, ['confirmed', 'packing', 'in_transit', 'delivered'])) {
            return back()->with('info', "Order #{$order->order_no} is already confirmed.");
        }

        $request->merge(['target_status' => 'confirmed']);
        return $this->advanceStatus($request, $order);
    }

    /**
     * Advance order status one step at a time with history logging, COD settlement, and buyer email.
     */
    public function advanceStatus(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('update', $order);

        $targetStatus = $request->input('target_status');
        $expectedNext = $this->determineNextStatus($order->status);

        if (!$expectedNext || $targetStatus !== $expectedNext) {
            return back()->with('error', "Invalid status progression from '{$order->status}' to '{$targetStatus}'. Status must advance sequentially.");
        }

        // Validate rider details when moving to in_transit
        if ($targetStatus === 'in_transit') {
            $request->validate([
                'rider_name' => ['required', 'string', 'max:255'],
                'rider_phone' => ['required', 'string', 'max:25'],
            ]);
        }

        $note = $request->input('note');

        DB::transaction(function () use ($order, $targetStatus, $request, $note) {
            // If advancing pending_payment order directly to confirmed, decrement stock if not yet decremented
            if ($order->status === 'pending_payment') {
                $order->loadMissing('items');
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                        if ($product) {
                            $product->decrement('stock', $item->quantity);
                            $product->fresh()->checkAndDispatchLowStockAlert();
                        }
                    }
                    if ($item->variant_id) {
                        $variant = ProductVariant::where('id', $item->variant_id)->lockForUpdate()->first();
                        if ($variant) {
                            $variant->decrement('stock', $item->quantity);
                        }
                    }
                }
            }

            $updateData = [
                'status' => $targetStatus,
            ];

            if ($targetStatus === 'in_transit') {
                $updateData['rider_name'] = $request->input('rider_name');
                $updateData['rider_phone'] = $request->input('rider_phone');
            }

            // COD settlement when delivered
            if ($targetStatus === 'delivered' && $order->payment_status === 'unpaid') {
                $updateData['payment_status'] = 'paid';

                // Also update Payment record if COD
                if ($order->orderGroup) {
                    Payment::where('order_group_id', $order->order_group_id)
                        ->where('method', 'cod')
                        ->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                }
            }

            $order->update($updateData);

            // Audit history
            $defaultNotes = [
                'confirmed' => 'Seller confirmed batch availability and scheduled preparation.',
                'packing' => 'Packed in cold-chain insulated packaging at the farm.',
                'in_transit' => "Dispatched with delivery rider {$request->input('rider_name')} ({$request->input('rider_phone')}).",
                'delivered' => 'Package successfully delivered and handed to customer.',
            ];

            $historyNote = $note ?: ($defaultNotes[$targetStatus] ?? 'Status updated by seller.');

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $targetStatus,
                'note' => $historyNote,
                'changed_by' => Auth::id(),
            ]);

            // Eager load full relations for notifications and emails
            $order->load(['buyer', 'seller.sellerProfile', 'orderGroup.payment', 'items.product', 'items.variant']);

            $farmName = $order->seller?->sellerProfile?->farm_name ?? $order->seller?->name ?? 'Farm';

            // Real-time In-App Notification to Buyer
            try {
                $statusIcons = [
                    'confirmed' => 'bi-check-circle-fill',
                    'packing' => 'bi-box-seam-fill',
                    'in_transit' => 'bi-truck',
                    'delivered' => 'bi-house-check-fill',
                ];

                $statusTitle = match ($targetStatus) {
                    'confirmed' => "Order #{$order->order_no} Confirmed by {$farmName}!",
                    'packing' => "Order #{$order->order_no} is Being Packed by {$farmName}",
                    'in_transit' => "Order #{$order->order_no} is On The Way!",
                    'delivered' => "Order #{$order->order_no} Delivered Successfully!",
                    default => "Order #{$order->order_no} Status Updated",
                };

                $notificationBody = match ($targetStatus) {
                    'confirmed' => "Good news! {$farmName} has verified harvest availability and confirmed your order (#{$order->order_no}). Cold-chain preparation has started.",
                    default => $historyNote,
                };

                \App\Models\Notification::sendTo(
                    $order->buyer_id,
                    $statusTitle,
                    $notificationBody,
                    route('orders.show', $order->id),
                    'order_' . $targetStatus,
                    $statusIcons[$targetStatus] ?? 'bi-bell-fill'
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Notification error on order status update: ' . $e->getMessage());
            }

            // Email to Buyer
            try {
                if ($order->buyer && $order->buyer->email) {
                    if ($targetStatus === 'confirmed') {
                        Mail::to($order->buyer->email)->send(
                            new \App\Mail\OrderConfirmedBuyerMail($order, $historyNote)
                        );
                        \Illuminate\Support\Facades\Log::info("OrderConfirmedBuyerMail sent to {$order->buyer->email} for Order #{$order->order_no}");
                    } elseif ($targetStatus === 'delivered') {
                        Mail::to($order->buyer->email)->send(
                            new \App\Mail\OrderDeliveredBuyerMail($order, $historyNote)
                        );
                        \Illuminate\Support\Facades\Log::info("OrderDeliveredBuyerMail sent to {$order->buyer->email} for Order #{$order->order_no}");
                    } else {
                        Mail::to($order->buyer->email)->send(
                            new OrderStatusUpdatedBuyerMail($order, $targetStatus, $historyNote)
                        );
                        \Illuminate\Support\Facades\Log::info("OrderStatusUpdatedBuyerMail sent to {$order->buyer->email} for Order #{$order->order_no} ({$targetStatus})");
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send order status email for Order #{$order->order_no}: " . $e->getMessage());
            }
        });

        $statusLabel = ucfirst(str_replace('_', ' ', $targetStatus));
        return back()->with('success', "Order #{$order->order_no} status updated to '{$statusLabel}'. Customer notification and confirmation email sent.");
    }

    /**
     * Cancel an order by the seller (e.g. out of stock or batch damaged).
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('update', $order);

        if (in_array($order->status, ['in_transit', 'delivered', 'cancelled'])) {
            return back()->with('error', "Order #{$order->order_no} cannot be cancelled because it is already {$order->status}.");
        }

        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:500'],
        ]);

        $seller = Auth::user();
        $farmName = $seller->sellerProfile->farm_name ?? $seller->name;
        $reason = $request->input('cancel_reason');

        DB::transaction(function () use ($order, $seller, $farmName, $reason) {
            $order->load('items');

            // 1. Restore Stock if decremented
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    $product = \App\Models\Product::where('id', $item->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }

                if ($item->variant_id) {
                    $variant = \App\Models\ProductVariant::where('id', $item->variant_id)->lockForUpdate()->first();
                    if ($variant) {
                        $variant->increment('stock', $item->quantity);
                    }
                }
            }

            // 2. Update Order
            $order->update([
                'status' => 'cancelled',
                'cancel_reason' => "Cancelled by Seller ({$farmName}): {$reason}",
            ]);

            // 3. Status History Log
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'cancelled',
                'note' => "Order cancelled by Seller ({$farmName}). Reason: {$reason}",
                'changed_by' => $seller->id,
            ]);

            // 4. Send real-time notification to buyer
            try {
                \App\Models\Notification::sendTo(
                    $order->buyer_id,
                    "Order #{$order->order_no} Cancelled by Seller",
                    "The seller ({$farmName}) cancelled your order. Reason: {$reason}",
                    route('orders.show', $order->id),
                    'order_cancelled',
                    'bi-x-circle-fill'
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Notification error on seller order cancellation: ' . $e->getMessage());
            }

            // 5. Send email notification to buyer
            try {
                if ($order->buyer && $order->buyer->email) {
                    Mail::to($order->buyer->email)->send(
                        new OrderStatusUpdatedBuyerMail($order, 'cancelled', "Order was cancelled by the seller ({$farmName}). Reason: {$reason}")
                    );
                }
            } catch (\Exception $e) {
                // Log and continue
            }
        });

        return back()->with('success', "Order #{$order->order_no} has been cancelled by seller. Customer has been notified.");
    }

    /**
     * Determine the next valid status in sequence.
     */
    protected function determineNextStatus(string $currentStatus): ?string
    {
        return match ($currentStatus) {
            'placed', 'new', 'pending_payment' => 'confirmed',
            'confirmed' => 'packing',
            'packing' => 'in_transit',
            'in_transit' => 'delivered',
            default => null,
        };
    }

    /**
     * Display printable PDF-ready packing slip & invoice for seller.
     */
     public function invoice(Order $order): View
     {
         Gate::authorize('view', $order);

         $order->load(['orderGroup.payment', 'items.product', 'items.variant', 'seller.sellerProfile', 'buyer']);

         return view('seller.orders.invoice', [
             'order' => $order,
         ]);
     }

    /**
     * Approve and verify customer payment directly by the Seller.
     */
    public function approvePayment(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('update', $order);

        if ($order->payment_status === 'paid') {
            return back()->with('info', 'This payment is already verified and marked as Paid.');
        }

        DB::transaction(function () use ($request, $order) {
            $order->load(['items.product', 'items.variant', 'orderGroup.payment']);
            $orderGroup = $order->orderGroup;
            $payment = $orderGroup ? $orderGroup->payment : null;

            // If order was waiting for payment, adjust inventory and move to placed
            if ($order->status === 'pending_payment') {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                        if ($product) {
                            $product->decrement('stock', $item->quantity);
                            $product->fresh()->checkAndDispatchLowStockAlert();
                        }
                    }
                    if ($item->variant_id) {
                        $variant = ProductVariant::where('id', $item->variant_id)->lockForUpdate()->first();
                        if ($variant) {
                            $variant->decrement('stock', $item->quantity);
                        }
                    }
                }

                $order->update([
                    'status' => 'placed',
                    'payment_status' => 'paid',
                ]);
            } else {
                $order->update([
                    'payment_status' => 'paid',
                ]);
            }

            // Update parent payment record
            if ($payment) {
                $allOrdersPaid = $orderGroup->orders()->where('payment_status', '!=', 'paid')->where('id', '!=', $order->id)->count() === 0;
                if ($allOrdersPaid) {
                    $payment->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'verified_by' => Auth::id(),
                        'verified_at' => now(),
                        'admin_notes' => 'Payment verified directly by seller: ' . Auth::user()->name,
                    ]);
                }
            }

            $sellerFarmName = Auth::user()->sellerProfile->farm_name ?? Auth::user()->name;

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'note' => "Payment of Rs " . number_format($order->total) . " verified and accepted directly by seller ({$sellerFarmName}). TRX ID: " . ($payment->transaction_id ?? $payment->gateway_ref ?? 'N/A'),
                'changed_by' => Auth::id(),
            ]);

            // Real-time Notification to Buyer
            try {
                \App\Models\Notification::sendTo(
                    $order->buyer_id,
                    "Payment Verified by {$sellerFarmName}",
                    "Your payment of Rs " . number_format($order->total) . " for Order #{$order->order_no} was verified & accepted by the farm! Fresh harvest preparation has begun.",
                    route('orders.show', $order->id),
                    'payment_approved',
                    'bi-patch-check-fill'
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Seller payment approval notification error: ' . $e->getMessage());
            }

            // Send confirmation email to buyer
            try {
                if ($orderGroup) {
                    app(CheckoutService::class)->sendOrderConfirmationEmails($orderGroup);
                }
            } catch (\Exception $e) {}
        });

        return back()->with('success', "Payment for Order #{$order->order_no} has been verified and confirmed successfully!");
    }

    /**
     * Reject customer payment transaction directly by the Seller.
     */
    public function rejectPayment(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('update', $order);

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $reason = $request->input('rejection_reason');

        DB::transaction(function () use ($order, $reason) {
            $order->update([
                'payment_status' => 'rejected',
            ]);

            $orderGroup = $order->orderGroup;
            if ($orderGroup && $orderGroup->payment) {
                $orderGroup->payment->update([
                    'status' => 'rejected',
                    'admin_notes' => "Rejected by Seller: " . $reason,
                    'verified_by' => Auth::id(),
                    'verified_at' => now(),
                ]);
            }

            $sellerFarmName = Auth::user()->sellerProfile->farm_name ?? Auth::user()->name;

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'note' => "Payment verification rejected by seller ({$sellerFarmName}). Reason: {$reason}",
                'changed_by' => Auth::id(),
            ]);

            // Notification to Buyer
            try {
                \App\Models\Notification::sendTo(
                    $order->buyer_id,
                    "Payment Receipt Rejected for Order #{$order->order_no}",
                    "{$sellerFarmName} could not verify your payment receipt: {$reason}. Please re-upload a valid transaction slip.",
                    route('orders.show', $order->id),
                    'payment_rejected',
                    'bi-x-circle-fill'
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Seller payment rejection notification error: ' . $e->getMessage());
            }
        });

        return back()->with('success', "Payment for Order #{$order->order_no} was rejected. The customer has been notified to re-submit proof.");
    }
}
