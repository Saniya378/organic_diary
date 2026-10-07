<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the order.
     * Buyer can view own orders, Seller can view own orders, Admin can view all.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'seller' && $order->seller_id === $user->id) {
            return true;
        }

        return $order->buyer_id === $user->id;
    }

    /**
     * Determine whether the user can update the order status.
     * Seller owning the sub-order or Admin can update.
     */
    public function update(User $user, Order $order): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->isSeller() && $order->seller_id === $user->id;
    }

    /**
     * Determine whether the user can cancel the order.
     * Only buyer (or admin) can cancel, and ONLY when status is placed, new, or confirmed.
     */
    public function cancel(User $user, Order $order): bool
    {
        if ($user->role !== 'admin' && $order->buyer_id !== $user->id) {
            return false;
        }

        return in_array($order->status, ['placed', 'new', 'confirmed']);
    }
}
