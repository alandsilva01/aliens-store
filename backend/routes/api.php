<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post("/login", [AuthController::class, "login"]);
Route::post("/register", [AuthController::class, "register"]);

Route::middleware("auth:sanctum")->group(function () {
    Route::post("/logout", [AuthController::class, "logout"]);
    Route::get("/me", [AuthController::class, "me"]);
    Route::get("/products", [ProductController::class, "index"]);
    Route::get("/products/categories", [ProductController::class, "categories"]);
    Route::post("/products", [ProductController::class, "store"]);
    Route::get("/products/{product}", [ProductController::class, "show"]);
    Route::post("/products/{product}", [ProductController::class, "update"]);
    Route::patch("/products/{product}/toggle-active", [ProductController::class, "toggleActive"]);
    Route::delete("/products/{product}", [ProductController::class, "destroy"]);
    Route::delete("/products/{product}/images/{image}", [ProductController::class, "removeImage"]);
});

// Users CRUD
Route::middleware("auth:sanctum")->group(function () {
    Route::get("/users",         [App\Http\Controllers\UserController::class, "index"]);
    Route::post("/users",        [App\Http\Controllers\UserController::class, "store"]);
    Route::put("/users/{user}",  [App\Http\Controllers\UserController::class, "update"]);
    Route::delete("/users/{user}", [App\Http\Controllers\UserController::class, "destroy"]);
});
