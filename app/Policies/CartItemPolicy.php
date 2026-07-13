<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CartItemPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CartItem $cartItem): Response
    {
        return $user->id === $cartItem->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CartItem $cartItem): Response
    {
        return $user->id === $cartItem->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CartItem $cartItem): Response
    {
        return $user->id === $cartItem->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
