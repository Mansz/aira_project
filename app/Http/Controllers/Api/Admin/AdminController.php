<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Get all admins with pagination
     */
    public function index(Request $request)
    {
        $query = Admin::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $admins = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $admins
        ]);
    }

    /**
     * Get admin by ID
     */
    public function show(Admin $admin)
    {
        $admin->load('activities');
        
        return response()->json([
            'status' => 'success',
            'data' => $admin
        ]);
    }

    /**
     * Create new admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(array_keys(Admin::getRoles()))],
            'permissions' => 'array',
            'permissions.*' => Rule::in(array_keys(Admin::PERMISSIONS)),
            'is_active' => 'boolean'
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->get('is_active', true),
            'email_verified_at' => now(),
        ]);

        // Log activity
        AdminActivity::log(
            AdminActivity::ACTION_CREATE,
            "Membuat admin baru: {$admin->name}",
            $admin
        );

        return response()->json([
            'status' => 'success',
            'data' => $admin,
            'message' => 'Admin berhasil dibuat'
        ], 201);
    }

    /**
     * Update admin
     */
    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('admins')->ignore($admin->id)],
            'password' => 'sometimes|nullable|string|min:8|confirmed',
            'role' => ['sometimes', 'required', Rule::in(array_keys(Admin::getRoles()))],
            'permissions' => 'sometimes|array',
            'permissions.*' => Rule::in(array_keys(Admin::PERMISSIONS)),
            'is_active' => 'sometimes|boolean'
        ]);

        $oldValues = $admin->toArray();

        $updateData = $request->only(['name', 'email', 'role', 'permissions', 'is_active']);
        
        if ($request->filled('password')) {
            $updateData['password'] = $request->password;
        }

        $admin->update($updateData);

        // Log activity
        AdminActivity::log(
            AdminActivity::ACTION_UPDATE,
            "Mengubah data admin: {$admin->name}",
            $admin,
            $oldValues,
            $admin->fresh()->toArray()
        );

        return response()->json([
            'status' => 'success',
            'data' => $admin->fresh(),
            'message' => 'Admin berhasil diperbarui'
        ]);
    }

    /**
     * Delete admin
     */
    public function destroy(Admin $admin)
    {
        // Prevent deleting super admin
        if ($admin->role === Admin::ROLE_SUPER_ADMIN) {
            return response()->json([
                'status' => 'error',
                'message' => 'Super Admin tidak dapat dihapus'
            ], 403);
        }

        // Prevent self deletion
        if ($admin->id === auth()->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak dapat menghapus akun sendiri'
            ], 403);
        }

        $adminName = $admin->name;
        $admin->delete();

        // Log activity
        AdminActivity::log(
            AdminActivity::ACTION_DELETE,
            "Menghapus admin: {$adminName}"
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Admin berhasil dihapus'
        ]);
    }

    /**
     * Toggle admin status
     */
    public function toggleStatus(Admin $admin)
    {
        // Prevent deactivating super admin
        if ($admin->role === Admin::ROLE_SUPER_ADMIN && $admin->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Super Admin tidak dapat dinonaktifkan'
            ], 403);
        }

        // Prevent self deactivation
        if ($admin->id === auth()->user()->id && $admin->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak dapat menonaktifkan akun sendiri'
            ], 403);
        }

        $admin->update(['is_active' => !$admin->is_active]);

        $status = $admin->is_active ? 'mengaktifkan' : 'menonaktifkan';
        
        // Log activity
        AdminActivity::log(
            AdminActivity::ACTION_UPDATE,
            "Status admin {$admin->name}: {$status}",
            $admin
        );

        return response()->json([
            'status' => 'success',
            'data' => $admin,
            'message' => "Admin berhasil " . ($admin->is_active ? 'diaktifkan' : 'dinonaktifkan')
        ]);
    }

    /**
     * Get available permissions
     */
    public function permissions()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'permissions' => Admin::PERMISSIONS,
                'roles' => Admin::getRoles()
            ]
        ]);
    }

    /**
     * Get admin activities
     */
    public function activities(Request $request, Admin $admin)
    {
        $activities = $admin->activities()
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $activities
        ]);
    }
}
