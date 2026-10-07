<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $sellerId = Auth::id();
        $user = Auth::user();
        $profile = $user->sellerProfile;
        $lowStockThreshold = (int) Setting::get('low_stock_threshold', 10);

        // 1. KPI: Today's Sales
        $todayStart = Carbon::today();
        $todaysSales = (int) Order::where('seller_id', $sellerId)
            ->where('created_at', '>=', $todayStart)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // 2. KPI: New / Placed Orders
        $newOrdersCount = Order::where('seller_id', $sellerId)
            ->whereIn('status', ['placed', 'new'])
            ->count();

        // 3. KPI: Active (Approved) Products
        $activeProductsCount = Product::where('seller_id', $sellerId)
            ->where('status', 'approved')
            ->count();

        // 4. KPI: Low Stock Count (Products or Variants <= threshold)
        $lowStockProducts = Product::where('seller_id', $sellerId)
            ->where('stock', '<=', $lowStockThreshold)
            ->get();

        $lowStockVariants = ProductVariant::whereHas('product', function ($q) use ($sellerId) {
                $q->where('seller_id', $sellerId);
            })
            ->where('stock', '<=', $lowStockThreshold)
            ->with('product')
            ->get();

        $lowStockCount = $lowStockProducts->count() + $lowStockVariants->count();

        // 5. Weekly Sales Bar Chart Data (Past 7 Days)
        $chartLabels = [];
        $chartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $dayName = $day->format('D, M d');
            $daySales = (int) Order::where('seller_id', $sellerId)
                ->whereDate('created_at', $day->toDateString())
                ->where('status', '!=', 'cancelled')
                ->sum('total');

            $chartLabels[] = $dayName;
            $chartData[] = $daySales;
        }

        // 6. Recent Orders (Latest 6)
        $recentOrders = Order::where('seller_id', $sellerId)
            ->with(['buyer', 'items', 'orderGroup'])
            ->latest()
            ->take(6)
            ->get();

        return view('seller.dashboard', [
            'user' => $user,
            'profile' => $profile,
            'todaysSales' => $todaysSales,
            'newOrdersCount' => $newOrdersCount,
            'activeProductsCount' => $activeProductsCount,
            'lowStockCount' => $lowStockCount,
            'lowStockThreshold' => $lowStockThreshold,
            'lowStockProducts' => $lowStockProducts,
            'lowStockVariants' => $lowStockVariants,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'recentOrders' => $recentOrders,
        ]);
    }
}
