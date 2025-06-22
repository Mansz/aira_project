<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LiveOrderController;
use App\Http\Controllers\Admin\LiveStreamingController;
use App\Http\Controllers\Admin\LiveVoucherController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\OrderItemController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\WhatsAppController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Admin Authentication Routes
Route::prefix('admin')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('login', [AuthController::class, 'login'])->name('admin.login.submit');
    });

    Route::middleware('auth:admin')->group(function () {
        // Logout
        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');
        
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('admin.dashboard.stats');

        // Live Streaming
        Route::prefix('streaming')->group(function () {
            Route::get('/', [LiveStreamingController::class, 'index'])->name('admin.streaming.index');
            Route::get('/dashboard', [LiveStreamingController::class, 'dashboard'])->name('admin.streaming.dashboard');
            Route::post('/start', [LiveStreamingController::class, 'startStream'])->name('admin.streaming.start');
            Route::post('/end', [LiveStreamingController::class, 'endStream'])->name('admin.streaming.end');
            Route::post('/export-comments', [LiveStreamingController::class, 'exportComments'])->name('admin.streaming.export-comments');
            Route::post('/add-product', [LiveStreamingController::class, 'addProduct'])->name('admin.streaming.add-product');
            Route::post('/remove-product', [LiveStreamingController::class, 'removeProduct'])->name('admin.streaming.remove-product');
            Route::get('create', [LiveStreamingController::class, 'create'])->name('admin.streaming.create');
            Route::post('/admin/streaming/store', [LiveStreamingController::class, 'store'])->name('admin.streaming.store');
        });

        // Product Management
        Route::resource('products', ProductController::class, ['as' => 'admin']);

        // Order Management
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('admin.orders.index');
            Route::get('/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
            Route::put('/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
            Route::post('/{order}/verify-payment', [OrderController::class, 'verifyPayment'])->name('admin.orders.verify-payment');
        });

        // Payment Settings
Route::prefix('settings')->group(function () {
    Route::get('/payment', [PaymentSettingsController::class, 'index'])->name('admin.settings.payment');
    Route::post('/payment', [PaymentSettingsController::class, 'store'])->name('admin.settings.payment.store');
    Route::put('/payment/{setting}', [PaymentSettingsController::class, 'update'])->name('admin.settings.payment.update');
    Route::delete('/payment/{setting}', [PaymentSettingsController::class, 'destroy'])->name('admin.settings.payment.destroy');
    Route::post('/payment/{setting}/toggle', [PaymentSettingsController::class, 'toggleStatus'])->name('admin.settings.payment.toggle'); // Tambahkan toggle status
});
// Rute untuk manajemen pembayaran
Route::prefix('admin/payments')->group(function () {
    Route::get('/', [PaymentController::class, 'index'])->name('admin.payments.index');
    Route::post('/', [PaymentController::class, 'store'])->name('admin.payments.store');
    Route::get('/{payment}/edit', [PaymentController::class, 'edit'])->name('admin.payments.edit');
    Route::put('/{payment}', [PaymentController::class, 'update'])->name('admin.payments.update');
    Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('admin.payments.destroy');
});

Route::prefix('shipping')->group(function () {
    Route::get('/', [ShippingController::class, 'index'])->name('admin.shipping.index');
    Route::post('/calculate', [ShippingController::class, 'calculate'])->name('admin.shipping.calculate');
    Route::put('/{order}/update-shipping', [ShippingController::class, 'updateShipping'])->name('admin.shipping.update');
    Route::post('/update-costs', [ShippingController::class, 'updateCosts'])->name('admin.shipping.updateCosts');
    Route::post('/update-tracking', [ShippingController::class, 'updateTracking'])->name('admin.shipping.updateTracking');
});

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('admin.users.index');
            Route::get('/{user}', [UserController::class, 'show'])->name('admin.users.show');
            Route::put('/{user}/block', [UserController::class, 'toggleBlock'])->name('admin.users.toggle-block');
            Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
        });
        // Order Item Management
Route::prefix('admin/order-items')->group(function () {
    Route::get('/', [OrderItemController::class, 'index'])->name('admin.order_items.index');
    Route::get('/create', [OrderItemController::class, 'create'])->name('admin.order_items.create');
    Route::post('/', [OrderItemController::class, 'store'])->name('admin.order_items.store');
    Route::get('/{orderItem}/edit', [OrderItemController::class, 'edit'])->name('admin.order_items.edit');
    Route::put('/{orderItem}', [OrderItemController::class, 'update'])->name('admin.order_items.update');
    Route::delete('/{orderItem}', [OrderItemController::class, 'destroy'])->name('admin.order_items.delete');
});
// Notifications
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroyAll');
});
Route::prefix('admin/streaming/vouchers')->name('admin.streaming.vouchers.')->group(function () {
    Route::get('/', [LiveVoucherController::class, 'index'])->name('index');
    Route::get('/create', [LiveVoucherController::class, 'create'])->name('create');
    Route::post('/', [LiveVoucherController::class, 'store'])->name('store');
    Route::get('/{voucher}/edit', [LiveVoucherController::class, 'edit'])->name('edit');
    Route::put('/{voucher}', [LiveVoucherController::class, 'update'])->name('update');
    Route::post('/{voucher}/toggle', [LiveVoucherController::class, 'toggleStatus'])->name('toggleStatus');
    Route::delete('/{voucher}', [LiveVoucherController::class, 'destroy'])->name('destroy');
});
Route::prefix('admin/streaming/orders')->name('admin.streaming.orders.')->group(function () {
    Route::get('/', [LiveOrderController::class, 'index'])->name('index');
    Route::get('/create', [LiveOrderController::class, 'create'])->name('create');
    Route::post('/', [LiveOrderController::class, 'store'])->name('store');
    Route::get('/{order}/edit', [LiveOrderController::class, 'edit'])->name('edit');
    Route::put('/{order}', [LiveOrderController::class, 'update'])->name('update');
    Route::delete('/{order}', [LiveOrderController::class, 'destroy'])->name('destroy');
        
    });
    Route::prefix('admin/whatsapp')->name('admin.whatsapp.')->group(function () {
        Route::get('/', [WhatsAppController::class, 'index'])->name('index');
        Route::get('/auto-replies', [WhatsAppController::class, 'autoReplies'])->name('autoReplies');
        Route::post('/auto-replies', [WhatsAppController::class, 'storeAutoReply'])->name('storeAutoReply');
        Route::put('/auto-replies/{autoReply}', [WhatsAppController::class, 'updateAutoReply'])->name('updateAutoReply');
        Route::delete('/auto-replies/{autoReply}', [WhatsAppController::class, 'destroyAutoReply'])->name('destroyAutoReply');
        Route::get('/broadcast', [WhatsAppController::class, 'broadcast'])->name('broadcast');
        Route::post('/broadcast/preview', [WhatsAppController::class, 'previewBroadcast'])->name('previewBroadcast');
        Route::post('/broadcast/send', [WhatsAppController::class, 'sendBroadcast'])->name('sendBroadcast');
        Route::get('/logs', [WhatsAppController::class, 'logs'])->name('logs');
        Route::get('/conversation/{phoneNumber}', [WhatsAppController::class, 'conversation'])->name('conversation');
        Route::post('/conversation/send', [WhatsAppController::class, 'send'])->name('send');
        // Tambahkan rute untuk statistik
    Route::get('/statistics', [WhatsAppController::class, 'statistics'])->name('statistics');
    // Tambahkan rute untuk ekspor
    Route::get('/export', [WhatsAppController::class, 'export'])->name('export');
    });
    
});
});


// Removed duplicate routes below as they are already defined in the admin prefix group above
