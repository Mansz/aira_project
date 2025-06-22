<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PaymentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_type',
        'name',
        'account_number',
        'account_name',
        'description',
        'is_active',
        'logo_path',
        'instructions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'instructions' => 'array',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'payment_method_id');
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function scopeByType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($paymentSetting) {
            // Delete logo file if exists
            if ($paymentSetting->logo_path) {
                Storage::disk('public')->delete($paymentSetting->logo_path);
            }
        });
    }
}
