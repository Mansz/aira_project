<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveComment extends Model
{
    protected $fillable = [
        'live_stream_id',
        'user_id',
        'content',
        'is_order',
        'order_code',
        'order_quantity'
    ];

    protected $casts = [
        'is_order' => 'boolean',
        'order_quantity' => 'integer',
    ];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function liveStream()
    {
        return $this->belongsTo(LiveStream::class);
    }

    // Helper method to parse order comments
    public static function parseOrderComment($content)
    {
        // Match pattern like "1023-2pcs" or "Batik05-M"
        if (preg_match('/^([A-Za-z0-9]+)-(\d+)(?:pcs)?$/i', $content, $matches)) {
            return [
                'is_order' => true,
                'order_code' => $matches[1],
                'order_quantity' => isset($matches[2]) ? (int)$matches[2] : 1
            ];
        }

        // Match pattern like "Batik05-M" (without quantity)
        if (preg_match('/^([A-Za-z0-9]+)-([A-Za-z]+)$/i', $content)) {
            return [
                'is_order' => true,
                'order_code' => $content,
                'order_quantity' => 1
            ];
        }

        return ['is_order' => false];
    }
}
