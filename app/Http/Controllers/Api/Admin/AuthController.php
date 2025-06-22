<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login admin and create token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (!$admin->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan.'],
            ]);
        }

        $token = $admin->createToken('admin-token')->plainTextToken;

        // Update last login
        $admin->update(['last_login_at' => now()]);

        // Log activity
        AdminActivity::log(
            AdminActivity::ACTION_LOGIN,
            "Login berhasil: {$admin->name}",
            $admin
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'role' => $admin->role,
                    'permissions' => $admin->permissions,
                    'avatar_url' => $admin->avatar_url,
                ],
            ],
            'message' => 'Login berhasil'
        ]);
    }

    /**
     * Get authenticated admin profile
     */
    public function profile(Request $request)
    {
        $admin = $request->user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
                'permissions' => $admin->permissions,
                'avatar_url' => $admin->avatar_url,
                'last_login_at' => $admin->last_login_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Logout admin (revoke token)
     */
    public function logout(Request $request)
    {
        $admin = $request->user();
        
        // Log activity before revoking token
        AdminActivity::log(
            AdminActivity::ACTION_LOGOUT,
            "Logout: {$admin->name}",
            $admin
        );

        $admin->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Refresh admin token
     */
    public function refresh(Request $request)
    {
        $admin = $request->user();
        
        // Revoke current token
        $admin->currentAccessToken()->delete();
        
        // Create new token
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
            ],
            'message' => 'Token berhasil diperbarui'
        ]);
    }
}
