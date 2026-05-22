<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IntroController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\TagController;

/*
|--------------------------------------------------------------------------
| Product API Routes
|--------------------------------------------------------------------------
*/

Route::group([], function () {
    Route::get("test", [IntroController::class, "test"])->name("test");
    Route::get("ip", [IntroController::class, "ip"])->name("ip");
})->name("intro");

Route::apiResource("products", ProductController::class);
Route::apiResource("comments", CommentController::class)->except("store");
Route::apiResource("tags", TagController::class);
