<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'whatsapp',
        'google_id',
        'avatar',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user's orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the user's cart items.
     */
    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get the user's shipping addresses.
     */
    public function shippingAddresses()
    {
        return $this->hasMany(ShippingAddress::class);
    }

    /**
     * Get the user's live streams.
     */
    public function liveStreams()
    {
        return $this->hasMany(LiveStream::class);
    }

    /**
     * Get the user's live comments.
     */
    public function liveComments()
    {
        return $this->hasMany(LiveComment::class);
    }
}
