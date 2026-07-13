<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CartItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cartItems = request()->user()->cartItems()->get();

        return CartItemResource::collection($cartItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            "product_id" => "required|int",
            "quantity" => "required|int",
        ]);

        $cartItem = CartItem::create([
            ...$validatedData,
            "user_id" => $request->user()->id,
        ]);

        return new CartItemResource($cartItem)->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CartItem $cartItem)
    {
        Gate::authorize("view", $cartItem);

        return new CartItemResource($cartItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CartItem $cartItem)
    {
        Gate::authorize("update", $cartItem);

        $validatedData = $request->validate([
            "product_id" => "prohibited",
            "quantity" => "int",
        ]);

        $cartItem->update($validatedData);

        return new CartItemResource($cartItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CartItem $cartItem)
    {
        Gate::authorize("delete", $cartItem);

        $cartItem->delete();

        return response()->noContent();
    }
}
