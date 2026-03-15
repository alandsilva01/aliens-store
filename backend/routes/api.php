<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Rotas publicas
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Products
    Route::get('/products',                             [ProductController::class, 'index']);
    Route::get('/products/categories',                  [ProductController::class, 'categories']);
    Route::post('/products',                            [ProductController::class, 'store']);
    Route::get('/products/{product}',                   [ProductController::class, 'show']);
    Route::put('/products/{product}/data',              [ProductController::class, 'updateData']);
    Route::post('/products/{product}/images',           [ProductController::class, 'uploadImages']);
    Route::patch('/products/{product}/toggle-active',   [ProductController::class, 'toggleActive']);
    Route::delete('/products/{product}',                [ProductController::class, 'destroy']);
    Route::delete('/products/{product}/images/{image}', [ProductController::class, 'removeImage']);

    // Users
    Route::get('/users',            [UserController::class, 'index']);
    Route::post('/users',           [UserController::class, 'store']);
    Route::put('/users/{user}',     [UserController::class, 'update']);
    Route::delete('/users/{user}',  [UserController::class, 'destroy']);
});
