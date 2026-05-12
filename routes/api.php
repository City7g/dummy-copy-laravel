<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IntroController;
use App\Http\Controllers\Api\ProductController;

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
