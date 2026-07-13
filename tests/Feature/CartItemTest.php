<?php

use App\Models\User;
use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Factories\Sequence;

describe("GET /api/cart", function () {
    it("get only user cart items", function () {
        $user = actingAs();
        $otherUser = User::factory()->create();

        $cartItems = CartItem::factory(6)
            ->state(
                new Sequence(
                    ["user_id" => $user->id],
                    ["user_id" => $otherUser->id],
                ),
            )
            ->create();

        $response = $this->getJson("/api/cart");

        $response
            ->assertOk()
            ->assertJsonStructure([
                "data" => [
                    "*" => ["id", "user_id", "product_id", "quantity"],
                ],
            ])
            ->assertJsonCount(3, "data");

        $cartItems->where("user_id", 1)->each(
            fn($cartItem) => $response->assertJsonFragment([
                "user_id" => $cartItem->user_id,
                "product_id" => $cartItem->product_id,
                "quantity" => 1,
            ]),
        );

        $this->assertDatabaseCount("cart_items", 6);

        $cartItems->each(
            fn($cartItem) => $this->assertDatabaseHas("cart_items", [
                "id" => $cartItem->id,
                "user_id" => $cartItem->user_id,
                "product_id" => $cartItem->product_id,
                "quantity" => 1,
            ]),
        );
    });

    it("get empty cart", function () {
        actingAs();

        CartItem::factory()->create();

        $this->getJson("/api/cart")
            ->assertOk()
            ->assertJson([
                "data" => [],
            ]);
    });
});

describe("GET /api/cart/{id}", function () {
    it("get specific my cart item", function () {
        $user = actingAs();
        $cartItem = CartItem::factory()->for($user)->create();

        $response = $this->getJson("/api/cart/{$cartItem->id}");

        $response->assertOk()->assertJsonStructure([
            "data" => ["id", "user_id", "product_id", "quantity"],
        ]);

        $response->assertJsonFragment([
            "user_id" => $cartItem->user_id,
            "product_id" => $cartItem->product_id,
            "quantity" => 1,
        ]);
    });

    it("get 404 if cart item does not exist", function () {
        actingAs();

        $this->getJson("/api/cart/999999")->assertNotFound();
    });

    it("get 404 if cart item not mine", function () {
        actingAs();
        $cartItem = CartItem::factory()->create();

        $this->getJson("/api/cart/{$cartItem->id}")->assertNotFound();
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
            ->assertJsonFragment([
                "user_id" => $user->id,
                "product_id" => $product->id,
                "quantity" => 2,
            ]);

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
        $cartItem = CartItem::factory()->for($user)->create();

        $response = $this->patchJson("/api/cart/{$cartItem->id}", [
            "quantity" => 5,
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                "data" => ["id", "user_id", "product_id", "quantity"],
            ])
            ->assertJsonFragment(["quantity" => 5]);

        $this->assertDatabaseHas("cart_items", [
            "user_id" => $user->id,
            "product_id" => $cartItem->product_id,
            "quantity" => 5,
        ]);
    });

    it("not allow change product_id", function () {
        $user = actingAs();
        $cartItem = CartItem::factory()->for($user)->create();
        $product = Product::factory()->create();

        $response = $this->patchJson("/api/cart/{$cartItem->id}", [
            "product_id" => $product->id,
            "quantity" => 5,
        ])->assertJsonValidationErrors(['product_id']);
    });

    it("returns 404 for non-existent cart item", function () {
        actingAs();

        $this->patchJson("/api/cart/999999", [
            "quantity" => 5,
        ])->assertNotFound();
    });

    it("get 404 if cart item not mine", function () {
        actingAs();
        $cartItem = CartItem::factory()->create();

        $this->patchJson("/api/cart/{$cartItem->id}", [
            "quantity" => 5,
        ])->assertNotFound();
    });
});

describe("DELETE /api/cart/{id}", function () {
    it("deletes a cart item", function () {
        $user = actingAs();
        $cartItem = CartItem::factory()->for($user)->create();

        $this->assertDatabaseCount("cart_items", 1);

        $this->deleteJson("/api/cart/{$cartItem->id}")->assertNoContent();

        $this->assertDatabaseCount("cart_items", 0);
    });

    it("returns 404 for non-existent cart item", function () {
        actingAs();

        $this->deleteJson("/api/cart/999999")->assertNotFound();
    });

    it("get 404 if cart item not mine", function () {
        actingAs();
        $cartItem = CartItem::factory()->create();

        $this->deleteJson("/api/cart/{$cartItem->id}")->assertNotFound();
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
