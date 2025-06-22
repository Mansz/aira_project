<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'weight',
        'color',
        'size',
        'image',
        'sku',
        'category_id',
        'status',
        'metadata'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
        'stock' => 'integer',
        'metadata' => 'array',
        'status' => 'string'
    ];

    protected $attributes = [
        'status' => 'active',
        'stock' => 0,
        'metadata' => '{}',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (!$product->sku) {
                $product->sku = 'PRD-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        return $this->stock > 0 && $this->status === 'active';
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute(): string
    {
        if (!$this->image) {  // Ganti image_path dengan image
            return asset('images/no-product-image.jpg');
        }
        return Storage::url($this->image); // Ganti image_path dengan image
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include products in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0)->where('status', 'active');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}