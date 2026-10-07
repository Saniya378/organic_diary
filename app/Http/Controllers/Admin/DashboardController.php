<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // 1. KPI Cards
        $totalSellers = User::where('role', 'seller')->count();
        $totalBuyers = User::where('role', 'buyer')->count();

        $todayStart = Carbon::today();
        $ordersToday = Order::where('created_at', '>=', $todayStart)->count();

        $monthStart = Carbon::now()->startOfMonth();
        $monthlyRevenue = (int) Order::where('created_at', '>=', $monthStart)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        // 2. Pending Sellers Table
        $pendingSellers = SellerProfile::where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        // 3. Pending Products Table
        $pendingProducts = Product::where('status', 'pending')
            ->with(['seller.sellerProfile', 'category', 'images'])
            ->latest()
            ->get();

        // 4. Orders by Status Breakdown
        $statusCounts = [
            'placed' => Order::whereIn('status', ['placed', 'new'])->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'packing' => Order::where('status', 'packing')->count(),
            'in_transit' => Order::where('status', 'in_transit')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        $totalActiveOrders = max(1, array_sum($statusCounts));

        return view('admin.dashboard', [
            'totalSellers' => $totalSellers,
            'totalBuyers' => $totalBuyers,
            'ordersToday' => $ordersToday,
            'monthlyRevenue' => $monthlyRevenue,
            'pendingSellers' => $pendingSellers,
            'pendingProducts' => $pendingProducts,
            'statusCounts' => $statusCounts,
            'totalActiveOrders' => $totalActiveOrders,
        ]);
    }
}
