<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Get all settings grouped by their group
     */
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return response()->json([
            'status' => 'success',
            'data' => $settings
        ]);
    }

    /**
     * Get settings by group
     */
    public function getByGroup($group)
    {
        $settings = Setting::group($group)->get();
        return response()->json([
            'status' => 'success',
            'data' => $settings
        ]);
    }

    /**
     * Update multiple settings
     */
    public function batchUpdate(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:settings,key',
            'settings.*.value' => 'required'
        ]);

        foreach ($request->settings as $setting) {
            Setting::setValue($setting['key'], $setting['value']);
        }

        // Clear settings cache
        Cache::tags(['settings'])->flush();

        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully'
        ]);
    }

    /**
     * Create a new setting
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|unique:settings,key',
            'value' => 'required',
            'type' => ['required', Rule::in(['string', 'number', 'boolean', 'array', 'object'])],
            'group' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $setting = Setting::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $setting,
            'message' => 'Setting created successfully'
        ], 201);
    }

    /**
     * Update a specific setting
     */
    public function update(Request $request, $key)
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        $request->validate([
            'value' => 'required',
            'type' => ['sometimes', Rule::in(['string', 'number', 'boolean', 'array', 'object'])],
            'group' => 'sometimes|string',
            'description' => 'nullable|string'
        ]);

        $setting->update($request->all());

        // Clear settings cache
        Cache::tags(['settings'])->flush();

        return response()->json([
            'status' => 'success',
            'data' => $setting,
            'message' => 'Setting updated successfully'
        ]);
    }

    /**
     * Delete a setting
     */
    public function destroy($key)
    {
        $setting = Setting::where('key', $key)->firstOrFail();
        $setting->delete();

        // Clear settings cache
        Cache::tags(['settings'])->flush();

        return response()->json([
            'status' => 'success',
            'message' => 'Setting deleted successfully'
        ]);
    }
}
