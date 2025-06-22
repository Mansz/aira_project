<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => [
                'value' => $this->price,
                'formatted' => '$' . number_format($this->price, 2)
            ],
            'stock' => $this->stock,
            'details' => [
                'weight' => $this->weight,
                'color' => $this->color,
                'size' => $this->size
            ],
            'codes' => [
                'product_code' => $this->product_code,
                'sku' => $this->sku
            ],
            'image_url' => $this->image_path,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString()
        ];
    }
}
