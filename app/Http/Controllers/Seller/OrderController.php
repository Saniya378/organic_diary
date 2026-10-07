<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusUpdatedBuyerMail;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
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

            // Real-time Notification to Buyer
            try {
                $statusIcons = [
                    'confirmed' => 'bi-check-circle-fill',
                    'packing' => 'bi-box-seam-fill',
                    'in_transit' => 'bi-truck',
                    'delivered' => 'bi-house-check-fill',
                ];
                $statusTitle = match ($targetStatus) {
                    'confirmed' => "Order #{$order->order_no} Confirmed by Farm",
                    'packing' => "Order #{$order->order_no} is Being Packed",
                    'in_transit' => "Order #{$order->order_no} is On The Way!",
                    'delivered' => "Order #{$order->order_no} Delivered Successfully!",
                    default => "Order #{$order->order_no} Status Updated",
                };

                \App\Models\Notification::sendTo(
                    $order->buyer_id,
                    $statusTitle,
                    $historyNote,
                    route('orders.show', $order->id),
                    'order_' . $targetStatus,
                    $statusIcons[$targetStatus] ?? 'bi-bell-fill'
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Notification error on order status update: ' . $e->getMessage());
            }

            // Email buyer
            try {
                if ($order->buyer && $order->buyer->email) {
                    if ($targetStatus === 'delivered') {
                        $order->load(['items.product', 'seller.sellerProfile', 'buyer']);
                        Mail::to($order->buyer->email)->send(
                            new \App\Mail\OrderDeliveredBuyerMail($order, $historyNote)
                        );
                    } else {
                        Mail::to($order->buyer->email)->send(
                            new OrderStatusUpdatedBuyerMail($order, $targetStatus, $historyNote)
                        );
                    }
                }
            } catch (\Exception $e) {
                // Log and continue
            }
        });

        $statusLabel = ucfirst(str_replace('_', ' ', $targetStatus));
        return back()->with('success', "Order #{$order->order_no} status updated to '{$statusLabel}'. Customer notification sent.");
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
            'placed', 'new' => 'confirmed',
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
}
