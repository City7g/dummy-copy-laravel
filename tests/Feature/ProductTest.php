<?php

use App\Models\Product;
use App\Models\Tag;

function productPayload(array $overrides = []): array
{
    return array_merge(
        [
            "title" => "Test Product",
            "description" => "Some description",
            "price" => 99.99,
            "stock" => 10,
            "rating" => 5,
            "tags" => [],
        ],
        $overrides,
    );
}

describe("GET /api/products", function () {
    it(
        "returns paginated list of products with correct resource structure",
        function () {
            $products = Product::factory(5)->create();

            $response = $this->getJson("/api/products");

            $response->assertOk()->assertJsonStructure([
                "data" => [
                    "*" => [
                        "id",
                        "title",
                        "description",
                        "price",
                        "stock",
                        "rating",
                        "tags" => ["*" => ["id", "name", "slug"]],
                        "created_at",
                        "updated_at",
                    ],
                ],
                "links" => ["first", "last", "prev", "next"],
                "meta" => ["current_page", "total", "per_page", "last_page"],
            ]);

            expect($response->json("data"))->toHaveCount(5);
        },
    );

    it("returns empty data when no products exist", function () {
        $this->getJson("/api/products")
            ->assertOk()
            ->assertJsonPath("data", [])
            ->assertJsonPath("meta.total", 0);
    });
});

describe("GET /api/products/{id}", function () {
    it("returns a single product with correct resource structure", function () {
        $product = Product::factory()->create();

        $this->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonStructure([
                "data" => [
                    "id",
                    "title",
                    "description",
                    "price",
                    "stock",
                    "tags" => ["*" => ["id", "name", "slug"]],
                    "created_at",
                    "updated_at",
                ],
            ])
            ->assertJsonPath("data.id", $product->id)
            ->assertJsonPath("data.title", $product->title);
    });

    it("returns 404 for non-existent product", function () {
        $this->getJson("/api/products/999999")->assertNotFound();
    });
});

describe("POST /api/products", function () {
    it("creates a product and returns 201 with resource", function () {
        $tags = Tag::factory(3)->create();

        $payload = productPayload([
            "tags" => $tags->pluck("id")->toArray(),
        ]);

        $response = $this->postJson("/api/products", $payload);

        $response
            ->assertCreated()
            ->assertJsonStructure(["data" => ["id", "tags"]])
            ->assertJsonPath("data.title", $payload["title"])
            ->assertJsonPath("data.price", $payload["price"])
            ->assertJsonPath("data.rating", $payload["rating"])
            ->assertJsonPath("data.stock", $payload["stock"]);

        $this->assertDatabaseHas("products", [
            "title" => $payload["title"],
            "price" => $payload["price"],
            "stock" => $payload["stock"],
            "rating" => $payload["rating"],
        ]);
    });

    it("validates product fields", function ($payload, $errors) {
        $this->postJson("/api/products", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errors);
    })->with([
        "required fields" => [[], ["title", "price", "stock"]],
        "short title" => [productPayload(["title" => "A"]), ["title"]],
        "max title length" => [
            productPayload(["title" => str_repeat("a", 256)]),
            ["title"],
        ],
        "price not numeric" => [productPayload(["price" => "free"]), ["price"]],
        "price negative" => [productPayload(["price" => -1]), ["price"]],
        "stock not integer" => [productPayload(["stock" => "many"]), ["stock"]],
        "stock negative" => [productPayload(["stock" => -5]), ["stock"]],
        "rating not numeric" => [
            productPayload(["rating" => "high"]),
            ["rating"],
        ],
        "rating out of range" => [productPayload(["rating" => 6]), ["rating"]],
        "invalid tags" => [productPayload(["tags" => ["invalid"]]), ["tags.0"]],
        "non-existent tags" => [productPayload(["tags" => [999]]), ["tags.0"]],
    ]);
});

describe("PATCH/PUT /api/products/{id}", function () {
    it("updates a product and returns the updated resource", function () {
        $product = Product::factory()->create();
        $tags = Tag::factory(3)->create();
        $payload = productPayload([
            "title" => "Updated Name",
            "price" => 199.99,
            "tags" => $tags->pluck("id")->toArray(),
        ]);

        $this->putJson("/api/products/{$product->id}", $payload)
            ->assertStatus(200)
            ->assertJsonPath("data.title", "Updated Name")
            ->assertJsonPath("data.price", 199.99)
            ->assertJsonPath("data.tags.0.id", 1)
            ->assertJsonPath("data.tags.1.id", 2)
            ->assertJsonPath("data.tags.2.id", 3);

        $this->assertDatabaseHas("products", [
            "id" => $product->id,
            "title" => "Updated Name",
            "price" => 199.99,
        ]);
    });

    it("returns 404 when updating non-existent product", function () {
        $this->putJson(
            "/api/products/999999",
            productPayload(),
        )->assertNotFound();
    });
});

describe("DELETE /api/products/{id}", function () {
    it("deletes a product and returns 204", function () {
        $product = Product::factory()->create();

        $this->deleteJson("/api/products/{$product->id}")->assertNoContent();

        $this->assertDatabaseMissing("products", ["id" => $product->id]);
    });

    it("returns 404 when deleting non-existent product", function () {
        $this->deleteJson("/api/products/999999")->assertNotFound();
    });
});
