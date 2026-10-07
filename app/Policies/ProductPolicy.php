<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSeller() || $user->isAdmin();
    }

    /**
     * Determine whether the user can view the product.
     */
    public function view(?User $user, Product $product): bool
    {
        if ($product->status === 'approved') {
            return true;
        }

        if (!$user) {
            return false;
        }

        return $user->isAdmin() || ($user->isSeller() && $product->seller_id === $user->id);
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSeller() && $user->sellerProfile && $user->sellerProfile->status === 'approved';
    }

    /**
     * Determine whether the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSeller() && $product->seller_id === $user->id;
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSeller() && $product->seller_id === $user->id;
    }
}
