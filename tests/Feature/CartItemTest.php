<?php

use App\Models\Product;
use App\Models\CartItem;

describe("GET /api/cart", function () {
    it("get all items in user cart", function () {
        $user = actingAs();
        $products = Product::factory(3)->create();

        $cartItems = $products->map(
            fn($product) => CartItem::create([
                "user_id" => $user->id,
                "product_id" => $product->id,
                "quantity" => 1,
            ]),
        );

        $response = $this->getJson("/api/cart");

        $response
            ->assertOk()
            ->assertJsonStructure([
                "data" => [
                    "*" => ["id", "user_id", "product_id", "quantity"],
                ],
            ])
            ->assertJsonCount(3, "data");

        $cartItems->each(
            fn($cartItem) => $response->assertJsonFragment([
                "user_id" => $cartItem->user_id,
                "product_id" => $cartItem->product_id,
                "quantity" => $cartItem->quantity,
            ]),
        );

        $this->assertDatabaseCount("cart_items", 3);

        $cartItems->each(
            fn($cartItem) => $this->assertDatabaseHas("cart_items", [
                "id" => $cartItem->id,
                "user_id" => $cartItem->user_id,
                "product_id" => $cartItem->product_id,
                "quantity" => $cartItem->quantity,
            ]),
        );
    });

    it("returns empty array when cart is empty", function () {
        actingAs();

        $this->getJson("/api/cart")
            ->assertOk()
            ->assertJson([
                "data" => [],
            ]);
    });
});

describe("GET /api/cart/{id}", function () {
    it("get specific cart item", function () {
        $user = actingAs();
        $product = Product::factory()->create();

        $cartItem = CartItem::create([
            "user_id" => $user->id,
            "product_id" => $product->id,
            "quantity" => 1,
        ]);

        $response = $this->getJson("/api/cart/{$cartItem->id}");

        $response->assertOk()->assertJsonStructure([
            "data" => ["id", "user_id", "product_id", "quantity"],
        ]);

        $response->assertJsonFragment([
            "user_id" => $cartItem->user_id,
            "product_id" => $cartItem->product_id,
            "quantity" => $cartItem->quantity,
        ]);
    });

    it("returns 404 for non-existent cart item", function () {
        actingAs();
        $this->getJson("/api/cart/999999")->assertNotFound();
    });
});

describe("POST /api/cart", function () {
    it("adds a new product to cart", function () {
        $user = actingAs();
        $product = Product::factory()->create();

        $response = $this->postJson("/api/cart", [
            "product_id" => $product->id,
            "quantity" => 2,
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                "data" => ["id", "user_id", "product_id", "quantity"],
            ])
            ->assertJsonPath("data.user_id", $user->id)
            ->assertJsonPath("data.product_id", $product->id)
            ->assertJsonPath("data.quantity", 2);

        $this->assertDatabaseCount("cart_items", 1);
        $this->assertDatabaseHas("cart_items", [
            "user_id" => $user->id,
            "product_id" => $product->id,
            "quantity" => 2,
        ]);
    });
});

describe("UPDATE /api/cart", function () {
    it("updates quantity", function () {
        $user = actingAs();
        $product = Product::factory()->create();

        $cartItem = CartItem::create([
            "user_id" => $user->id,
            "product_id" => $product->id,
            "quantity" => 1,
        ]);

        $response = $this->patchJson("/api/cart/{$cartItem->id}", [
            "quantity" => 5,
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                "data" => ["id", "user_id", "product_id", "quantity"],
            ])
            ->assertJsonPath("data.quantity", 5);

        $this->assertDatabaseHas("cart_items", [
            "id" => $cartItem->id,
            "user_id" => $user->id,
            "product_id" => $product->id,
            "quantity" => 5,
        ]);
    });

    it("returns 404 for non-existent cart item", function () {
        actingAs();
        $this->patchJson("/api/cart/999999")->assertNotFound();
    });
});

describe("DELETE /api/cart/{id}", function () {
    it("deletes a cart item", function () {
        $user = actingAs();
        $product = Product::factory()->create();

        $cartItem = CartItem::create([
            "user_id" => $user->id,
            "product_id" => $product->id,
            "quantity" => 1,
        ]);

        $this->deleteJson("/api/cart/{$cartItem->id}")->assertNoContent();

        $this->assertDatabaseCount("cart_items", 0);
    });

    it("returns 404 for non-existent cart item", function () {
        actingAs();
        $this->deleteJson("/api/cart/999999")->assertNotFound();
    });
});

describe("AUTH GUARD", function () {
    it("unauthenticated", function (string $method, string $url) {
        $this->$method($url)->assertUnauthorized();
    })->with([
        ["GET", "/api/cart"],
        ["POST", "/api/cart"],
        ["GET", "/api/cart/1"],
        ["PUT", "/api/cart/1"],
        ["DELETE", "/api/cart/1"],
    ]);
});
