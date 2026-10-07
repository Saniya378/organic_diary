<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderGroup;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of buyer's order groups.
     */
    public function index(): View
    {
        $orderGroups = OrderGroup::where('user_id', Auth::id())
            ->with([
                'orders.items.product',
                'orders.items.variant',
                'orders.seller.sellerProfile',
                'payment',
            ])
            ->latest()
            ->paginate(10);

        return view('orders.index', [
            'orderGroups' => $orderGroups,
        ]);
    }

    /**
     * Display specific sub-order details.
     */
    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load([
            'orderGroup.payment',
            'items.product.images',
            'items.variant',
            'seller.sellerProfile',
            'statusHistories.changedByUser',
        ]);

        $canCancel = in_array($order->status, ['placed', 'new', 'confirmed']);

        return view('orders.show', [
            'order' => $order,
            'canCancel' => $canCancel,
        ]);
    }

    /**
     * Display the Track Order page with vertical timeline, rider card, and auto-refresh.
     */
    public function track(Request $request, ?string $orderNo = null): View|RedirectResponse
    {
        $searchedOrderNo = $orderNo ?? $request->input('order_no');

        if (!$searchedOrderNo) {
            return view('orders.track', [
                'order' => null,
                'timeline' => [],
                'canCancel' => false,
            ]);
        }

        $order = Order::where('order_no', trim($searchedOrderNo))
            ->with([
                'orderGroup.payment',
                'items.product.images',
                'items.variant',
                'seller.sellerProfile',
                'statusHistories.changedByUser',
            ])
            ->first();

        if (!$order) {
            return view('orders.track', [
                'order' => null,
                'timeline' => [],
                'canCancel' => false,
                'searchedOrderNo' => $searchedOrderNo,
                'errorMessage' => "No order found with reference '{$searchedOrderNo}'. Please check your receipt or order history.",
            ]);
        }

        // Authorize if logged in
        if (Auth::check()) {
            Gate::authorize('view', $order);
        }

        $timeline = $this->buildTimelineData($order);
        $canCancel = in_array($order->status, ['placed', 'new', 'confirmed']);

        return view('orders.track', [
            'order' => $order,
            'timeline' => $timeline,
            'canCancel' => $canCancel,
            'searchedOrderNo' => $searchedOrderNo,
        ]);
    }

    /**
     * Real-time tracking status API endpoint for live polling and dynamic updates.
     */
    public function trackStatus(Order $order): \Illuminate\Http\JsonResponse
    {
        $order->load([
            'orderGroup.payment',
            'seller.sellerProfile',
            'statusHistories.changedByUser',
        ]);

        $timeline = $this->buildTimelineData($order);

        $farmName = $order->seller->sellerProfile->farm_name ?? $order->seller->name ?? 'Himalayan Producer Farm';
        $isCancelledBySeller = $order->isCancelledBySeller();
        $isCancelledByAdmin = $order->isCancelledByAdmin();
        $isCancelledByBuyer = $order->isCancelledByBuyer();

        $cancelledByText = null;
        if ($order->isCancelled()) {
            if ($isCancelledBySeller) {
                $cancelledByText = "Seller ({$farmName})";
            } elseif ($isCancelledByAdmin) {
                $cancelledByText = "Admin";
            } else {
                $cancelledByText = "Buyer";
            }
        }

        $payment = $order->orderGroup?->payment;

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_no' => $order->order_no,
            'status' => $order->status,
            'status_label' => ucfirst(str_replace('_', ' ', $order->status)),
            'payment_status' => $order->payment_status,
            'payment_status_label' => ucfirst(str_replace('_', ' ', $order->payment_status)),
            'payment_method' => $order->orderGroup?->payment_method ?? 'cod',
            'is_cancelled' => $order->isCancelled(),
            'is_cancelled_by_seller' => $isCancelledBySeller,
            'is_cancelled_by_admin' => $isCancelledByAdmin,
            'is_cancelled_by_buyer' => $isCancelledByBuyer,
            'cancelled_by_text' => $cancelledByText,
            'cancel_reason' => $order->cancel_reason,
            'farm_name' => $farmName,
            'rider_name' => $order->rider_name,
            'rider_phone' => $order->rider_phone,
            'can_cancel' => in_array($order->status, ['placed', 'new', 'confirmed']),
            'timeline' => $timeline,
            'updated_at' => now()->format('h:i:s A'),
            'payment_details' => $payment ? [
                'status' => $payment->status,
                'status_label' => ucfirst(str_replace('_', ' ', $payment->status)),
                'method' => $payment->method,
                'channel' => $payment->payment_channel,
                'transaction_id' => $payment->transaction_id ?? $payment->gateway_ref,
                'admin_notes' => $payment->admin_notes,
                'is_under_review' => $payment->isUnderReview(),
                'is_rejected' => $payment->isRejected(),
                'is_paid' => $payment->isPaid(),
            ] : null,
        ]);
    }

    /**
     * Re-submit payment transaction ID and screenshot proof if rejected.
     */
    public function resubmitPayment(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('view', $order);

        $request->validate([
            'payment_channel' => ['required', 'string', 'max:50'],
            'sender_name' => ['required', 'string', 'max:100'],
            'sender_account' => ['nullable', 'string', 'max:50'],
            'transaction_id' => ['required', 'string', 'max:100'],
            'proof_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $proofImagePath = null;
        if ($request->hasFile('proof_image')) {
            $proofImagePath = $request->file('proof_image')->store('payment_proofs', 'public');
        }

        $orderGroup = $order->orderGroup;
        $payment = $orderGroup->payment;

        if (!$payment) {
            $payment = \App\Models\Payment::create([
                'order_group_id' => $orderGroup->id,
                'method' => 'bank_transfer',
                'amount' => $orderGroup->grand_total,
                'status' => 'pending',
            ]);
        }

        $payment->update([
            'payment_channel' => $request->input('payment_channel'),
            'sender_name' => $request->input('sender_name'),
            'sender_account' => $request->input('sender_account'),
            'transaction_id' => $request->input('transaction_id'),
            'gateway_ref' => $request->input('transaction_id'),
            'proof_image' => $proofImagePath ?? $payment->proof_image,
            'status' => 'under_review',
            'admin_notes' => null,
        ]);

        foreach ($orderGroup->orders as $o) {
            $o->update([
                'payment_status' => 'under_review',
            ]);

            OrderStatusHistory::create([
                'order_id' => $o->id,
                'status' => $o->status,
                'note' => 'Buyer re-submitted payment proof via ' . ucfirst(str_replace('_', ' ', $request->input('payment_channel'))) . ' (TRX ID: ' . $request->input('transaction_id') . '). Status: Under Admin Review.',
                'changed_by' => Auth::id(),
            ]);
        }

        return back()->with('success', 'Payment proof (TRX ID: ' . $request->input('transaction_id') . ') re-submitted successfully. Admin will verify shortly.');
    }

    /**
     * Cancel an eligible order and restore stock immediately.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('cancel', $order);

        if (!in_array($order->status, ['placed', 'new', 'confirmed'])) {
            return back()->with('error', "Order #{$order->order_no} cannot be cancelled because it is already {$order->status}.");
        }

        $reason = $request->input('cancel_reason', 'Cancelled by customer');

        DB::transaction(function () use ($order, $reason) {
            $order->load('items');

            // 1. Restore Stock
            foreach ($order->items as $item) {
                if ($item->product_id) {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }

                if ($item->variant_id) {
                    $variant = ProductVariant::where('id', $item->variant_id)->lockForUpdate()->first();
                    if ($variant) {
                        $variant->increment('stock', $item->quantity);
                    }
                }
            }

            // 2. Update Order Status
            $order->update([
                'status' => 'cancelled',
                'cancel_reason' => $reason,
            ]);

            // 3. Save History Audit Log
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'cancelled',
                'note' => 'Order cancelled by customer: ' . $reason . ' (Stock restored to farm inventory)',
                'changed_by' => Auth::id() ?? $order->buyer_id,
            ]);

            // 4. Send real-time notification to seller
            try {
                \App\Models\Notification::sendTo(
                    $order->seller_id,
                    "Order #{$order->order_no} Cancelled by Customer",
                    "Customer cancelled order #{$order->order_no}. Reason: {$reason}. Inventory was automatically restored.",
                    route('seller.orders.show', $order->id),
                    'order_cancelled',
                    'bi-x-circle-fill'
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Notification error on buyer order cancellation: ' . $e->getMessage());
            }
        });

        return back()->with('success', "Order #{$order->order_no} has been cancelled successfully and product stock has been restored.");
    }

    /**
     * Build the 5-step vertical timeline data with timestamps from order_status_history.
     */
    protected function buildTimelineData(Order $order): array
    {
        // 5 standard delivery stages: Placed, Confirmed, Packed, Out for Delivery, Delivered
        $stageDefinitions = [
            [
                'key' => 'placed',
                'title' => 'Order Placed',
                'desc' => 'Order received and registered with Himalayan dairy cooperative.',
                'icon' => 'bi-bag-check-fill',
                'status_keys' => ['placed', 'pending_payment'],
            ],
            [
                'key' => 'confirmed',
                'title' => 'Order Confirmed',
                'desc' => 'Farm approved the batch availability and scheduled fresh morning harvesting.',
                'icon' => 'bi-clipboard-check-fill',
                'status_keys' => ['new', 'confirmed'],
            ],
            [
                'key' => 'packing',
                'title' => 'Packed in Cold-Storage',
                'desc' => 'Dairy packaged in insulated ice-pack boxes directly at the local valley farm.',
                'icon' => 'bi-box-seam-fill',
                'status_keys' => ['packing'],
            ],
            [
                'key' => 'in_transit',
                'title' => 'Out for Delivery',
                'desc' => 'Rider is on route to your address in Gilgit-Baltistan with temperature controlled carrier.',
                'icon' => 'bi-truck',
                'status_keys' => ['in_transit'],
            ],
            [
                'key' => 'delivered',
                'title' => 'Delivered to Doorstep',
                'desc' => 'Package successfully received and signed by customer.',
                'icon' => 'bi-house-check-fill',
                'status_keys' => ['delivered'],
            ],
        ];

        // Map status history events by status
        $historyByStatus = [];
        foreach ($order->statusHistories as $history) {
            $historyByStatus[$history->status] = $history;
        }

        // Determine current progress index
        $statusProgressOrder = [
            'pending_payment' => 0,
            'placed' => 0,
            'new' => 1,
            'confirmed' => 1,
            'packing' => 2,
            'in_transit' => 3,
            'delivered' => 4,
            'cancelled' => -1,
        ];

        $currentProgress = $statusProgressOrder[$order->status] ?? 0;
        $isCancelled = ($order->status === 'cancelled');

        $timeline = [];
        foreach ($stageDefinitions as $index => $stage) {
            $isCompleted = (!$isCancelled && $index <= $currentProgress);
            $isActive = (!$isCancelled && $index === $currentProgress);

            // Find matching history timestamp
            $timestamp = null;
            $note = null;
            foreach ($stage['status_keys'] as $sk) {
                if (isset($historyByStatus[$sk])) {
                    $timestamp = $historyByStatus[$sk]->created_at->format('M d, Y - h:i A');
                    $note = $historyByStatus[$sk]->note;
                    break;
                }
            }

            // Fallback timestamp for completed initial step
            if ($isCompleted && !$timestamp && $index === 0) {
                $timestamp = $order->created_at->format('M d, Y - h:i A');
            }

            $timeline[] = [
                'key' => $stage['key'],
                'title' => $stage['title'],
                'description' => $stage['desc'],
                'icon' => $stage['icon'],
                'completed' => $isCompleted,
                'active' => $isActive,
                'timestamp' => $timestamp,
                'note' => $note,
            ];
        }

        return [
            'stages' => $timeline,
            'is_cancelled' => $isCancelled,
            'cancel_reason' => $order->cancel_reason,
            'cancel_history' => $historyByStatus['cancelled'] ?? null,
            'current_status' => $order->status,
        ];
    }

    /**
     * Display printable PDF-ready invoice for a single sub-order.
     */
    public function invoice(Order $order): \Illuminate\View\View
    {
        if (Auth::id() !== $order->buyer_id && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access to invoice.');
        }

        $order->load(['orderGroup.payment', 'items.product', 'items.variant', 'seller.sellerProfile', 'buyer']);

        return view('invoices.order', ['order' => $order]);
    }

    /**
     * Display printable PDF-ready combined master receipt for multi-vendor order group.
     */
    public function groupInvoice(\App\Models\OrderGroup $orderGroup): \Illuminate\View\View
    {
        if (Auth::id() !== $orderGroup->user_id && Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access to order receipt.');
        }

        $orderGroup->load(['orders.items.product', 'orders.items.variant', 'orders.seller.sellerProfile', 'payment', 'user']);

        return view('invoices.group', ['orderGroup' => $orderGroup]);
    }
}

