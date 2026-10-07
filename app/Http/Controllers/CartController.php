<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display the shopping cart page.
     */
    public function index(): View
    {
        $cartDetails = $this->cartService->getCartDetails();
        return view('cart.index', $cartDetails);
    }

    /**
     * Add a product to the cart (via AJAX fetch or standard Form submission).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $productId = (int) $request->input('product_id');
        $variantId = $request->filled('variant_id') ? (int) $request->input('variant_id') : null;
        $quantity = (int) $request->input('quantity', 1);

        $result = $this->cartService->addItem($productId, $variantId, $quantity);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    /**
     * Update quantity of a cart line item via AJAX.
     */
    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $quantity = (int) $request->input('quantity');
        $result = $this->cartService->updateItem($id, $quantity);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        return redirect()->route('cart.index')->with('success', $result['message']);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $result = $this->cartService->removeItem($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, 200);
        }

        return redirect()->route('cart.index')->with('success', 'Item removed from basket.');
    }

    /**
     * Merge items from client localStorage into the authenticated/session cart.
     */
    public function merge(Request $request): JsonResponse
    {
        $items = (array) $request->input('items', []);
        $this->cartService->mergeFromLocalStorage($items);

        return response()->json([
            'success' => true,
            'cart_count' => $this->cartService->getCartCount(),
        ]);
    }

    /**
     * Get live cart count for navbar.
     */
    public function count(): JsonResponse
    {
        return response()->json([
            'cart_count' => $this->cartService->getCartCount(),
        ]);
    }
}
