<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\LiveStreamingController;

/*
|--------------------------------------------------------------------------
| Public API Routes (for Android & React.js)
|--------------------------------------------------------------------------
*/

use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

// Add CSRF cookie route for Sanctum
Route::get('sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

// Authentication with rate limiting (10 attempts per minute)
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/google', [AuthController::class, 'googleSignIn']);
    Route::post('auth/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('auth/admin/login', [\App\Http\Controllers\Api\Admin\AuthController::class, 'login']);
});

// Products (Public)
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('products/categories', [ProductController::class, 'categories']);

// Live Streaming (Public)
Route::get('streams/active', [LiveStreamingController::class, 'getActiveStreams']);
Route::get('streams/{streamId}', [LiveStreamingController::class, 'getStreamDetails']);
Route::get('streams/{streamId}/products', [LiveStreamingController::class, 'getStreamProducts']);

// Protected Routes (require auth)
Route::middleware(['auth:sanctum'])->group(function () {
    // User Profile
    Route::get('user/profile', [AuthController::class, 'profile']);
    Route::put('user/profile', [AuthController::class, 'updateProfile']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    
    // Cart
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart/add', [CartController::class, 'add']);
    Route::put('cart/update/{cartItem}', [CartController::class, 'update']);
    Route::delete('cart/remove/{cartItem}', [CartController::class, 'remove']);
    
    // Orders
    Route::get('orders', [OrderController::class, 'userOrders']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('orders/{order}/payment-proof', [OrderController::class, 'uploadPaymentProof']);
    
    // Live Streaming (Auth Required)
    Route::post('streams/{streamId}/join', [LiveStreamingController::class, 'joinStream']);
    Route::post('streams/{streamId}/leave', [LiveStreamingController::class, 'leaveStream']);
    Route::get('streams/{streamId}/comments', [LiveStreamingController::class, 'getComments']);
    Route::post('streams/{streamId}/comments', [LiveStreamingController::class, 'sendComment']);
});

// Rate Limiting for general API calls
Route::middleware(['throttle:60,1'])->group(function () {
    // Add other rate-limited routes here if needed
});
