<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::approved()
            ->with(['seller.sellerProfile', 'category', 'variants', 'images', 'reviews']);

        // 1. Search filter
        if ($request->filled('q')) {
            $search = trim($request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($catQuery) use ($search) {
                      $catQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('seller.sellerProfile', function ($sellerQuery) use ($search) {
                      $sellerQuery->where('farm_name', 'like', "%{$search}%")
                                  ->orWhere('location', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Category filter
        if ($request->filled('category')) {
            $categorySlugs = is_array($request->input('category')) ? $request->input('category') : [$request->input('category')];
            $query->whereHas('category', function ($q) use ($categorySlugs) {
                $q->whereIn('slug', $categorySlugs);
            });
        }

        // 3. Price range filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (int) $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (int) $request->input('max_price'));
        }

        // 4. Farm / Seller filter
        if ($request->filled('sellers')) {
            $sellerIds = (array) $request->input('sellers');
            $query->whereIn('seller_id', $sellerIds);
        }

        // 5. Minimum rating filter
        if ($request->filled('rating')) {
            $minRating = (float) $request->input('rating');
            $query->whereHas('reviews', function ($q) use ($minRating) {
                $q->havingRaw('AVG(rating) >= ?', [$minRating]);
            });
        }

        // 6. Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->withCount('orderItems')->orderBy('order_items_count', 'desc');
                break;
            case 'newest':
            default:
                $query->latest('produced_on')->latest('created_at');
                break;
        }

        $products = $query->paginate(12)->withQueryString();

        // Data for sidebar filters with eager loading
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->approved();
            }])
            ->get();

        $approvedSellers = SellerProfile::approved()
            ->with(['user.products' => function ($q) {
                $q->approved();
            }])
            ->get();

        return view('shop.index', compact('products', 'categories', 'approvedSellers'));
    }

    public function show(string $slug): View
    {
        $product = Product::approved()
            ->with([
                'seller.sellerProfile',
                'category',
                'variants',
                'images',
                'reviews.user',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProducts = Product::approved()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['seller.sellerProfile', 'variants', 'images', 'reviews'])
            ->take(4)
            ->get();

        $canReview = false;
        $unreviewedOrder = null;
        if (auth()->check()) {
            $unreviewedOrder = \App\Models\Order::where('buyer_id', auth()->id())
                ->where('status', 'delivered')
                ->whereHas('items', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->whereDoesntHave('reviews', function ($q) use ($product) {
                    $q->where('user_id', auth()->id())->where('product_id', $product->id);
                })
                ->first();
            $canReview = (bool) $unreviewedOrder;
        }

        return view('shop.show', compact('product', 'relatedProducts', 'canReview', 'unreviewedOrder'));
    }

    /**
     * Live search autocomplete endpoint for Navbar & Shop search inputs.
     */
    public function autocomplete(Request $request): \Illuminate\Http\JsonResponse
    {
        $q = trim($request->input('q', ''));
        if (mb_strlen($q) < 1) {
            return response()->json([
                'query' => '',
                'products' => [],
                'categories' => [],
                'sellers' => [],
                'total' => 0,
            ]);
        }

        // 1. Products (limit 6)
        $products = Product::approved()
            ->with(['category', 'seller.sellerProfile', 'images'])
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%")
                      ->orWhereHas('category', function ($catQuery) use ($q) {
                          $catQuery->where('name', 'like', "%{$q}%");
                      })
                      ->orWhereHas('seller.sellerProfile', function ($sellerQuery) use ($q) {
                          $sellerQuery->where('farm_name', 'like', "%{$q}%")
                                      ->orWhere('location', 'like', "%{$q}%");
                      });
            })
            ->take(6)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'url' => route('shop.show', $product->slug),
                    'price' => $product->price,
                    'formatted_price' => 'Rs ' . number_format($product->price),
                    'unit' => $product->unit ?? 'unit',
                    'category_name' => $product->category->name ?? 'Dairy',
                    'category_slug' => $product->category->slug ?? '',
                    'farm_name' => $product->seller->sellerProfile->farm_name ?? $product->seller->name ?? 'Local Farm',
                    'location' => $product->seller->sellerProfile->location ?? 'Gilgit-Baltistan',
                    'image_url' => $product->primary_image ? asset('storage/' . $product->primary_image) : null,
                    'in_stock' => $product->stock > 0,
                    'stock' => $product->stock,
                    'is_organic' => (bool) $product->is_organic,
                ];
            });

        // 2. Categories (limit 3)
        $categories = Category::where('is_active', true)
            ->where('name', 'like', "%{$q}%")
            ->withCount(['products' => function ($query) {
                $query->approved();
            }])
            ->take(3)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'url' => route('shop.index', ['category' => $category->slug]),
                    'products_count' => $category->products_count,
                ];
            });

        // 3. Sellers / Farms (limit 3)
        $sellers = SellerProfile::approved()
            ->where(function ($query) use ($q) {
                $query->where('farm_name', 'like', "%{$q}%")
                      ->orWhere('location', 'like', "%{$q}%");
            })
            ->take(3)
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'farm_name' => $profile->farm_name,
                    'location' => $profile->location,
                    'url' => route('farmers.show', $profile->id),
                ];
            });

        // Total count of matching products
        $totalMatches = Product::approved()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%")
                      ->orWhereHas('category', function ($catQuery) use ($q) {
                          $catQuery->where('name', 'like', "%{$q}%");
                      })
                      ->orWhereHas('seller.sellerProfile', function ($sellerQuery) use ($q) {
                          $sellerQuery->where('farm_name', 'like', "%{$q}%");
                      });
            })
            ->count();

        return response()->json([
            'query' => $q,
            'products' => $products,
            'categories' => $categories,
            'sellers' => $sellers,
            'total' => $totalMatches,
        ]);
    }
}
