<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Payment::with(['order.user', 'verifiedBy']);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $payments = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $payments,
                'message' => 'Payments retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve payments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payments',
            ], 500);
        }
    }

    public function show(Payment $payment)
    {
        try {
            $payment->load(['order.user', 'verifiedBy']);

            return response()->json([
                'success' => true,
                'data' => $payment,
                'message' => 'Payment retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment',
            ], 500);
        }
    }

    public function verify(Request $request, Payment $payment)
    {
        try {
            $admin = auth('sanctum')->user();
            $payment->verify($admin);

            return response()->json([
                'success' => true,
                'data' => $payment,
                'message' => 'Payment verified successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to verify payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payment',
            ], 500);
        }
    }

    public function reject(Request $request, Payment $payment)
    {
        try {
            $admin = auth('sanctum')->user();
            $payment->reject($admin);

            return response()->json([
                'success' => true,
                'data' => $payment,
                'message' => 'Payment rejected successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reject payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject payment',
            ], 500);
        }
    }
}
