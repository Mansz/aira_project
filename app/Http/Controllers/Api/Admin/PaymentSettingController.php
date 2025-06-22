<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentSettingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = PaymentSetting::query();

            if ($request->has('type')) {
                $query->byType($request->type);
            }

            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            }

            $settings = $query->orderBy('payment_type')->get();

            return response()->json([
                'success' => true,
                'data' => $settings,
                'message' => 'Payment settings retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve payment settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment settings'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'payment_type' => 'required|string',
                'name' => 'required|string|max:255',
                'account_number' => 'required|string|max:255',
                'account_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'logo' => 'nullable|image|max:2048',
                'instructions' => 'nullable|string',
            ]);

            // Handle instructions JSON string
            if (isset($validated['instructions'])) {
                $validated['instructions'] = json_decode($validated['instructions'], true);
            }

            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('payment-logos', 'public');
                $validated['logo_path'] = $path;
            }

            $setting = PaymentSetting::create($validated);

            return response()->json([
                'success' => true,
                'data' => $setting,
                'message' => 'Payment setting created successfully'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create payment setting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment setting'
            ], 500);
        }
    }

    public function show(PaymentSetting $paymentSetting)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $paymentSetting,
                'message' => 'Payment setting retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve payment setting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment setting'
            ], 500);
        }
    }

    public function update(Request $request, PaymentSetting $paymentSetting)
    {
        try {
            $validated = $request->validate([
                'payment_type' => 'string',
                'name' => 'string|max:255',
                'account_number' => 'string|max:255',
                'account_name' => 'string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'logo' => 'nullable|image|max:2048',
                'instructions' => 'nullable|string',
            ]);

            // Handle instructions JSON string
            if (isset($validated['instructions'])) {
                $validated['instructions'] = json_decode($validated['instructions'], true);
            }

            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($paymentSetting->logo_path) {
                    Storage::disk('public')->delete($paymentSetting->logo_path);
                }
                $path = $request->file('logo')->store('payment-logos', 'public');
                $validated['logo_path'] = $path;
            }

            $paymentSetting->update($validated);

            return response()->json([
                'success' => true,
                'data' => $paymentSetting,
                'message' => 'Payment setting updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update payment setting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment setting'
            ], 500);
        }
    }

    public function destroy(PaymentSetting $paymentSetting)
    {
        try {
            // Check if payment setting is in use
            if ($paymentSetting->orders()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete payment setting that is in use'
                ], 400);
            }

            // Delete logo if exists
            if ($paymentSetting->logo_path) {
                Storage::disk('public')->delete($paymentSetting->logo_path);
            }

            $paymentSetting->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment setting deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete payment setting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment setting'
            ], 500);
        }
    }

    public function toggleStatus(PaymentSetting $paymentSetting)
    {
        try {
            $paymentSetting->update([
                'is_active' => !$paymentSetting->is_active
            ]);

            return response()->json([
                'success' => true,
                'data' => $paymentSetting,
                'message' => 'Payment setting status updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to toggle payment setting status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle payment setting status'
            ], 500);
        }
    }
}
