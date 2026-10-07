<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\Models\SellerProfile;
use Illuminate\View\View;

class FarmerController extends Controller
{
    public function index(): View
    {
        $farmers = SellerProfile::approved()
            ->with(['user.products' => function ($q) {
                $q->approved();
            }])
            ->paginate(9);

        return view('farmers.index', compact('farmers'));
    }

    public function show(int $id): View
    {
        $farmer = SellerProfile::approved()
            ->with('user')
            ->findOrFail($id);

        $products = Product::approved()
            ->where('seller_id', $farmer->user_id)
            ->with(['category', 'variants', 'reviews', 'images'])
            ->paginate(12);

        $reviews = Review::whereHas('product', function ($q) use ($farmer) {
                $q->where('seller_id', $farmer->user_id);
            })
            ->with(['user', 'product'])
            ->latest()
            ->take(8)
            ->get();

        return view('farmers.show', compact('farmer', 'products', 'reviews'));
    }
}
