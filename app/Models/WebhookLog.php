<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'source',
        'event_type',
        'payload',
        'headers',
        'ip_address',
        'status',
        'response',
        'processed_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
        'response' => 'array',
        'processed_at' => 'datetime'
    ];

    /**
     * Scope a query to only include successful webhooks.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope a query to only include failed webhooks.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if the webhook was successful
     */
    public function isSuccessful()
    {
        return $this->status === 'success';
    }
}