<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ShippingController extends Controller
{
    /**
     * Get all shipments with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Shipment::with(['order.orderItems.product'])
            ->latest();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }

        $shipments = $query->get();

        return response()->json([
            'data' => $shipments->map->toShipmentResponse(),
            'message' => 'Shipments retrieved successfully'
        ]);
    }

    /**
     * Get shipping statistics
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'totalShipments' => Shipment::count(),
            'processing' => Shipment::where('status', Shipment::STATUS_PROCESSING)->count(),
            'inTransit' => Shipment::where('status', Shipment::STATUS_IN_TRANSIT)->count(),
            'outForDelivery' => Shipment::where('status', Shipment::STATUS_OUT_FOR_DELIVERY)->count(),
            'delivered' => Shipment::where('status', Shipment::STATUS_DELIVERED)->count(),
        ];

        return response()->json([
            'data' => $stats,
            'message' => 'Shipping stats retrieved successfully'
        ]);
    }

    /**
     * Get specific shipment details
     */
    public function show($id): JsonResponse
    {
        // Extract order ID from shipment ID (SHP-001 -> 1)
        $shipmentId = (int) str_replace('SHP-', '', $id);
        
        $shipment = Shipment::with(['order.orderItems.product'])
            ->findOrFail($shipmentId);

        return response()->json([
            'success' => true,
            'data' => $shipment->toShipmentResponse(),
        ]);
    }

    /**
     * Update shipping status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Shipment::getStatuses())),
        ]);

        // Extract shipment ID
        $shipmentId = (int) str_replace('SHP-', '', $id);
        
        $shipment = Shipment::findOrFail($shipmentId);
        
        try {
            DB::beginTransaction();
            
            $shipment->status = $request->status;
            $shipment->save();

            // Update order status if needed
            if ($request->status === Shipment::STATUS_DELIVERED) {
                $shipment->order->update(['status' => 'delivered']);
            } elseif ($request->status === Shipment::STATUS_IN_TRANSIT) {
                $shipment->order->update(['status' => 'shipped']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shipping status updated successfully',
                'data' => $shipment->toShipmentResponse(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shipping status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update tracking number and courier info
     */
    public function updateTracking(Request $request, $id): JsonResponse
    {
        $request->validate([
            'tracking_number' => 'required|string|max:255',
            'courier_name' => 'nullable|string|max:100',
            'courier_service' => 'nullable|string|max:100',
        ]);

        // Extract shipment ID
        $shipmentId = (int) str_replace('SHP-', '', $id);
        
        $shipment = Shipment::findOrFail($shipmentId);
        
        try {
            $shipment->update([
                'tracking_number' => $request->tracking_number,
                'courier_name' => $request->courier_name,
                'courier_service' => $request->courier_service,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tracking information updated successfully',
                'data' => $shipment->toShipmentResponse(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tracking information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create shipment for order
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'courier_name' => 'nullable|string|max:100',
            'courier_service' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::findOrFail($request->order_id);
            
            $shipment = Shipment::create([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'street' => $request->street,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'courier_name' => $request->courier_name,
                'courier_service' => $request->courier_service,
                'tracking_number' => $request->tracking_number,
                'status' => Shipment::STATUS_PROCESSING,
            ]);

            // Update order status
            $order->update(['status' => 'processing']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shipment created successfully',
                'data' => $shipment->toShipmentResponse(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create shipment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
