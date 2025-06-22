<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'stream_id',
        'stream_token',
        'user_id',
        'viewer_count',
        'pinned_product_id',
        'start_time',
        'end_time',
    ];

    protected $dates = [
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'viewer_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'live_stream_product');
    }

    public function comments()
    {
        return $this->hasMany(LiveComment::class);
    }

    public function pinnedProduct()
    {
        return $this->belongsTo(Product::class, 'pinned_product_id');
    }

    public function vouchers()
    {
        return $this->hasMany(LiveVoucher::class);
    }

    public function orders()
    {
        return $this->hasMany(LiveOrder::class);
    }

    public function analytics()
    {
        return $this->hasMany(LiveAnalytics::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getDurationAttribute()
    {
        if (!$this->start_time) {
            return 0;
        }

        $endTime = $this->end_time ?? now();
        return $this->start_time->diffInMinutes($endTime);
    }
}
