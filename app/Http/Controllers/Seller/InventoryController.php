<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display seller's inventory table with products and variants.
     */
    public function index(): View
    {
        $sellerId = Auth::id();
        $lowStockThreshold = (int) Setting::get('low_stock_threshold', 10);

        $products = Product::where('seller_id', $sellerId)
            ->with(['category', 'variants', 'images'])
            ->orderBy('name')
            ->get();

        return view('seller.inventory.index', [
            'products' => $products,
            'lowStockThreshold' => $lowStockThreshold,
        ]);
    }

    /**
     * Quick inline stock update for standard product via AJAX fetch.
     */
    public function updateStock(Request $request, Product $product): JsonResponse
    {
        Gate::authorize('update', $product);

        $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $newStock = (int) $request->input('stock');
        $product->update(['stock' => $newStock]);

        $threshold = (int) Setting::get('low_stock_threshold', 10);

        return response()->json([
            'success' => true,
            'message' => "Stock for '{$product->name}' updated to {$newStock}.",
            'stock' => $newStock,
            'is_low_stock' => $newStock <= $threshold,
        ]);
    }

    /**
     * Quick inline stock update for product variant via AJAX fetch.
     */
    public function updateVariantStock(Request $request, ProductVariant $variant): JsonResponse
    {
        Gate::authorize('update', $variant->product);

        $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $newStock = (int) $request->input('stock');
        $variant->update(['stock' => $newStock]);

        $threshold = (int) Setting::get('low_stock_threshold', 10);

        return response()->json([
            'success' => true,
            'message' => "Stock for '{$variant->product->name} ({$variant->label})' updated to {$newStock}.",
            'stock' => $newStock,
            'is_low_stock' => $newStock <= $threshold,
        ]);
    }
}
