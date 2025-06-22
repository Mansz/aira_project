<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\LiveStream;
use App\Models\OrderItem; // Pastikan ini ada
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_products' => Product::count(),
            'active_orders' => Order::whereIn('status', ['pending', 'processing', 'shipping'])->count(),
            'live_streams' => LiveStream::where('status', 'active')->count(),
            'recent_transactions' => Order::with(['user', 'items'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_price'), // Ganti total_amount ke total_price
            'pending_payments' => Order::where('status', 'pending')->count(),
            // Monthly orders statistics
            'monthly_orders' => Order::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('count', 'month')
                ->toArray(),
            // Best selling products
            'best_selling_products' => OrderItem::selectRaw('product_id, SUM(quantity) as total_sold')
                ->with('product:id,name,price')
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get(),
            // Total transactions count
            'total_transactions' => Order::where('status', 'completed')->count(),
        ];

        // Get active live stream if any
        $activeStream = LiveStream::where('status', 'active')
            ->with('products')
            ->first();

        return view('admin.dashboard', compact('stats', 'activeStream'));
    }

    public function getRealtimeStats()
    {
        // For AJAX updates of dashboard stats
        return response()->json([
            'active_orders' => Order::whereIn('status', ['pending', 'processing', 'shipping'])->count(),
            'live_viewers' => LiveStream::where('status', 'active')->sum('viewer_count'),
            'recent_orders' => Order::with(['user'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
            'monthly_orders' => Order::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->pluck('count', 'month')
                ->toArray(),
            'best_selling_products' => OrderItem::selectRaw('product_id, SUM(quantity) as total_sold')
                ->with('product:id,name,price')
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get(),
            'total_transactions' => Order::where('status', 'completed')->count(),
        ]);
    }
}