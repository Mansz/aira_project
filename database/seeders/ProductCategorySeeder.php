<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
                'is_active' => true,
            ],
            [
                'name' => 'Clothing',
                'description' => 'Fashion and apparel items',
                'is_active' => true,
            ],
            [
                'name' => 'Home & Garden',
                'description' => 'Home improvement and garden supplies',
                'is_active' => true,
            ],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'is_active' => true,
            ],
            [
                'name' => 'Books',
                'description' => 'Books and educational materials',
                'is_active' => true,
            ],
            [
                'name' => 'Health & Beauty',
                'description' => 'Health and beauty products',
                'is_active' => true,
            ],
            [
                'name' => 'Toys & Games',
                'description' => 'Toys and gaming products',
                'is_active' => true,
            ],
            [
                'name' => 'Automotive',
                'description' => 'Car parts and automotive accessories',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'is_active' => $category['is_active'],
            ]);
        }
    }
}
