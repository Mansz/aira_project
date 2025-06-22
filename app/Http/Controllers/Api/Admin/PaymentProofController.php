<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentProofController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = PaymentProof::with(['order.user', 'verifiedBy']);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $paymentProofs = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $paymentProofs,
                'message' => 'Payment proofs retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve payment proofs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment proofs',
            ], 500);
        }
    }

    public function show(PaymentProof $paymentProof)
    {
        try {
            $paymentProof->load(['order.user', 'verifiedBy']);

            return response()->json([
                'success' => true,
                'data' => $paymentProof,
                'message' => 'Payment proof retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve payment proof: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment proof',
            ], 500);
        }
    }

    public function verify(Request $request, PaymentProof $paymentProof)
    {
        try {
            $admin = auth('sanctum')->user();
            $paymentProof->verify($admin);

            return response()->json([
                'success' => true,
                'data' => $paymentProof,
                'message' => 'Payment proof verified successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to verify payment proof: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payment proof',
            ], 500);
        }
    }

    public function reject(Request $request, PaymentProof $paymentProof)
    {
        try {
            $admin = auth('sanctum')->user();
            $notes = $request->input('notes', '');
            $paymentProof->reject($admin, $notes);

            return response()->json([
                'success' => true,
                'data' => $paymentProof,
                'message' => 'Payment proof rejected successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reject payment proof: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject payment proof',
            ], 500);
        }
    }
}
