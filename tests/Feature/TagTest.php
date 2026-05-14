<?php
use App\Models\Tag;

function tagPayload(array $overrides = []): array
{
    return array_merge(
        [
            "name" => "Test Tag",
        ],
        $overrides,
    );
}

describe("GET /api/tags", function () {
    it(
        "returns paginated list of tags with correct resource structure",
        function () {
            Tag::factory(5)->create();

            $response = $this->getJson("/api/tags");

            $response->assertOk()->assertJsonStructure([
                "data" => [
                    "*" => ["id", "name", "slug", "created_at", "updated_at"],
                ],
                "links" => ["first", "last", "prev", "next"],
                "meta" => ["current_page", "total", "per_page", "last_page"],
            ]);

            expect($response->json("data"))->toHaveCount(5);
        },
    );

    it("returns empty data when no tags exist", function () {
        $this->getJson("/api/tags")
            ->assertOk()
            ->assertJsonPath("data", [])
            ->assertJsonPath("meta.total", 0);
    });
});

describe("GET /api/tags/{id}", function () {
    it("returns a single tag with correct resource structure", function () {
        $tag = Tag::factory()->create();

        $this->getJson("/api/tags/{$tag->id}")
            ->assertOk()
            ->assertJsonStructure([
                "data" => ["id", "name", "slug", "created_at", "updated_at"],
            ])
            ->assertJsonPath("data.id", $tag->id)
            ->assertJsonPath("data.name", $tag->name);
    });

    it("returns 404 for non-existent tag", function () {
        $this->getJson("/api/tags/999999")->assertNotFound();
    });
});

describe("POST /api/tags", function () {
    it("creates a tag and returns 201 with resource", function () {
        $payload = tagPayload();

        $response = $this->postJson("/api/tags", $payload);

        $response->assertCreated()->assertJsonPath("name", $payload["name"]);

        $this->assertDatabaseHas("tags", [
            "name" => $payload["name"],
        ]);
    });

    it("validates tag fields", function ($payload, $errors) {
        $this->postJson("/api/tags", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errors);
    })->with([
        "required fields" => [[], ["name"]],
        "short name" => [tagPayload(["name" => "A"]), ["name"]],
        "max name length" => [
            tagPayload(["name" => str_repeat("a", 256)]),
            ["name"],
        ],
    ]);
});

describe("PATCH/PUT /api/tags/{id}", function () {
    it("updates a tag and returns the updated resource", function () {
        $tag = Tag::factory()->create();
        $payload = tagPayload([
            "name" => "Updated Tag Name",
        ]);

        $this->putJson("/api/tags/{$tag->id}", $payload)
            ->assertOk()
            ->assertJsonPath("name", "Updated Tag Name");

        $this->assertDatabaseHas("tags", [
            "id" => $tag->id,
            "name" => "Updated Tag Name",
        ]);
    });

    it("returns 404 when updating non-existent tag", function () {
        $this->putJson("/api/tags/999999", tagPayload())->assertNotFound();
    });
});

describe("DELETE /api/tags/{id}", function () {
    it("deletes a tag and returns 204", function () {
        $tag = Tag::factory()->create();

        $this->deleteJson("/api/tags/{$tag->id}")->assertNoContent();

        $this->assertDatabaseMissing("tags", ["id" => $tag->id]);
    });

    it("returns 404 when deleting non-existent tag", function () {
        $this->deleteJson("/api/tags/999999")->assertNotFound();
    });
});
