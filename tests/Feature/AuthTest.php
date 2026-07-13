<?php

use App\Models\User;

describe("POST /api/auth/register", function () {
    it("registers a new user and returns token", function () {
        $payload = [
            "name" => fake()->name(),
            "email" => fake()->unique()->safeEmail(),
            "password" => fake()->password(),
        ];

        $response = $this->postJson("/api/auth/register", $payload);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                "user" => ["id", "name", "email"],
                "token",
            ])
            ->assertJsonMissingPath("user.password")
            ->assertJsonPath("user.name", $payload["name"])
            ->assertJsonPath("user.email", $payload["email"]);

        $this->assertDatabaseHas("users", [
            "name" => $payload["name"],
            "email" => $payload["email"],
        ]);
    });

    it("fails with missing fields", function () {
        $this->postJson("/api/auth/register", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(["name", "email", "password"]);
    });

    it("fails when email is already taken", function () {
        $existing = User::factory()->create();

        $payload = [
            "name" => fake()->name(),
            "email" => $existing->email,
            "password" => fake()->password(),
        ];

        $this->postJson("/api/auth/register", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(["email"]);
    });
});

describe("POST /api/auth/login", function () {
    it("returns token on valid credentials", function () {
        $password = fake()->password();
        $user = User::factory()->create(["password" => $password]);

        $this->postJson("/api/auth/login", [
            "email" => $user->email,
            "password" => $password,
        ])
            ->assertOk()
            ->assertJsonStructure([
                "user" => ["id", "name", "email"],
                "token",
            ])
            ->assertJsonMissingPath("user.password");
    });

    it("returns 401 on invalid credentials", function () {
        $user = User::factory()->create();

        $this->postJson("/api/auth/login", [
            "email" => $user->email,
            "password" => "wrong password",
        ])
            ->assertUnauthorized()
            ->assertJsonPath("message", "Invalid credentials.");
    });

    it("fails with missing fields", function () {
        $this->postJson("/api/auth/login", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(["email", "password"]);
    });
});

describe("GET /api/auth/me", function () {
    it("returns the authenticated user", function () {
        $user = actingAs();

        $this->getJson("/api/auth/me")
            ->assertOk()
            ->assertJsonPath("user.id", $user->id)
            ->assertJsonPath("user.email", $user->email)
            ->assertJsonMissingPath("user.password");
    });

    it("returns 401 when unauthenticated", function () {
        $this->getJson("/api/auth/me")->assertUnauthorized();
    });
});

describe("POST /api/auth/logout", function () {
    it("revokes the current token", function () {
        $user = User::factory()->create();

        $token = $user->createToken("test")->plainTextToken;

        $this->assertDatabaseCount("personal_access_tokens", 1);

        $this->withToken($token)
            ->postJson("/api/auth/logout")
            ->assertOk()
            ->assertJsonPath("message", "Logged out successfully.");

        $this->assertDatabaseCount("personal_access_tokens", 0);
    });

    it("returns 401 when unauthenticated", function () {
        $this->postJson("/api/auth/logout")->assertUnauthorized();
    });
});
