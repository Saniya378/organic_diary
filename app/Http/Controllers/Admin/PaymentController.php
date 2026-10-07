<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(protected CheckoutService $checkoutService)
    {
    }

    /**
     * Display a listing of payment transactions with filters.
     */
    public function index(Request $request): View
    {
        $methodFilter = $request->query('method');
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $query = Payment::with(['orderGroup.user', 'orderGroup.orders', 'verifiedByUser'])->latest();

        if ($methodFilter && in_array($methodFilter, ['cod', 'bank_transfer', 'wallet', 'card'])) {
            $query->where(function ($q) use ($methodFilter) {
                $q->where('method', $methodFilter)
                  ->orWhere('payment_channel', $methodFilter);
            });
        }

        if ($statusFilter && in_array($statusFilter, ['under_review', 'paid', 'pending', 'rejected', 'failed', 'refunded'])) {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('gateway_ref', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhere('sender_account', 'like', "%{$search}%")
                  ->orWhere('last4', 'like', "%{$search}%")
                  ->orWhereHas('orderGroup', function ($ogq) use ($search) {
                      $ogq->where('group_no', 'like', "%{$search}%")
                          ->orWhereHas('user', function ($uq) use ($search) {
                              $uq->where('name', 'like', "%{$search}%")
                                 ->orWhere('email', 'like', "%{$search}%")
                                 ->orWhere('phone', 'like', "%{$search}%");
                          });
                  });
            });
        }

        $payments = $query->paginate(15)->withQueryString();

        // Calculate summary totals
        $totalPaidSum = Payment::where('status', 'paid')->sum('amount');
        $totalUnderReviewCount = Payment::where('status', 'under_review')->count();
        $totalUnderReviewSum = Payment::where('status', 'under_review')->sum('amount');
        $totalPendingSum = Payment::where('status', 'pending')->sum('amount');

        return view('admin.payments.index', [
            'payments' => $payments,
            'currentMethod' => $methodFilter,
            'currentStatus' => $statusFilter,
            'search' => $search,
            'totalPaidSum' => $totalPaidSum,
            'totalUnderReviewCount' => $totalUnderReviewCount,
            'totalUnderReviewSum' => $totalUnderReviewSum,
            'totalPendingSum' => $totalPendingSum,
        ]);
    }

    /**
     * Approve and verify a payment submission.
     */
    public function approve(Request $request, Payment $payment): RedirectResponse
    {
        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($payment->status === 'paid') {
            return back()->with('info', 'This payment is already verified and marked as Paid.');
        }

        DB::transaction(function () use ($request, $payment) {
            $orderGroup = $payment->orderGroup;
            $orderGroup->load(['orders.items.product', 'orders.items.variant']);

            // Update payment record
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'admin_notes' => $request->input('admin_notes', 'Payment verified and approved by Admin.'),
            ]);

            // Update all orders in group & decrement stock if needed
            foreach ($orderGroup->orders as $order) {
                if ($order->status === 'pending_payment') {
                    // Lock and decrement stock
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

                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'note' => 'Payment of Rs ' . number_format($payment->amount) . ' approved by Admin (' . Auth::user()->name . '). Transaction ID: ' . ($payment->transaction_id ?? $payment->gateway_ref ?? 'N/A'),
                    'changed_by' => Auth::id(),
                ]);
            }

            // Send real-time notifications
            try {
                \App\Models\Notification::sendTo(
                    $orderGroup->user_id,
                    "Payment Approved by Admin",
                    "Your payment of Rs " . number_format($payment->amount) . " for Order Group #{$orderGroup->group_no} was verified & approved!",
                    route('orders.index'),
                    'payment_approved',
                    'bi-patch-check-fill'
                );

                foreach ($orderGroup->orders as $order) {
                    \App\Models\Notification::sendTo(
                        $order->seller_id,
                        "Payment Verified for Order #{$order->order_no}",
                        "Admin approved buyer payment. You can now prepare and pack this order.",
                        route('seller.orders.show', $order->id),
                        'payment_approved',
                        'bi-check-circle-fill'
                    );
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Payment approval notification error: ' . $e->getMessage());
            }

            // Send confirmation emails to buyer and sellers
            $this->checkoutService->sendOrderConfirmationEmails($orderGroup);
        });

        return back()->with('success', "Payment for Order Group #{$payment->orderGroup->group_no} has been APPROVED and marked as Paid.");
    }

    /**
     * Reject a payment submission with a specific note/reason.
     */
    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $payment) {
            $orderGroup = $payment->orderGroup;
            $orderGroup->load('orders');

            $reason = $request->input('rejection_reason');

            // Update payment record
            $payment->update([
                'status' => 'rejected',
                'admin_notes' => $reason,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            // Update all orders in group
            foreach ($orderGroup->orders as $order) {
                $order->update([
                    'payment_status' => 'rejected',
                ]);

                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'note' => 'Payment verification rejected by Admin (' . Auth::user()->name . '). Reason: ' . $reason,
                    'changed_by' => Auth::id(),
                ]);
            }

            // Send notification to buyer
            try {
                \App\Models\Notification::sendTo(
                    $orderGroup->user_id,
                    "Payment Verification Action Required",
                    "Your payment proof for Order Group #{$orderGroup->group_no} was rejected (Reason: {$reason}). Please resubmit or switch to COD.",
                    route('checkout.payment', $orderGroup->id),
                    'payment_rejected',
                    'bi-exclamation-triangle-fill'
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Payment rejection notification error: ' . $e->getMessage());
            }
        });

        return back()->with('warning', "Payment for Order Group #{$payment->orderGroup->group_no} has been REJECTED. The buyer can now re-submit or switch to Cash on Delivery.");
    }
}

