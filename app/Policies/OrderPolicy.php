<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->role === 'vendor') {
            return $order->orderItems()->where('vendor_id', $user->id)->exists();
        }
        return $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->role === 'vendor' && $order->orderItems()->where('vendor_id', $user->id)->exists();
    }
}
