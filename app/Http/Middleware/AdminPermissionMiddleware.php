<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $admin = $request->user();

        if (!$admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        // Check if user is an admin (from admins table)
        if (!$admin instanceof \App\Models\Admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied. Admin privileges required.'
            ], 403);
        }

        // Check if admin is active
        if (!$admin->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account is deactivated.'
            ], 403);
        }

        // If specific permission is required, check it
        if ($permission && !$admin->hasPermission($permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient permissions.'
            ], 403);
        }

        return $next($request);
    }
}
