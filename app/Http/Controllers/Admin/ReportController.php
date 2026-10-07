<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the platform sales and analytics reports.
     */
    public function index(Request $request): View
    {
        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        // 1. Overall Summary Totals
        $ordersQuery = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled');

        $totalSales = (int) $ordersQuery->sum('total');
        $totalSubtotal = (int) $ordersQuery->sum('subtotal');
        $totalCommission = (int) $ordersQuery->sum('commission');
        $ordersCount = $ordersQuery->count();

        // 2. Sales by Date Range (Daily Breakdown)
        $salesByDate = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(id) as orders_count'),
                DB::raw('SUM(subtotal) as total_subtotal'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(commission) as total_commission')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        // 3. Sales by Seller Farm
        $salesBySeller = Order::select(
                'seller_id',
                DB::raw('COUNT(id) as orders_count'),
                DB::raw('SUM(subtotal) as total_subtotal'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(commission) as total_commission')
            )
            ->with(['seller.sellerProfile'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy('seller_id')
            ->orderBy('total_revenue', 'desc')
            ->get();

        // 4. Sales by Category
        $salesByCategory = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_units_sold'),
                DB::raw('SUM(order_items.line_total) as total_revenue')
            )
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get();

        return view('admin.reports.index', [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'totalSales' => $totalSales,
            'totalSubtotal' => $totalSubtotal,
            'totalCommission' => $totalCommission,
            'ordersCount' => $ordersCount,
            'salesByDate' => $salesByDate,
            'salesBySeller' => $salesBySeller,
            'salesByCategory' => $salesByCategory,
        ]);
    }

    /**
     * Export the filtered sales report to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->query('start_date') ? Carbon::parse($request->query('start_date'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endDate = $request->query('end_date') ? Carbon::parse($request->query('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        $fileName = 'sales-report-' . $startDate->format('Ymd') . '-to-' . $endDate->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($startDate, $endDate) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Order No',
                'Date',
                'Buyer Name',
                'Buyer Phone',
                'Seller Farm',
                'Items Count',
                'Subtotal (PKR)',
                'Delivery Fee (PKR)',
                'Commission (PKR)',
                'Grand Total (PKR)',
                'Status',
                'Payment Status',
            ]);

            $orders = Order::with(['buyer', 'seller.sellerProfile', 'items'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->latest()
                ->cursor();

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_no,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->buyer->name ?? 'Guest',
                    $order->buyer->phone ?? '',
                    $order->seller->sellerProfile->farm_name ?? $order->seller->name,
                    $order->items->count(),
                    $order->subtotal,
                    $order->delivery_fee,
                    $order->commission,
                    $order->total,
                    $order->status,
                    $order->payment_status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
