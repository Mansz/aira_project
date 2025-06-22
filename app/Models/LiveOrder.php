<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveOrder extends Model
{
    protected $fillable = [
        'order_id',
        'live_stream_id',
        'buyer_id',
        'total_amount',
        'voucher_id',
        'discount_amount',
        'order_details'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'order_details' => 'json'
    ];

    /**
     * Get the order associated with the live order.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the live stream associated with the order.
     */
    public function liveStream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class);
    }

    /**
     * Get the buyer associated with the order.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the voucher used in this order.
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(LiveVoucher::class, 'voucher_id');
    }

    /**
     * Get the final amount after discount.
     */
    public function getFinalAmount(): float
    {
        return $this->total_amount - $this->discount_amount;
    }

    /**
     * Scope a query to only include orders from a specific live stream.
     */
    public function scopeFromLiveStream($query, $liveStreamId)
    {
        return $query->where('live_stream_id', $liveStreamId);
    }

    /**
     * Scope a query to only include orders from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Get formatted order details for display.
     */
    public function getFormattedDetails(): array
    {
        return [
            'order_number' => $this->order->order_number,
            'buyer_name' => $this->buyer->name,
            'total_amount' => number_format($this->total_amount, 2),
            'discount_amount' => number_format($this->discount_amount, 2),
            'final_amount' => number_format($this->getFinalAmount(), 2),
            'items' => $this->order_details,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'voucher_code' => $this->voucher ? $this->voucher->code : null
        ];
    }
}