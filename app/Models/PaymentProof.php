<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'file_path',
        'status',
        'verified_at',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }

    public function verify(Admin $admin)
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'notes' => 'Payment verified by admin',
        ]);

        // Update order status if needed
        if ($this->order) {
            $this->order->update(['status' => 'processing']);
        }
    }

    public function reject(Admin $admin, string $notes = '')
    {
        $this->update([
            'status' => 'rejected',
            'verified_at' => null,
            'verified_by' => null,
            'notes' => $notes ?: 'Payment proof rejected',
        ]);

        // Update order status if needed
        if ($this->order) {
            $this->order->update(['status' => 'pending']);
        }
    }

    // Accessor for backward compatibility
    public function getRejectedByAttribute()
    {
        return $this->status === 'rejected' ? $this->verifiedBy : null;
    }

    public function getRejectedAtAttribute()
    {
        return $this->status === 'rejected' ? $this->updated_at : null;
    }

    public function getRejectionNotesAttribute()
    {
        return $this->status === 'rejected' ? $this->notes : null;
    }
}
