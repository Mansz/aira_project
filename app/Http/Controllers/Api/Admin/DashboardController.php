<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $stats = $this->getDashboardStats();
            
            return response()->json([
                'data' => $stats,
                'message' => 'Dashboard data retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stats()
    {
        try {
            $stats = $this->getDashboardStats();
            
            return response()->json([
                'data' => $stats,
                'message' => 'Dashboard stats retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getDashboardStats()
    {
        // Total orders
        $totalOrders = Order::count();
        
        // Total revenue
        $totalRevenue = Order::whereIn('status', ['Selesai', 'Dikirim'])
            ->sum('total_price');
        
        // Pending orders
        $pendingOrders = Order::where('status', 'Menunggu Pembayaran')->count();
        
        // Total products
        $totalProducts = Product::count();
        
        // Total users
        $totalUsers = User::count();
        
        // Orders by status
        $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        
        // Recent orders
        $recentOrders = Order::with(['user'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->user->name ?? 'Unknown',
                    'total_price' => $order->total_price,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toISOString(),
                ];
            });
        
        // Monthly revenue (last 6 months)
        $monthlyRevenue = Order::whereIn('status', ['Selesai', 'Dikirim'])
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    'revenue' => $item->revenue,
                ];
            });

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'pending_orders' => $pendingOrders,
            'total_products' => $totalProducts,
            'total_users' => $totalUsers,
            'orders_by_status' => $ordersByStatus,
            'recent_orders' => $recentOrders,
            'monthly_revenue' => $monthlyRevenue,
        ];
    }
}
