<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Retrieve or create the active Cart instance for the authenticated user or guest session.
     */
    public function getOrCreateCart(): Cart
    {
        if (Auth::check()) {
            $user = Auth::user();
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            // If a guest cart exists in the current session, merge it
            $sessionId = Session::getId();
            if ($sessionId) {
                $guestCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
                if ($guestCart && $guestCart->id !== $cart->id) {
                    $this->mergeCart($guestCart, $cart);
                }
            }

            return $cart;
        }

        $sessionId = Session::getId();
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Add a product/variant to the cart with strict stock and approved status checks.
     */
    public function addItem(int $productId, ?int $variantId = null, int $quantity = 1, ?Cart $explicitCart = null): array
    {
        if ($quantity < 1) {
            $quantity = 1;
        }

        // 1. Verify product is approved and from an active, approved seller
        $product = Product::approved()
            ->with(['seller.sellerProfile', 'variants'])
            ->find($productId);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'This product is unavailable or no longer approved for sale.',
            ];
        }

        // 2. Determine variant and available stock
        $variant = null;
        $unitPrice = $product->price;
        $maxStock = $product->stock;
        $label = $product->name;

        if ($variantId) {
            $variant = ProductVariant::where('product_id', $product->id)->find($variantId);
            if (!$variant) {
                return [
                    'success' => false,
                    'message' => 'Selected product variant is invalid.',
                ];
            }
            $unitPrice = $variant->price;
            $maxStock = $variant->stock;
            $label .= ' (' . $variant->label . ')';
        }

        if ($maxStock <= 0) {
            return [
                'success' => false,
                'message' => "Sorry, '{$label}' is currently out of stock.",
            ];
        }

        $cart = $explicitCart ?? $this->getOrCreateCart();

        // 3. Check for existing line item in cart
        $cartItemQuery = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id);

        if ($variantId) {
            $cartItemQuery->where('variant_id', $variantId);
        } else {
            $cartItemQuery->whereNull('variant_id');
        }

        $existingItem = $cartItemQuery->first();
        $isCapped = false;
        $finalQuantity = $quantity;

        if ($existingItem) {
            $targetQuantity = $existingItem->quantity + $quantity;
            if ($targetQuantity > $maxStock) {
                $finalQuantity = $maxStock;
                $isCapped = true;
            } else {
                $finalQuantity = $targetQuantity;
            }

            $existingItem->update(['quantity' => $finalQuantity]);
            $cartItem = $existingItem;
        } else {
            if ($finalQuantity > $maxStock) {
                $finalQuantity = $maxStock;
                $isCapped = true;
            }

            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'quantity' => $finalQuantity,
            ]);
        }

        $cartCount = $this->getCartCount($cart);
        $subtotal = $this->getCartSubtotal($cart);

        $message = "Added '{$label}' to your basket.";
        if ($isCapped) {
            $message = "Added '{$label}' to basket (quantity capped to maximum available stock of {$maxStock}).";
        }

        return [
            'success' => true,
            'message' => $message,
            'is_capped' => $isCapped,
            'cart_count' => $cartCount,
            'subtotal' => $subtotal,
            'formatted_subtotal' => 'Rs ' . number_format($subtotal),
            'item' => [
                'id' => $cartItem->id,
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'name' => $label,
                'quantity' => $cartItem->quantity,
                'unit_price' => $unitPrice,
                'formatted_unit_price' => 'Rs ' . number_format($unitPrice),
                'line_total' => $cartItem->quantity * $unitPrice,
                'formatted_line_total' => 'Rs ' . number_format($cartItem->quantity * $unitPrice),
            ],
        ];
    }

    /**
     * Update quantity of an existing item in the cart.
     */
    public function updateItem(int $cartItemId, int $quantity): array
    {
        $cartItem = CartItem::find($cartItemId);

        if (!$cartItem) {
            return [
                'success' => false,
                'message' => 'Cart item not found.',
            ];
        }

        $cart = $cartItem->cart;

        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }

        // Check product/variant stock limits
        $product = Product::approved()->find($cartItem->product_id);
        if (!$product) {
            $cartItem->delete();
            return [
                'success' => false,
                'message' => 'Product is no longer available and was removed from cart.',
                'cart_count' => $this->getCartCount($cart),
                'subtotal' => $this->getCartSubtotal($cart),
            ];
        }

        $maxStock = $product->stock;
        if ($cartItem->variant_id) {
            $variant = ProductVariant::find($cartItem->variant_id);
            if ($variant) {
                $maxStock = $variant->stock;
            }
        }

        $isCapped = false;
        if ($quantity > $maxStock) {
            $quantity = $maxStock;
            $isCapped = true;
        }

        $cartItem->update(['quantity' => $quantity]);

        $unitPrice = $cartItem->unit_price;
        $lineTotal = $unitPrice * $quantity;
        $subtotal = $this->getCartSubtotal($cart);
        $deliveryFee = (int) Setting::get('delivery_fee', 150);
        $grandTotal = $subtotal + ($subtotal > 0 ? $deliveryFee : 0);

        return [
            'success' => true,
            'message' => $isCapped ? "Quantity capped to maximum available stock ({$maxStock})." : 'Cart updated.',
            'is_capped' => $isCapped,
            'quantity' => $quantity,
            'max_stock' => $maxStock,
            'unit_price' => $unitPrice,
            'formatted_unit_price' => 'Rs ' . number_format($unitPrice),
            'line_total' => $lineTotal,
            'formatted_line_total' => 'Rs ' . number_format($lineTotal),
            'cart_count' => $this->getCartCount($cart),
            'subtotal' => $subtotal,
            'formatted_subtotal' => 'Rs ' . number_format($subtotal),
            'delivery_fee' => $deliveryFee,
            'formatted_delivery_fee' => 'Rs ' . number_format($deliveryFee),
            'grand_total' => $grandTotal,
            'formatted_grand_total' => 'Rs ' . number_format($grandTotal),
        ];
    }

    /**
     * Remove an item from the active cart.
     */
    public function removeItem(int $cartItemId): array
    {
        $cartItem = CartItem::find($cartItemId);
        $cart = $cartItem ? $cartItem->cart : $this->getOrCreateCart();

        if ($cartItem) {
            $cartItem->delete();
        }

        $subtotal = $this->getCartSubtotal($cart);
        $deliveryFee = (int) Setting::get('delivery_fee', 150);
        $grandTotal = $subtotal > 0 ? $subtotal + $deliveryFee : 0;
        $cartCount = $this->getCartCount($cart);

        return [
            'success' => true,
            'message' => 'Item removed from basket.',
            'cart_count' => $cartCount,
            'subtotal' => $subtotal,
            'formatted_subtotal' => 'Rs ' . number_format($subtotal),
            'delivery_fee' => $deliveryFee,
            'formatted_delivery_fee' => 'Rs ' . number_format($deliveryFee),
            'grand_total' => $grandTotal,
            'formatted_grand_total' => 'Rs ' . number_format($grandTotal),
            'is_empty' => $cartCount === 0,
        ];
    }

    /**
     * Get complete cart details including items, seller breakdown, subtotal, delivery fee, and grand total.
     */
    public function getCartDetails(): array
    {
        $cart = $this->getOrCreateCart();
        $items = CartItem::where('cart_id', $cart->id)
            ->with(['product.seller.sellerProfile', 'product.images', 'variant'])
            ->get();

        // Filter out any items that became inactive/deleted
        $validItems = collect();
        foreach ($items as $item) {
            if ($item->product && $item->product->status === 'approved') {
                $validItems->push($item);
            } else {
                $item->delete();
            }
        }

        $subtotal = $validItems->sum(function ($item) {
            return $item->line_total;
        });

        $deliveryFee = (int) Setting::get('delivery_fee', 150);
        $grandTotal = $subtotal > 0 ? ($subtotal + $deliveryFee) : 0;

        return [
            'cart' => $cart,
            'items' => $validItems,
            'cart_count' => $validItems->sum('quantity'),
            'subtotal' => $subtotal,
            'formatted_subtotal' => 'Rs ' . number_format($subtotal),
            'delivery_fee' => $deliveryFee,
            'formatted_delivery_fee' => 'Rs ' . number_format($deliveryFee),
            'grand_total' => $grandTotal,
            'formatted_grand_total' => 'Rs ' . number_format($grandTotal),
            'is_empty' => $validItems->isEmpty(),
        ];
    }

    /**
     * Get total quantity count of all items in active cart.
     */
    public function getCartCount(?Cart $cart = null): int
    {
        if ($cart) {
            return (int) $cart->items()->sum('quantity');
        }

        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
        } else {
            $sessionId = Session::getId();
            $cart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();
        }

        if (!$cart) {
            return 0;
        }

        return (int) $cart->items()->sum('quantity');
    }

    /**
     * Calculate active subtotal for given cart.
     */
    public function getCartSubtotal(Cart $cart): int
    {
        return (int) $cart->items()->with(['product', 'variant'])->get()->sum(function ($item) {
            return $item->line_total;
        });
    }

    /**
     * Merge guest cart records into user's DB cart.
     */
    public function mergeCart(Cart $sourceCart, Cart $targetCart): void
    {
        foreach ($sourceCart->items as $item) {
            $this->addItem($item->product_id, $item->variant_id, $item->quantity, $targetCart);
        }
        $sourceCart->delete();
    }

    /**
     * Merge items sent from client localStorage upon login/register.
     */
    public function mergeFromLocalStorage(array $localItems): void
    {
        $targetCart = $this->getOrCreateCart();
        foreach ($localItems as $item) {
            $productId = $item['product_id'] ?? $item['productId'] ?? null;
            $variantId = $item['variant_id'] ?? $item['variantId'] ?? null;
            $quantity = (int) ($item['quantity'] ?? 1);

            if ($productId) {
                $this->addItem((int) $productId, $variantId ? (int) $variantId : null, $quantity, $targetCart);
            }
        }
    }
}
