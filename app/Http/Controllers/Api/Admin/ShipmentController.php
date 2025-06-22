<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Shipment::with('items');

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('courier_tracking_number', 'like', "%{$search}%");
                });
            }

            $shipments = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $shipments->map(function ($shipment) {
                    return $shipment->toArray();
                }),
                'message' => 'Shipments retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve shipments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve shipments',
            ], 500);
        }
    }

    public function show(Shipment $shipment)
    {
        try {
            $shipment->load('items');

            return response()->json([
                'success' => true,
                'data' => $shipment->toArray(),
                'message' => 'Shipment retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve shipment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve shipment',
            ], 500);
        }
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        try {
            $request->validate([
                'status' => 'required|in:processing,in_transit,out_for_delivery,delivered',
            ]);

            $shipment->update([
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'data' => $shipment->toArray(),
                'message' => 'Shipment status updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update shipment status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shipment status',
            ], 500);
        }
    }

    public function stats()
    {
        try {
            $stats = [
                'totalShipments' => Shipment::count(),
                'processing' => Shipment::where('status', 'processing')->count(),
                'inTransit' => Shipment::where('status', 'in_transit')->count(),
                'outForDelivery' => Shipment::where('status', 'out_for_delivery')->count(),
                'delivered' => Shipment::where('status', 'delivered')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Shipment stats retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve shipment stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve shipment stats',
            ], 500);
        }
    }
}
