<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'action',
        'description',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Activity types
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_VIEW = 'view';
    const ACTION_EXPORT = 'export';

    public static function getActions()
    {
        return [
            self::ACTION_CREATE => 'Membuat',
            self::ACTION_UPDATE => 'Mengubah',
            self::ACTION_DELETE => 'Menghapus',
            self::ACTION_LOGIN => 'Login',
            self::ACTION_LOGOUT => 'Logout',
            self::ACTION_VIEW => 'Melihat',
            self::ACTION_EXPORT => 'Export',
        ];
    }

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function model()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByModel($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getActionNameAttribute()
    {
        return self::getActions()[$this->action] ?? $this->action;
    }

    public function getModelNameAttribute()
    {
        if (!$this->model_type) {
            return null;
        }

        $modelClass = class_basename($this->model_type);
        
        $modelNames = [
            'Product' => 'Produk',
            'Order' => 'Pesanan',
            'Payment' => 'Pembayaran',
            'Admin' => 'Admin',
            'Setting' => 'Pengaturan',
            'LiveStream' => 'Live Stream',
            'WhatsAppMessage' => 'Pesan WhatsApp',
        ];

        return $modelNames[$modelClass] ?? $modelClass;
    }

    // Static methods
    public static function log($action, $description, $model = null, $oldValues = null, $newValues = null)
    {
        $admin = auth('sanctum')->user();
        
        if (!$admin) {
            return;
        }

        return self::create([
            'admin_id' => $admin->id,
            'action' => $action,
            'description' => $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
