<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle user login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string|max:255',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password tidak valid'
                ], 401);
            }

            // Revoke all existing tokens for this device
            $user->tokens()->where('name', $request->device_name)->delete();

            $token = $user->createToken($request->device_name)->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'token' => $token,
                    'user' => $user->only(['id', 'name', 'email', 'whatsapp'])
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Login failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Login gagal. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Handle Google Sign In
     */
    public function googleSignIn(Request $request)
    {
        $request->validate([
            'google_token' => 'required',
            'device_name' => 'required',
        ]);

        // Verify Google token and get user info
        // Implementation depends on Google Sign In SDK

        // Create or update user
        $user = User::updateOrCreate(
            ['email' => $request->email],
            [
                'name' => $request->name,
                'google_id' => $request->google_id,
                'email_verified_at' => now(),
            ]
        );

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $user,
                'requires_whatsapp' => empty($user->whatsapp)
            ]
        ]);
    }

    /**
     * Send OTP
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'whatsapp' => 'required|string|regex:/^[0-9]{10,15}$/',
        ]);

        // Generate OTP
        $otp = rand(100000, 999999);
        
        // Store OTP in cache for 5 minutes
        Cache::put('otp_' . $request->whatsapp, $otp, now()->addMinutes(5));

        // Integrate with WhatsApp API to send OTP
        try {
            $whatsappService = app(\App\Services\WhatsAppService::class);
            $message = "Kode OTP Anda: {$otp}. Berlaku selama 5 menit. Jangan bagikan kode ini kepada siapapun.";
            
            $whatsappService->sendMessage($request->whatsapp, $message);
            
            return response()->json([
                'success' => true,
                'message' => 'OTP berhasil dikirim ke WhatsApp Anda'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP via WhatsApp', [
                'whatsapp' => $request->whatsapp,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim OTP. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'whatsapp' => 'required|string',
            'otp' => 'required|string',
        ]);

        $storedOtp = Cache::get('otp_' . $request->whatsapp);

        if (!$storedOtp || $storedOtp != $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 400);
        }

        // Clear OTP from cache
        Cache::forget('otp_' . $request->whatsapp);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully'
        ]);
    }

    /**
     * Handle user registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'whatsapp' => 'required|string|regex:/^[0-9]{10,15}$/|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/'
            ],
            'device_name' => 'required|string|max:255',
        ], [
            'password.regex' => 'Password harus mengandung minimal 1 huruf kecil, 1 huruf besar, 1 angka, dan 1 karakter khusus.',
            'whatsapp.regex' => 'Format nomor WhatsApp tidak valid.',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'whatsapp' => $request->whatsapp,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(), // Auto verify for now
            ]);

            $token = $user->createToken($request->device_name)->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => [
                    'token' => $token,
                    'user' => $user->only(['id', 'name', 'email', 'whatsapp'])
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Registrasi gagal. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Handle user logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user()
            ]
        ]);
    }

    /**
     * Get user profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user()
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'whatsapp' => 'sometimes|string|unique:users,whatsapp,' . $user->id,
        ]);

        $user->update($request->only(['name', 'email', 'whatsapp']));

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->fresh()
            ]
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Revoke current token
        $user->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('refreshed-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ]);
    }
}
