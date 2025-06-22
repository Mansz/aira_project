<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\StreamingController as AdminStreamingController;
use App\Http\Controllers\Api\Admin\SettingController;
use App\Http\Controllers\Api\Admin\WhatsAppController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\LiveStreamingController;
use App\Http\Controllers\Admin\StreamingController;
use App\Http\Controllers\Admin\PaymentSettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Include public API routes (includes auth routes with rate limiting)
    require __DIR__.'/api_v1_public.php';
    
    // User Protected Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::get('auth/user', [AuthController::class, 'user']);
    });
    
    // Admin Protected Routes
    Route::middleware(['auth:sanctum', 'admin.permission'])->group(function () {
        Route::post('auth/admin/logout', [\App\Http\Controllers\Api\Admin\AuthController::class, 'logout']);
        Route::post('auth/admin/refresh', [\App\Http\Controllers\Api\Admin\AuthController::class, 'refresh']);
        Route::get('auth/admin/profile', [\App\Http\Controllers\Api\Admin\AuthController::class, 'profile']);
        
        // Admin Routes
        Route::prefix('admin')->group(function () {
            // Dashboard
            Route::get('dashboard', [DashboardController::class, 'index']);
            Route::get('dashboard/stats', [DashboardController::class, 'stats']);
            
            // Admin Management (Super Admin Only)
            Route::middleware('admin.permission:manage_admins')->group(function () {
                Route::get('admins', [AdminController::class, 'index']);
                Route::post('admins', [AdminController::class, 'store']);
                Route::put('admins/{admin}', [AdminController::class, 'update']);
                Route::delete('admins/{admin}', [AdminController::class, 'destroy']);
                Route::patch('admins/{admin}/toggle-status', [AdminController::class, 'toggleStatus']);
                Route::get('permissions', [AdminController::class, 'permissions']);
            });
            
            // Products
            Route::middleware('admin.permission:manage_products')->group(function () {
                Route::apiResource('products', \App\Http\Controllers\Api\Admin\ProductController::class);
                Route::get('products/categories', [\App\Http\Controllers\Api\Admin\ProductController::class, 'categories']);
            });
            
            // Product Categories
            Route::middleware('admin.permission:manage_products')->group(function () {
                Route::apiResource('categories', \App\Http\Controllers\Api\Admin\ProductCategoryController::class);
                Route::patch('categories/{category}/toggle-status', [\App\Http\Controllers\Api\Admin\ProductCategoryController::class, 'toggleStatus']);
            });
            
            // Orders
            Route::middleware('admin.permission:manage_orders')->group(function () {
                Route::get('orders', [AdminOrderController::class, 'index']);
                Route::get('orders/stats', [AdminOrderController::class, 'stats']);
                Route::get('orders/{id}', [AdminOrderController::class, 'show']);
                Route::patch('orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
                Route::patch('orders/{id}/shipping', [AdminOrderController::class, 'updateShipping']);
            });
            
            // Payments
            Route::middleware('admin.permission:manage_orders')->group(function () {
                Route::get('payments', [\App\Http\Controllers\Api\Admin\PaymentController::class, 'index']);
                Route::get('payments/{payment}', [\App\Http\Controllers\Api\Admin\PaymentController::class, 'show']);
                Route::post('payments/{payment}/verify', [\App\Http\Controllers\Api\Admin\PaymentController::class, 'verify']);
                Route::post('payments/{payment}/reject', [\App\Http\Controllers\Api\Admin\PaymentController::class, 'reject']);
            });
            
            // Live Streaming
            Route::middleware('admin.permission:manage_streaming')->prefix('streaming')->group(function () {
                Route::get('/', [AdminStreamingController::class, 'index']);
                Route::post('start', [AdminStreamingController::class, 'start']);
                Route::post('end', [AdminStreamingController::class, 'end']);
                Route::get('stats', [AdminStreamingController::class, 'stats']);
                Route::get('active', [AdminStreamingController::class, 'active']);
                Route::post('message', [AdminStreamingController::class, 'sendMessage']);
                
                // Orders
                Route::get('orders', [AdminStreamingController::class, 'orders']);
                Route::post('orders/{orderId}/confirm', [AdminStreamingController::class, 'confirmLiveOrder']);
                Route::patch('orders/{orderId}/status', [AdminStreamingController::class, 'updateLiveOrderStatus']);
                
                // Vouchers
                Route::get('vouchers', [AdminStreamingController::class, 'vouchers']);
                
                // Comments
                Route::get('comments', [AdminStreamingController::class, 'comments']);
                Route::delete('comments/{commentId}', [AdminStreamingController::class, 'deleteComment']);
                
                // Stream Token
                Route::post('token', [AdminStreamingController::class, 'getStreamToken']);
                
                // Product Management
                Route::post('pin-product', [AdminStreamingController::class, 'pinProduct']);
                Route::post('unpin-product', [AdminStreamingController::class, 'unpinProduct']);
                
                // Analytics
                Route::post('analytics', [AdminStreamingController::class, 'saveAnalytics']);
                
                // Stream Management
                Route::get('{id}', [AdminStreamingController::class, 'show']);
                Route::put('{id}', [AdminStreamingController::class, 'update']);
                Route::delete('{id}', [AdminStreamingController::class, 'destroy']);
            });
            
            // WhatsApp
            Route::middleware('admin.permission:manage_whatsapp')->prefix('whatsapp')->group(function () {
                // Messages
                Route::get('/', [WhatsAppController::class, 'index']);
                Route::post('/', [WhatsAppController::class, 'store']);
                Route::get('/{message}', [WhatsAppController::class, 'show']);
                Route::get('/stats', [WhatsAppController::class, 'getStats']);

                // Auto Replies
                Route::get('/auto-replies', [WhatsAppController::class, 'getAutoReplies']);
                Route::post('/auto-replies', [WhatsAppController::class, 'storeAutoReply']);
                Route::put('/auto-replies/{autoReply}', [WhatsAppController::class, 'updateAutoReply']);
                Route::delete('/auto-replies/{autoReply}', [WhatsAppController::class, 'deleteAutoReply']);
                Route::patch('/auto-replies/{autoReply}/toggle', [WhatsAppController::class, 'toggleAutoReply']);
            });

            // Shipping
            Route::middleware('admin.permission:manage_orders')->prefix('shipments')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\Admin\ShipmentController::class, 'index']);
                Route::get('/stats', [\App\Http\Controllers\Api\Admin\ShipmentController::class, 'stats']);
                Route::get('/{shipment}', [\App\Http\Controllers\Api\Admin\ShipmentController::class, 'show']);
                Route::put('/{shipment}/status', [\App\Http\Controllers\Api\Admin\ShipmentController::class, 'updateStatus']);
            });

            // User Management
            Route::middleware('admin.permission:manage_customers')->prefix('users')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\Admin\UserController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\Api\Admin\UserController::class, 'store']);
                Route::get('/stats', [\App\Http\Controllers\Api\Admin\UserController::class, 'stats']);
                Route::get('/{user}', [\App\Http\Controllers\Api\Admin\UserController::class, 'show']);
                Route::put('/{user}', [\App\Http\Controllers\Api\Admin\UserController::class, 'update']);
                Route::delete('/{user}', [\App\Http\Controllers\Api\Admin\UserController::class, 'destroy']);
                Route::patch('/{user}/toggle-status', [\App\Http\Controllers\Api\Admin\UserController::class, 'toggleStatus']);
            });

            // Settings
            Route::middleware('admin.permission:manage_settings')->prefix('settings')->group(function () {
                Route::get('/', [SettingController::class, 'index']);
                Route::get('/group/{group}', [SettingController::class, 'getByGroup']);
                Route::post('/batch', [SettingController::class, 'batchUpdate']);
                Route::post('/', [SettingController::class, 'store']);
                Route::put('/{key}', [SettingController::class, 'update']);
                Route::delete('/{key}', [SettingController::class, 'destroy']);
                Route::get('/payment', [\App\Http\Controllers\Api\Admin\PaymentSettingController::class, 'index']);
                Route::post('/payment', [\App\Http\Controllers\Api\Admin\PaymentSettingController::class, 'store']);
                Route::put('/payment/{paymentSetting}', [\App\Http\Controllers\Api\Admin\PaymentSettingController::class, 'update']);
                Route::delete('/payment/{paymentSetting}', [\App\Http\Controllers\Api\Admin\PaymentSettingController::class, 'destroy']);
                Route::patch('/payment/{paymentSetting}/toggle-status', [\App\Http\Controllers\Api\Admin\PaymentSettingController::class, 'toggleStatus']);
            });

            // Payment Proofs
            Route::middleware('admin.permission:manage_orders')->prefix('payment-proofs')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\Admin\PaymentProofController::class, 'index']);
                Route::get('/{paymentProof}', [\App\Http\Controllers\Api\Admin\PaymentProofController::class, 'show']);
                Route::put('/{paymentProof}/verify', [\App\Http\Controllers\Api\Admin\PaymentProofController::class, 'verify']);
                Route::put('/{paymentProof}/reject', [\App\Http\Controllers\Api\Admin\PaymentProofController::class, 'reject']);
            });

            // Order Complaints
            Route::middleware('admin.permission:manage_orders')->prefix('order-complaints')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\Admin\OrderComplaintController::class, 'index']);
                Route::get('/stats', [\App\Http\Controllers\Api\Admin\OrderComplaintController::class, 'stats']);
                Route::get('/{complaint}', [\App\Http\Controllers\Api\Admin\OrderComplaintController::class, 'show']);
                Route::put('/{complaint}/resolve', [\App\Http\Controllers\Api\Admin\OrderComplaintController::class, 'resolve']);
                Route::put('/{complaint}/reject', [\App\Http\Controllers\Api\Admin\OrderComplaintController::class, 'reject']);
                Route::put('/{complaint}/process', [\App\Http\Controllers\Api\Admin\OrderComplaintController::class, 'process']);
            });
        });
    });
});

// Fallback for undefined routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route not found'
    ], 404);
});
