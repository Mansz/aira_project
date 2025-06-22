<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'order_id',
        'customer_name',
        'customer_phone',
        'address_street',
        'address_city',
        'address_province',
        'address_postal_code',
        'courier_name',
        'courier_service',
        'courier_tracking_number',
        'status',
        'weight',
    ];

    protected $attributes = [
        'status' => 'processing'
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function toArray()
    {
        $array = parent::toArray();
        
        // Format data sesuai dengan yang diharapkan frontend
        return [
            'id' => $this->order_number,
            'orderId' => $this->order_id,
            'customer' => [
                'name' => $this->customer_name,
                'phone' => $this->customer_phone,
            ],
            'address' => [
                'street' => $this->address_street,
                'city' => $this->address_city,
                'province' => $this->address_province,
                'postalCode' => $this->address_postal_code,
            ],
            'courier' => [
                'name' => $this->courier_name,
                'service' => $this->courier_service,
                'trackingNumber' => $this->courier_tracking_number,
            ],
            'status' => $this->status,
            'weight' => $this->weight,
            'items' => $this->items->map(function ($item) {
                return [
                    'name' => $item->item_name,
                    'quantity' => $item->quantity,
                ];
            }),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
