<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'is_active',
        'last_login_at',
        'avatar'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    // Roles
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_STAFF = 'staff';

    public static function getRoles()
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_STAFF => 'Staff',
        ];
    }

    // Permissions
    const PERMISSIONS = [
        'manage_admins' => 'Kelola Admin',
        'manage_products' => 'Kelola Produk',
        'manage_orders' => 'Kelola Pesanan',
        'manage_payments' => 'Kelola Pembayaran',
        'manage_shipping' => 'Kelola Pengiriman',
        'manage_customers' => 'Kelola Pelanggan',
        'manage_streaming' => 'Kelola Live Streaming',
        'manage_whatsapp' => 'Kelola WhatsApp',
        'manage_settings' => 'Kelola Pengaturan',
        'view_analytics' => 'Lihat Analytics',
        'manage_content' => 'Kelola Konten',
    ];

    public function hasPermission($permission)
    {
        if ($this->role === self::ROLE_SUPER_ADMIN) {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function isSuperAdmin()
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isActive()
    {
        return $this->is_active;
    }

    // Relationships
    public function createdOrders()
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function updatedOrders()
    {
        return $this->hasMany(Order::class, 'updated_by');
    }

    public function activities()
    {
        return $this->hasMany(AdminActivity::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Mutators
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    // Accessors
    public function getRoleNameAttribute()
    {
        return self::getRoles()[$this->role] ?? $this->role;
    }

    public function getPermissionNamesAttribute()
    {
        if (!$this->permissions) {
            return [];
        }

        return array_map(function ($permission) {
            return self::PERMISSIONS[$permission] ?? $permission;
        }, $this->permissions);
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=000&color=fff';
    }
}
