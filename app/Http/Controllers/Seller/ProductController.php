<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of the seller's products.
     */
    public function index(Request $request): View
    {
        $sellerId = Auth::id();
        $statusFilter = $request->query('status');

        $query = Product::where('seller_id', $sellerId)
            ->with(['category', 'variants', 'images'])
            ->latest();

        if ($statusFilter && in_array($statusFilter, ['approved', 'pending', 'rejected', 'inactive'])) {
            $query->where('status', $statusFilter);
        }

        $products = $query->paginate(12)->withQueryString();

        $counts = [
            'all' => Product::where('seller_id', $sellerId)->count(),
            'approved' => Product::where('seller_id', $sellerId)->where('status', 'approved')->count(),
            'pending' => Product::where('seller_id', $sellerId)->where('status', 'pending')->count(),
            'inactive' => Product::where('seller_id', $sellerId)->where('status', 'inactive')->count(),
        ];

        return view('seller.products.index', [
            'products' => $products,
            'currentStatus' => $statusFilter,
            'isApprovedSeller' => Auth::user()->sellerProfile?->status === 'approved',
            'counts' => $counts,
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View|RedirectResponse
    {
        $profile = Auth::user()->sellerProfile;

        if (!$profile || $profile->status !== 'approved') {
            return redirect()->route('seller.products.index')
                ->with('error', 'Your seller account is awaiting administrator verification. Products cannot be created until approved.');
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('seller.products.create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        $seller = Auth::user();

        if (!$seller->sellerProfile || $seller->sellerProfile->status !== 'approved') {
            return redirect()->route('seller.products.index')
                ->with('error', 'You must have an approved seller account to add products.');
        }

        $validated = $request->validated();

        $product = DB::transaction(function () use ($validated, $request, $seller) {
            $baseSlug = Str::slug($validated['name']);
            $uniqueSlug = $baseSlug . '-' . substr(uniqid(), -5);

            $product = Product::create([
                'seller_id' => $seller->id,
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'slug' => $uniqueSlug,
                'description' => $validated['description'],
                'price' => $validated['price'],
                'unit' => $validated['unit'],
                'stock' => $validated['stock'],
                'batch_no' => $validated['batch_no'] ?? null,
                'produced_on' => $validated['produced_on'] ?? null,
                'is_organic' => $request->boolean('is_organic'),
                'deals_enabled' => $request->boolean('deals_enabled'),
                'status' => 'pending', // New products are pending review
            ]);

            // Save Variants
            if (!empty($validated['variants'])) {
                foreach ($validated['variants'] as $v) {
                    if (!empty($v['label']) && isset($v['price'])) {
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'label' => $v['label'],
                            'price' => (int) $v['price'],
                            'stock' => (int) ($v['stock'] ?? 0),
                        ]);
                    }
                }
            }

            // Save Uploaded Images (up to 5)
            if ($request->hasFile('images')) {
                $sort = 0;
                foreach ($request->file('images') as $imageFile) {
                    if ($imageFile->isValid()) {
                        $path = $imageFile->store('products', 'public');
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $path,
                            'sort_order' => $sort++,
                        ]);
                    }
                }
            }

            return $product;
        });

        return redirect()->route('seller.products.index')
            ->with('success', "Product '{$product->name}' was created successfully and submitted for admin review (Status: Pending).");
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View|RedirectResponse
    {
        Gate::authorize('update', $product);

        $profile = Auth::user()->sellerProfile;
        if (!$profile || $profile->status !== 'approved') {
            return redirect()->route('seller.products.index')
                ->with('error', 'Your seller account must be approved before editing products.');
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $product->load(['variants', 'images']);

        return view('seller.products.edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $product) {
            $product->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'unit' => $validated['unit'],
                'stock' => $validated['stock'],
                'batch_no' => $validated['batch_no'] ?? null,
                'produced_on' => $validated['produced_on'] ?? null,
                'is_organic' => $request->boolean('is_organic'),
                'deals_enabled' => $request->boolean('deals_enabled'),
                'status' => 'pending', // Edited products become pending
            ]);

            // Sync Variants
            ProductVariant::where('product_id', $product->id)->delete();
            if (!empty($validated['variants'])) {
                foreach ($validated['variants'] as $v) {
                    if (!empty($v['label']) && isset($v['price'])) {
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'label' => $v['label'],
                            'price' => (int) $v['price'],
                            'stock' => (int) ($v['stock'] ?? 0),
                        ]);
                    }
                }
            }

            // Add new images (keeping existing count under 5)
            if ($request->hasFile('images')) {
                $existingCount = $product->images()->count();
                $sort = $existingCount;
                foreach ($request->file('images') as $imageFile) {
                    if ($imageFile->isValid() && $sort < 5) {
                        $path = $imageFile->store('products', 'public');
                        ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $path,
                            'sort_order' => $sort++,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('seller.products.index')
            ->with('success', "Product '{$product->name}' was updated and re-submitted for admin review (Status: Pending).");
    }

    /**
     * Soft-delete the specified product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $productName = $product->name;
        $product->delete();

        return redirect()->route('seller.products.index')
            ->with('success', "Product '{$productName}' was removed from your catalog.");
    }

    /**
     * Toggle product active/inactive state.
     */
    public function toggleActive(Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        if ($product->status === 'inactive') {
            $product->update(['status' => 'approved']);
            $msg = "Product '{$product->name}' activated.";
        } else {
            $product->update(['status' => 'inactive']);
            $msg = "Product '{$product->name}' set to inactive.";
        }

        return back()->with('success', $msg);
    }

    /**
     * Quick toggle for seller to enable or disable recurring deals on this product.
     */
    public function toggleDeals(Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $newVal = !$product->deals_enabled;
        $product->update(['deals_enabled' => $newVal]);

        $statusText = $newVal ? 'ENABLED' : 'DISABLED';
        $message = "Subscription deals (3-Day, 1-Week, 1-Month) have been {$statusText} for '{$product->name}'.";

        return back()->with('success', $message);
    }

    /**
     * Delete an individual product image.
     */
    public function deleteImage(Product $product, ProductImage $image): RedirectResponse
    {
        Gate::authorize('update', $product);

        if ($image->product_id !== $product->id) {
            abort(403);
        }

        if ($image->path && Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        return back()->with('success', 'Product photo was successfully removed.');
    }
}
