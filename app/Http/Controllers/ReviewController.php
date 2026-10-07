<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a verified review for a delivered product.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
        ]);

        $userId = auth()->id();

        // Look for eligible delivered order that hasn't been reviewed yet for this product
        $eligibleOrderQuery = Order::where('buyer_id', $userId)
            ->where('status', 'delivered')
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->whereDoesntHave('reviews', function ($q) use ($userId, $product) {
                $q->where('user_id', $userId)->where('product_id', $product->id);
            });

        if ($request->filled('order_id')) {
            $eligibleOrder = $eligibleOrderQuery->where('id', $request->input('order_id'))->first();
        } else {
            $eligibleOrder = $eligibleOrderQuery->first();
        }

        if (!$eligibleOrder) {
            return back()->with('error', 'You can only review products from orders that have been successfully delivered to you, and each order can be reviewed once.');
        }

        Review::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'order_id' => $eligibleOrder->id,
            'rating' => (int) $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);

        return back()->with('success', 'Thank you! Your verified purchase review has been published.');
    }
}
