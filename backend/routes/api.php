<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\AnalyticsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public routes
Route::post('/auth/login',    [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// Products (public — read)
Route::get('/products',             [ProductController::class, 'index']);
Route::get('/products/recommended', [ProductController::class, 'recommended']);
Route::get('/products/{product}',   [ProductController::class, 'show']);

// Search (public)
Route::get('/search', [SearchController::class, 'search']);

// Analytics (public for demo)
Route::get('/analytics', [AnalyticsController::class, 'index']);

// Chat — streaming endpoint (public for demo, add auth middleware in production)
Route::post('/chat', [ChatController::class, 'stream']);

Route::get('/up', function () {
    return response()->json(['status' => 'ok']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Admin product management
    Route::post('/products',            [ProductController::class, 'store']);
    Route::put('/products/{product}',   [ProductController::class, 'update']);
    Route::delete('/products/{product}',[ProductController::class, 'destroy']);
});
