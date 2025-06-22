<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'amount',
        'payment_method',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'card_type',
        'card_last4',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
        ]);

        // Update order status if needed
        if ($this->order) {
            $this->order->update(['status' => 'processing']);
        }
    }

    public function reject(Admin $admin)
    {
        $this->update([
            'status' => 'rejected',
            'verified_at' => null,
            'verified_by' => null,
        ]);

        // Update order status if needed
        if ($this->order) {
            $this->order->update(['status' => 'pending']);
        }
    }
}
