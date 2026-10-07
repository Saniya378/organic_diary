<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function ($query) {
                $query->approved();
            }])
            ->get();

        $freshTodayProducts = Product::approved()
            ->with(['seller.sellerProfile', 'category', 'variants', 'images', 'reviews'])
            ->latest('produced_on')
            ->latest('created_at')
            ->take(4)
            ->get();

        $topFarmers = SellerProfile::approved()
            ->with(['user.products' => function ($q) {
                $q->approved();
            }])
            ->take(3)
            ->get();

        return view('welcome', compact('categories', 'freshTodayProducts', 'topFarmers'));
    }
}
