<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveVoucher extends Model
{
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'description',
        'live_stream_id',
        'start_time',
        'end_time',
        'active'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'active' => 'boolean',
        'discount_value' => 'decimal:2'
    ];

    /**
     * Get the live stream that owns the voucher.
     */
    public function liveStream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class);
    }

    /**
     * Get the orders that used this voucher.
     */
    public function liveOrders(): HasMany
    {
        return $this->hasMany(LiveOrder::class, 'voucher_id');
    }

    /**
     * Check if the voucher is currently valid.
     */
    public function isValid(): bool
    {
        $now = now();
        return $this->active &&
            $now->greaterThanOrEqualTo($this->start_time) &&
            $now->lessThanOrEqualTo($this->end_time);
    }

    /**
     * Calculate discount amount for a given total.
     */
    public function calculateDiscount(float $total): float
    {
        if ($this->discount_type === 'percentage') {
            return ($total * $this->discount_value) / 100;
        }
        return min($this->discount_value, $total);
    }
}