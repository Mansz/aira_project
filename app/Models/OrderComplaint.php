<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrderComplaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'description',
        'photo_path',
        'status',
        'admin_notes',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    protected $appends = [
        'photo_url',
        'status_label',
        'status_color',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function resolvedBy()
    {
        return $this->belongsTo(Admin::class, 'resolved_by');
    }

    // Accessors
    public function getPhotoUrlAttribute()
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'Pending' => 'Menunggu Review',
            'Processing' => 'Sedang Diproses',
            'Resolved' => 'Selesai',
            'Rejected' => 'Ditolak',
            default => $this->status
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'Pending' => '#f59e0b',
            'Processing' => '#3b82f6',
            'Resolved' => '#10b981',
            'Rejected' => '#ef4444',
            default => '#6b7280'
        };
    }

    // Methods
    public function resolve($admin, $notes = null)
    {
        $this->update([
            'status' => 'Resolved',
            'resolved_at' => now(),
            'resolved_by' => $admin->id,
            'admin_notes' => $notes,
        ]);

        // Send notification to user
        if ($this->order->fcm_token) {
            app(FCMService::class)->sendNotification(
                $this->order->fcm_token,
                "✅",
                "Komplain untuk pesanan #{$this->order->id} telah diselesaikan",
                [
                    'order_id' => $this->order->id,
                    'complaint_id' => $this->id,
                ]
            );
        }
    }

    public function reject($admin, $notes)
    {
        $this->update([
            'status' => 'Rejected',
            'resolved_at' => now(),
            'resolved_by' => $admin->id,
            'admin_notes' => $notes,
        ]);

        // Send notification to user
        if ($this->order->fcm_token) {
            app(FCMService::class)->sendNotification(
                $this->order->fcm_token,
                "❌",
                "Komplain untuk pesanan #{$this->order->id} ditolak: {$notes}",
                [
                    'order_id' => $this->order->id,
                    'complaint_id' => $this->id,
                ]
            );
        }
    }

    protected static function booted()
    {
        static::deleting(function ($complaint) {
            // Delete complaint photo
            if ($complaint->photo_path) {
                Storage::disk('public')->delete($complaint->photo_path);
            }
        });

        static::created(function ($complaint) {
            // Send notification to admin
            $admins = Admin::all();
            foreach ($admins as $admin) {
                if ($admin->fcm_token) {
                    app(FCMService::class)->sendNotification(
                        $admin->fcm_token,
                        "⚠️",
                        "Ada komplain baru untuk pesanan #{$complaint->order->id}",
                        [
                            'order_id' => $complaint->order->id,
                            'complaint_id' => $complaint->id,
                        ]
                    );
                }
            }
        });
    }
}
