<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IntroController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\CartItemController;

Route::group([], function () {
    Route::get("test", [IntroController::class, "test"])->name("test");
    Route::get("ip", [IntroController::class, "ip"])->name("ip");
})->name("intro");

Route::post("/auth/register", [AuthController::class, "register"]);
Route::post("/auth/login", [AuthController::class, "login"]);
Route::get("/auth/me", [AuthController::class, "me"])->middleware(
    "auth:sanctum",
);
Route::post("/auth/logout", [AuthController::class, "logout"])->middleware(
    "auth:sanctum",
);

Route::middleware("auth:sanctum")->group(function () {
    Route::get("users", fn() => User::all());
    Route::apiResource("products", ProductController::class);
    Route::apiResource("comments", CommentController::class)->except("store");
    Route::apiResource("tags", TagController::class);
    Route::apiResource("cart", CartItemController::class)->parameters([
        "cart" => "cartItem",
    ]);
});
