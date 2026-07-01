<?php

use App\Models\User;

function registerPayload(array $overrides = []): array
{
    $password = $overrides["password"] ?? fake()->password();

    return array_merge(
        [
            "name" => fake()->name(),
            "email" => fake()->unique()->safeEmail(),
            "password" => $password,
            "password_confirmation" => $password,
        ],
        $overrides,
    );
}

function loginPayload(User $user, string $password): array
{
    return [
        "email" => $user->email,
        "password" => $password,
    ];
}

describe("POST /api/auth/register", function () {
    it("registers a new user and returns token", function () {
        $payload = registerPayload();

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

        $this->assertDatabaseHas("users", ["email" => $payload["email"]]);
    });

    it("fails with missing fields", function () {
        $this->postJson("/api/auth/register", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(["name", "email", "password"]);
    });

    it("fails when email is already taken", function () {
        $existing = User::factory()->create();

        $this->postJson(
            "/api/auth/register",
            registerPayload(["email" => $existing->email]),
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(["email"]);
    });
});

describe("POST /api/auth/login", function () {
    it("returns token on valid credentials", function () {
        $password = fake()->password();
        $user = User::factory()->create(["password" => $password]);

        $this->postJson("/api/auth/login", loginPayload($user, $password))
            ->assertOk()
            ->assertJsonStructure([
                "user" => ["id", "name", "email"],
                "token",
            ])
            ->assertJsonMissingPath("user.password");
    });

    it("returns 401 on invalid credentials", function () {
        $user = User::factory()->create();

        $this->postJson(
            "/api/auth/login",
            loginPayload($user, "wrong-password"),
        )
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
