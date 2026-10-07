<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EarningsController extends Controller
{
    /**
     * Display seller's earnings report with date range filters.
     */
    public function index(Request $request): View
    {
        $sellerId = Auth::id();
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $preset = $request->query('preset', 'this_month');

        if ($preset === 'this_month') {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        } elseif ($preset === 'last_30_days') {
            $startDate = Carbon::now()->subDays(30)->toDateString();
            $endDate = Carbon::now()->toDateString();
        } elseif ($preset === 'this_year') {
            $startDate = Carbon::now()->startOfYear()->toDateString();
            $endDate = Carbon::now()->endOfYear()->toDateString();
        }

        $query = Order::where('seller_id', $sellerId)
            ->where('status', '!=', 'cancelled');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Summary metrics
        $totalOrdersCount = (clone $query)->count();
        $grossSales = (clone $query)->sum('subtotal');
        $totalCommission = (clone $query)->sum('commission');
        $netPayable = $grossSales - $totalCommission;

        // Paginated orders list for the breakdown
        $orders = (clone $query)
            ->with(['buyer', 'orderGroup.payment'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('seller.earnings.index', [
            'orders' => $orders,
            'totalOrdersCount' => $totalOrdersCount,
            'grossSales' => $grossSales,
            'totalCommission' => $totalCommission,
            'netPayable' => $netPayable,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'preset' => $preset,
        ]);
    }
}
