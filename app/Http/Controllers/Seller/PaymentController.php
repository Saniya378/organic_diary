<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * Display seller's customer payments list for verification and monitoring.
     */
    public function index(Request $request): View
    {
        $sellerId = Auth::id();
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $query = Order::where('seller_id', $sellerId)
            ->with(['buyer', 'orderGroup.payment', 'items'])
            ->latest();

        if ($statusFilter && in_array($statusFilter, ['under_review', 'paid', 'pending', 'rejected'])) {
            $query->where('payment_status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('orderGroup.payment', function ($pq) use ($search) {
                      $pq->where('transaction_id', 'like', "%{$search}%")
                         ->orWhere('sender_name', 'like', "%{$search}%")
                         ->orWhere('sender_account', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        // Count totals for seller
        $counts = [
            'all' => Order::where('seller_id', $sellerId)->count(),
            'under_review' => Order::where('seller_id', $sellerId)->where('payment_status', 'under_review')->count(),
            'paid' => Order::where('seller_id', $sellerId)->where('payment_status', 'paid')->count(),
            'pending' => Order::where('seller_id', $sellerId)->where('payment_status', 'pending')->count(),
            'rejected' => Order::where('seller_id', $sellerId)->where('payment_status', 'rejected')->count(),
        ];

        $totalPaidSum = Order::where('seller_id', $sellerId)->where('payment_status', 'paid')->sum('total');
        $totalPendingReviewSum = Order::where('seller_id', $sellerId)->where('payment_status', 'under_review')->sum('total');

        return view('seller.payments.index', [
            'orders' => $orders,
            'currentStatus' => $statusFilter,
            'search' => $search,
            'counts' => $counts,
            'totalPaidSum' => $totalPaidSum,
            'totalPendingReviewSum' => $totalPendingReviewSum,
        ]);
    }
}
