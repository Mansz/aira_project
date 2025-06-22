<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Order::with(['user', 'items.product', 'payment_proof']);

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Search by order ID or customer name
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            // Sort by created_at desc by default
            $orders = $query->orderBy('created_at', 'desc')
                           ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $orders->items(),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $order = Order::with(['user', 'items.product', 'payment_proof'])
                         ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Order retrieved successfully',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|string|in:Menunggu Pembayaran,Menunggu Konfirmasi,Diproses,Dikirim,Selesai,Dibatalkan'
            ]);

            $order = Order::findOrFail($id);
            
            $oldStatus = $order->status;
            $order->status = $request->status;
            $order->save();

            // Log the status change
            Log::info("Order {$id} status changed from {$oldStatus} to {$request->status}");

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $order->load(['user', 'items.product', 'payment_proof'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateShipping(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'tracking_number' => 'nullable|string|max:255',
                'shipping_courier' => 'nullable|string|max:255',
            ]);

            $order = Order::findOrFail($id);
            
            if ($request->has('tracking_number')) {
                $order->tracking_number = $request->tracking_number;
            }
            
            if ($request->has('shipping_courier')) {
                $order->shipping_courier = $request->shipping_courier;
            }
            
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Shipping information updated successfully',
                'data' => $order->load(['user', 'items.product', 'payment_proof'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shipping information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total_orders' => Order::count(),
                'pending_orders' => Order::whereIn('status', ['Menunggu Pembayaran', 'Menunggu Konfirmasi'])->count(),
                'processing_orders' => Order::where('status', 'Diproses')->count(),
                'shipped_orders' => Order::where('status', 'Dikirim')->count(),
                'completed_orders' => Order::where('status', 'Selesai')->count(),
                'cancelled_orders' => Order::where('status', 'Dibatalkan')->count(),
                'total_revenue' => Order::whereIn('status', ['Selesai', 'Dikirim'])->sum('total_amount'),
                'today_orders' => Order::whereDate('created_at', today())->count(),
                'this_month_orders' => Order::whereMonth('created_at', now()->month)
                                           ->whereYear('created_at', now()->year)
                                           ->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Order statistics retrieved successfully',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
