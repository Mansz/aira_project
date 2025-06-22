<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\LiveStreamingController;
use App\Http\Controllers\Admin\LiveVoucherController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\Admin\WebhookLogController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest:admin')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth:admin')->group(function () {
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Orders
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('orders.index');
        Route::get('{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::post('{order}/verify-payment', [OrderController::class, 'verifyPayment'])->name('orders.verifyPayment');
        Route::post('{order}/reject-payment', [OrderController::class, 'rejectPayment'])->name('orders.rejectPayment');
        Route::post('{order}/shipping-proof', [OrderController::class, 'uploadShippingProof'])->name('orders.uploadShippingProof');
        Route::delete('{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    });

    // Products
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggleStatus');
    Route::post('products/reorder', [ProductController::class, 'reorder'])->name('products.reorder');

    // Users
    Route::resource('users', UserController::class)->except(['create', 'store']);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');

    // Payment Settings
    Route::prefix('payment-settings')->group(function () {
        Route::get('/', [PaymentSettingsController::class, 'index'])->name('payment-settings.index');
        Route::post('/', [PaymentSettingsController::class, 'store'])->name('payment-settings.store');
        Route::patch('{paymentSetting}/toggle-status', [PaymentSettingsController::class, 'toggleStatus'])
            ->name('payment-settings.toggleStatus');
        Route::delete('{paymentSetting}', [PaymentSettingsController::class, 'destroy'])->name('payment-settings.destroy');
    });

    // Shipping
    Route::prefix('shipping')->group(function () {
        Route::get('/', [ShippingController::class, 'index'])->name('shipping.index');
        Route::post('/', [ShippingController::class, 'store'])->name('shipping.store');
        Route::patch('{shipping}/toggle-status', [ShippingController::class, 'toggleStatus'])
            ->name('shipping.toggleStatus');
        Route::delete('{shipping}', [ShippingController::class, 'destroy'])->name('shipping.destroy');
    });

    // Live Streaming
    Route::prefix('streaming')->group(function () {
        Route::get('/', [LiveStreamingController::class, 'index'])->name('streaming.index');
        Route::get('dashboard', [LiveStreamingController::class, 'dashboard'])->name('streaming.dashboard');
        Route::post('start', [LiveStreamingController::class, 'start'])->name('streaming.start');
        Route::post('end', [LiveStreamingController::class, 'end'])->name('streaming.end');
        Route::post('toggle-product', [LiveStreamingController::class, 'toggleProduct'])->name('streaming.toggleProduct');

        // New routes for pinned product, analytics, order comments
        Route::post('pin-product', [LiveStreamingController::class, 'pinProduct'])->name('live-stream.pin-product');
        Route::post('save-analytics', [LiveStreamingController::class, 'saveAnalytics'])->name('live-stream.save-analytics');
        Route::get('order-comments', [LiveStreamingController::class, 'orderComments'])->name('live-stream.order-comments');
        Route::get('export-order-comments', [LiveStreamingController::class, 'exportOrderComments'])->name('live-stream.export-order-comments');

        // Voucher Management Routes
        Route::prefix('vouchers')->name('streaming.vouchers.')->group(function () {
            Route::get('/', [LiveVoucherController::class, 'index'])->name('index');
            Route::get('/create', [LiveVoucherController::class, 'create'])->name('create');
            Route::post('/', [LiveVoucherController::class, 'store'])->name('store');
            Route::get('/{voucher}/edit', [LiveVoucherController::class, 'edit'])->name('edit');
            Route::put('/{voucher}', [LiveVoucherController::class, 'update'])->name('update');
            Route::post('/{voucher}/toggle-status', [LiveVoucherController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{voucher}', [LiveVoucherController::class, 'destroy'])->name('destroy');
        });

        // Live Orders History Routes
        Route::prefix('orders')->name('streaming.orders.')->group(function () {
            Route::get('/', [LiveStreamingController::class, 'liveOrders'])->name('index');
            Route::get('/export', [LiveStreamingController::class, 'exportLiveOrders'])->name('export');
        });
    });

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [AuthController::class, 'showProfile'])->name('profile.show');
        Route::patch('/', [AuthController::class, 'updateProfile'])->name('profile.update');
        Route::patch('/password', [AuthController::class, 'updatePassword'])->name('profile.password');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('clear-all', [NotificationController::class, 'destroyAll'])->name('clear-all');
    });

    // WhatsApp Management
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/', [WhatsAppController::class, 'index'])->name('index');
        Route::get('logs', [WhatsAppController::class, 'logs'])->name('logs');
        
        // Auto Replies
        Route::get('auto-replies', [WhatsAppController::class, 'autoReplies'])->name('auto-replies');
        Route::post('auto-replies', [WhatsAppController::class, 'storeAutoReply'])->name('auto-replies.store');
        Route::put('auto-replies/{autoReply}', [WhatsAppController::class, 'updateAutoReply'])->name('auto-replies.update');
        Route::delete('auto-replies/{autoReply}', [WhatsAppController::class, 'destroyAutoReply'])->name('auto-replies.destroy');
        
        // Broadcast
        Route::get('broadcast', [WhatsAppController::class, 'broadcast'])->name('broadcast');
        Route::post('broadcast/preview', [WhatsAppController::class, 'previewBroadcast'])->name('broadcast.preview');
        Route::post('broadcast/send', [WhatsAppController::class, 'sendBroadcast'])->name('broadcast.send');
        
        // Conversations
        Route::get('conversation/{phoneNumber}', [WhatsAppController::class, 'conversation'])->name('conversation');
        Route::post('send', [WhatsAppController::class, 'send'])->name('send');
        
        // Message Management
        Route::get('messages/{id}', [WhatsAppController::class, 'show'])->name('messages.show');
        Route::get('export', [WhatsAppController::class, 'export'])->name('export');
        Route::get('statistics', [WhatsAppController::class, 'statistics'])->name('statistics');
    });

    // Webhook Logs
    Route::prefix('webhook-logs')->name('webhook-logs.')->group(function () {
        Route::get('/', [WebhookLogController::class, 'index'])->name('index');
        Route::get('/export', [WebhookLogController::class, 'export'])->name('export');
        Route::get('/{webhookLog}', [WebhookLogController::class, 'show'])->name('show');
    });
});