<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveAnalytics extends Model
{
    protected $fillable = [
        'live_stream_id',
        'total_comments',
        'active_users',
        'recorded_at',
    ];

    protected $casts = [
        'total_comments' => 'integer',
        'active_users' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function liveStream()
    {
        return $this->belongsTo(LiveStream::class);
    }
}
