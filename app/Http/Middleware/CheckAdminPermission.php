<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
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
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Check if admin has the required permission
        if ($permission && !$this->hasPermission($admin, $permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Anda tidak memiliki izin yang diperlukan.'
            ], 403);
        }

        return $next($request);
    }

    /**
     * Check if admin has the required permission
     */
    private function hasPermission($admin, string $permission): bool
    {
        // Super admin has all permissions
        if ($admin->role === 'super_admin') {
            return true;
        }

        // Check if admin has specific permission
        $permissions = is_array($admin->permissions) ? $admin->permissions : json_decode($admin->permissions ?? '[]', true);
        
        return in_array($permission, $permissions);
    }
}
