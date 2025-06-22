<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderComplaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = OrderComplaint::with(['order', 'resolvedBy'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                return $query->whereHas('order', function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhere('order_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $complaints
        ]);
    }

    public function show(OrderComplaint $complaint)
    {
        $complaint->load(['order', 'resolvedBy']);
        
        return response()->json([
            'success' => true,
            'data' => $complaint
        ]);
    }

    public function resolve(Request $request, OrderComplaint $complaint)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        $complaint->resolve($request->user(), $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Complaint resolved successfully',
            'data' => $complaint->fresh(['order', 'resolvedBy'])
        ]);
    }

    public function reject(Request $request, OrderComplaint $complaint)
    {
        $request->validate([
            'notes' => 'required|string|max:500'
        ]);

        $complaint->reject($request->user(), $request->notes);

        return response()->json([
            'success' => true,
            'message' => 'Complaint rejected successfully',
            'data' => $complaint->fresh(['order', 'resolvedBy'])
        ]);
    }

    public function process(OrderComplaint $complaint)
    {
        $complaint->update([
            'status' => 'Processing'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint status updated to processing',
            'data' => $complaint->fresh(['order', 'resolvedBy'])
        ]);
    }

    public function stats()
    {
        $stats = [
            'total' => OrderComplaint::count(),
            'pending' => OrderComplaint::where('status', 'Pending')->count(),
            'processing' => OrderComplaint::where('status', 'Processing')->count(),
            'resolved' => OrderComplaint::where('status', 'Resolved')->count(),
            'rejected' => OrderComplaint::where('status', 'Rejected')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
