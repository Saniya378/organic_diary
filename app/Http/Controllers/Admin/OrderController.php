<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of orders with filters and search.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $paymentStatus = $request->query('payment_status');
        $search = $request->query('q');

        $query = Order::with(['buyer', 'seller.sellerProfile', 'items', 'orderGroup'])->latest();

        if ($status && in_array($status, ['placed', 'new', 'confirmed', 'packing', 'in_transit', 'delivered', 'cancelled'])) {
            $query->where('status', $status);
        }

        if ($paymentStatus && in_array($paymentStatus, ['paid', 'unpaid', 'refunded'])) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                  ->orWhereHas('buyer', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('seller.sellerProfile', function ($sq) use ($search) {
                      $sq->where('farm_name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'currentStatus' => $status,
            'paymentStatus' => $paymentStatus,
            'search' => $search,
        ]);
    }

    /**
     * Display the specified order with full history, items, and logistics details.
     */
    public function show(Order $order): View
    {
        $order->load(['buyer', 'seller.sellerProfile', 'items.product', 'items.variant', 'orderGroup.payment', 'statusHistories.changedByUser']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Cancel an order with a reason and restore product / variant stock.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($order->status === 'cancelled') {
            return back()->with('error', 'Order is already cancelled.');
        }

        DB::transaction(function () use ($request, $order) {
            // Restore inventory
            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    ProductVariant::where('id', $item->variant_id)->increment('stock', $item->quantity);
                }
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->increment('stock', $item->quantity);
                }
            }

            // Update order status
            $order->update([
                'status' => 'cancelled',
                'cancel_reason' => $request->input('cancel_reason'),
            ]);

            // Add history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'cancelled',
                'note' => 'Order cancelled by Admin: ' . $request->input('cancel_reason'),
                'changed_by' => auth()->id(),
            ]);
        });

        return back()->with('success', "Order #{$order->order_no} has been cancelled and stock has been restored.");
    }

    /**
     * Display printable PDF-ready invoice for admin.
     */
    public function invoice(Order $order): View
    {
        $order->load(['orderGroup.payment', 'items.product', 'items.variant', 'seller.sellerProfile', 'buyer']);

        return view('invoices.order', [
            'order' => $order,
        ]);
    }
}
