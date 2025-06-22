<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    protected $appends = [
        'subtotal',
        'formatted_price',
        'formatted_subtotal',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    // Methods
    public function updateQuantity($quantity)
    {
        if ($quantity < 1) {
            throw new \InvalidArgumentException('Quantity must be at least 1');
        }

        // Check product stock
        if ($this->product->stock < $quantity) {
            throw new \InvalidArgumentException('Insufficient stock');
        }

        // Calculate stock difference
        $stockDiff = $quantity - $this->quantity;

        // Update product stock
        $this->product->decrement('stock', $stockDiff);

        // Update quantity
        $this->update(['quantity' => $quantity]);

        // Update order total
        $this->order->update([
            'total_price' => $this->order->calculateTotal()
        ]);
    }

    protected static function booted()
    {
        static::creating(function ($orderItem) {
            // Set price from product if not set
            if (!$orderItem->price) {
                $orderItem->price = $orderItem->product->price;
            }
        });

        static::created(function ($orderItem) {
            // Update order total
            $orderItem->order->update([
                'total_price' => $orderItem->order->calculateTotal()
            ]);
        });

        static::deleted(function ($orderItem) {
            // Restore product stock
            $orderItem->product->increment('stock', $orderItem->quantity);

            // Update order total
            $orderItem->order->update([
                'total_price' => $orderItem->order->calculateTotal()
            ]);
        });
    }
}