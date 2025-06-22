<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    protected $table = 'whatsapp_messages';
    
    protected $fillable = [
        'message_id',
        'phone_number',
        'message',
        'status',
        'direction',
        'user_id',
        'order_id',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $attributes = [
        'status' => 'pending',
        'direction' => 'outbound',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
