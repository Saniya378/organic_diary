<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products with filters.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $query = Product::with(['seller.sellerProfile', 'category', 'images', 'variants'])->latest();

        if ($statusFilter && in_array($statusFilter, ['approved', 'pending', 'rejected', 'inactive'])) {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('seller.sellerProfile', function ($sq) use ($search) {
                      $sq->where('farm_name', 'like', "%{$search}%");
                  });
            });
        }

        $products = $query->paginate(15)->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'currentStatus' => $statusFilter,
            'search' => $search,
        ]);
    }

    /**
     * Approve product for live marketplace listing.
     */
    public function approve(Product $product): RedirectResponse
    {
        $product->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Product '{$product->name}' approved. It is now live in the shop catalog.");
    }

    /**
     * Reject product with reason.
     */
    public function reject(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $product->update([
            'status' => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        return back()->with('success', "Product '{$product->name}' was rejected.");
    }

    /**
     * Update product-specific platform commission percentage.
     */
    public function updateCommission(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $product->update([
            'commission_rate' => $request->filled('commission_rate') ? (float) $request->input('commission_rate') : null,
        ]);

        $rateDisplay = $product->commission_rate !== null ? "{$product->commission_rate}%" : "default platform rate ({$product->effective_commission_rate}%)";

        return back()->with('success', "Commission rate for '{$product->name}' has been updated to {$rateDisplay}.");
    }
}
