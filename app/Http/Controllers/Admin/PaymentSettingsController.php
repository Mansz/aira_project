<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaymentSettingsController extends Controller
{
    public function index()
    {
        try {
            $bankAccounts = PaymentSetting::where('payment_type', 'bank')->get();
            $eWallets = PaymentSetting::where('payment_type', 'e-wallet')->get();

            return view('admin.payment-settings.index', compact('bankAccounts', 'eWallets'));
        } catch (\Exception $e) {
            Log::error('Failed to load payment settings: ' . $e->getMessage());
            return back()->with('error', 'Failed to load payment settings.');
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_type' => 'required|in:bank,e-wallet',
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'logo_path' => 'nullable|image|max:2048',
            'instructions' => 'nullable|array',
        ]);

        try {
            if ($request->hasFile('logo_path')) {
                $path = $request->file('logo_path')->store('payment-logos', 'public');
                $validated['logo_path'] = $path;
            }

            // Convert instructions array to JSON if present
            if (isset($validated['instructions'])) {
                $validated['instructions'] = json_encode($validated['instructions']);
            }

            PaymentSetting::create($validated);

            return redirect()->route('admin.settings.payment')
                ->with('success', 'Payment method added successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create payment setting: ' . $e->getMessage());
            return back()->with('error', 'Failed to add payment method.')
                ->withInput();
        }
    }

    public function update(Request $request, PaymentSetting $setting)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'logo_path' => 'nullable|image|max:2048',
            'instructions' => 'nullable|array',
        ]);

        try {
            if ($request->hasFile('logo_path')) {
                // Delete old logo if exists
                if ($setting->logo_path) {
                    Storage::disk('public')->delete($setting->logo_path);
                }
                
                $path = $request->file('logo_path')->store('payment-logos', 'public');
                $validated['logo_path'] = $path;
            }

            // Convert instructions array to JSON if present
            if (isset($validated['instructions'])) {
                $validated['instructions'] = json_encode($validated['instructions']);
            }

            $setting->update($validated);

            return redirect()->route('admin.settings.payment')
                ->with('success', 'Payment method updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update payment setting: ' . $e->getMessage());
            return back()->with('error', 'Failed to update payment method.')
                ->withInput();
        }
    }

    public function destroy(PaymentSetting $setting)
    {
        try {
            // Delete logo file if exists
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $setting->delete();

            return redirect()->route('admin.settings.payment')
                ->with('success', 'Payment method deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete payment setting: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete payment method.');
        }
    }

    public function toggleStatus(PaymentSetting $setting)
    {
        try {
            $setting->update(['is_active' => !$setting->is_active]);

            return redirect()->route('admin.settings.payment')
                ->with('success', 'Payment method status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to toggle payment setting status: ' . $e->getMessage());
            return back()->with('error', 'Failed to update payment method status.');
        }
    }
}