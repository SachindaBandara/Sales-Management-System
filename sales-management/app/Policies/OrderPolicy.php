<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any orders.
     */
    public function viewAny(User $user)
    {
        return $user->is_admin;
    }

    /**
     * Determine if the user can view the order.
     */
    public function view(User $user, Order $order)
    {
        return $user->is_admin || $user->id === $order->user_id;
    }

    /**
     * Determine if the user can create orders.
     */
    public function create(User $user)
    {
        return true; // All authenticated users can create orders
    }

    /**
     * Determine if the user can update the order.
     */
    public function update(User $user, Order $order)
    {
        if ($user->is_admin) {
            return true;
        }

        // Customers can only cancel their own orders
        return $user->id === $order->user_id && $order->canBeCancelled();
    }

    /**
     * Determine if the user can delete the order.
     */
    public function delete(User $user, Order $order)
    {
        return $user->is_admin;
    }

    /**
     * Determine if the user can cancel the order.
     */
    public function cancel(User $user, Order $order)
    {
        if ($user->is_admin) {
            return true;
        }

        return $user->id === $order->user_id && $order->canBeCancelled();
    }

    /**
     * Determine if the user can update order status.
     */
    public function updateStatus(User $user, Order $order)
    {
        return $user->is_admin;
    }

    /**
     * Determine if the user can update payment status.
     */
    public function updatePaymentStatus(User $user, Order $order)
    {
        return $user->is_admin;
    }
}
