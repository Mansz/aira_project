<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use App\Services\FCMService;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shipping_address_id',
        'total_price',
        'shipping_address',
        'payment_method',
        'status',
        'shipping_status',
        'courier_name',
        'courier_service',
        'tracking_number',
        'shipping_cost',
        'estimated_delivery_date',
        'delivered_at',
        'shipping_proof_path',
        'fcm_token',
        'total_amount',
    ];

    protected $dates = [
        'estimated_delivery_date',
        'delivered_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    protected $appends = [
        'status_icon',
        'status_color',
        'formatted_total',
        'shipping_proof_url',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function paymentProof()
    {
        return $this->hasOne(PaymentProof::class);
    }

    public function complaints()
    {
        return $this->hasMany(OrderComplaint::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(ShippingAddress::class);
    }

    // Status List
    public static function getStatusList()
    {
        return [
            'Menunggu Pembayaran' => 'Menunggu Pembayaran',
            'Menunggu Konfirmasi' => 'Menunggu Konfirmasi',
            'Diproses' => 'Diproses',
            'Dikirim' => 'Dikirim',
            'Selesai' => 'Selesai',
            'Dibatalkan' => 'Dibatalkan',
        ];
    }

    // Accessors
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'Menunggu Pembayaran' =>'🕒',
            'Menunggu Konfirmasi' => '🕒',
            'Diproses' => '🔄',
            'Dikirim' => '🚚',
            'Selesai' => '✅',
            'Dibatalkan' => '❌',
            default => '📦'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'Menunggu Pembayaran', 'Menunggu Konfirmasi' => '#f59e0b',
            'Diproses' => '#3b82f6',
            'Dikirim' => '#4f46e5',
            'Selesai' => '#10b981',
            'Dibatalkan' => '#ef4444',
            default => '#6b7280'
        };
    }

    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    public function getShippingProofUrlAttribute()
    {
        if (!$this->shipping_proof_path) {
            return null;
        }

        return Storage::url($this->shipping_proof_path);
    }

    // Shipping status constants
    const SHIPPING_STATUS_PROCESSING = 'processing';
    const SHIPPING_STATUS_IN_TRANSIT = 'in_transit';
    const SHIPPING_STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const SHIPPING_STATUS_DELIVERED = 'delivered';

    public static function getShippingStatusList()
    {
        return [
            self::SHIPPING_STATUS_PROCESSING => 'Processing',
            self::SHIPPING_STATUS_IN_TRANSIT => 'In Transit',
            self::SHIPPING_STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
            self::SHIPPING_STATUS_DELIVERED => 'Delivered',
        ];
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['Menunggu Pembayaran', 'Menunggu Konfirmasi']);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'Diproses');
    }

    public function scopeShipping($query)
    {
        return $query->whereIn('shipping_status', [
            self::SHIPPING_STATUS_IN_TRANSIT,
            self::SHIPPING_STATUS_OUT_FOR_DELIVERY
        ]);
    }

    public function scopeDelivered($query)
    {
        return $query->where('shipping_status', self::SHIPPING_STATUS_DELIVERED);
    }

    public function scopeProcessingShipment($query)
    {
        return $query->where('shipping_status', self::SHIPPING_STATUS_PROCESSING);
    }

    public function scopeInTransit($query)
    {
        return $query->where('shipping_status', self::SHIPPING_STATUS_IN_TRANSIT);
    }

    public function scopeOutForDelivery($query)
    {
        return $query->where('shipping_status', self::SHIPPING_STATUS_OUT_FOR_DELIVERY);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Selesai');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'Dibatalkan');
    }

    // Methods
    public function canBeCancelled()
    {
        return in_array($this->status, ['Menunggu Pembayaran', 'Menunggu Konfirmasi']);
    }

    public function canBeProcessed()
    {
        return $this->status === 'Menunggu Konfirmasi' && 
               $this->paymentProof && 
               $this->paymentProof->is_verified;
    }

    public function canBeShipped()
    {
        return $this->status === 'Diproses' && 
               $this->shipping_status === self::SHIPPING_STATUS_PROCESSING;
    }

    public function updateShippingStatus($status)
    {
        if (!array_key_exists($status, self::getShippingStatusList())) {
            throw new \InvalidArgumentException('Invalid shipping status');
        }

        $this->update(['shipping_status' => $status]);

        // If delivered, update delivered_at timestamp
        if ($status === self::SHIPPING_STATUS_DELIVERED) {
            $this->update(['delivered_at' => now()]);
        }

        // Send notification to customer
        if ($this->fcm_token) {
            $message = match($status) {
                self::SHIPPING_STATUS_IN_TRANSIT => "Pesanan #{$this->id} sedang dalam perjalanan",
                self::SHIPPING_STATUS_OUT_FOR_DELIVERY => "Pesanan #{$this->id} sedang menuju lokasi Anda",
                self::SHIPPING_STATUS_DELIVERED => "Pesanan #{$this->id} telah sampai di tujuan",
                default => "Status pengiriman pesanan #{$this->id} telah diperbarui"
            };

            app(FCMService::class)->sendNotification(
                $this->fcm_token,
                "🚚",
                $message,
                ['order_id' => $this->id]
            );
        }
    }

    public function updateShippingDetails(array $details)
    {
        $this->update([
            'courier_name' => $details['courier_name'] ?? $this->courier_name,
            'courier_service' => $details['courier_service'] ?? $this->courier_service,
            'tracking_number' => $details['tracking_number'] ?? $this->tracking_number,
            'shipping_cost' => $details['shipping_cost'] ?? $this->shipping_cost,
            'estimated_delivery_date' => $details['estimated_delivery_date'] ?? $this->estimated_delivery_date,
        ]);
    }

    public function canBeCompleted()
    {
        return $this->status === 'Dikirim' && !$this->complaints()->where('status', 'Pending')->exists();
    }

    public function complete()
    {
        if (!$this->canBeCompleted()) {
            throw new \Exception('Order cannot be completed');
        }

        $this->update(['status' => 'Selesai']);

        // Send notification
        if ($this->fcm_token) {
            app(FCMService::class)->sendNotification(
                $this->fcm_token,
                "✅",
                "Pesanan #{$this->id} telah selesai. Terima kasih telah berbelanja!",
                ['order_id' => $this->id]
            );
        }
    }

    public function updateStatus($status)
    {
        if (!array_key_exists($status, self::getStatusList())) {
            throw new \InvalidArgumentException('Invalid status');
        }

        $this->update(['status' => $status]);
    }

    public function calculateTotal()
    {
        return $this->orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    protected static function booted()
    {
        static::deleting(function ($order) {
            // Delete related files
            if ($order->shipping_proof_path) {
                Storage::disk('public')->delete($order->shipping_proof_path);
            }

            // Delete related models
            $order->orderItems()->delete();
            if ($order->paymentProof) {
                Storage::disk('public')->delete($order->paymentProof->path);
                $order->paymentProof->delete();
            }
        });
    }
}
