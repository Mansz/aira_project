<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories first
        $categories = [
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Fashion clothing items', 'is_active' => true],
            ['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Fashion accessories', 'is_active' => true],
            ['name' => 'Shoes', 'slug' => 'shoes', 'description' => 'Footwear collection', 'is_active' => true],
            ['name' => 'Bags', 'slug' => 'bags', 'description' => 'Bags and purses', 'is_active' => true],
        ];

        foreach ($categories as $categoryData) {
            ProductCategory::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }

        // Get category IDs
        $clothingCategory = ProductCategory::where('name', 'Clothing')->first();
        $accessoriesCategory = ProductCategory::where('name', 'Accessories')->first();
        $shoesCategory = ProductCategory::where('name', 'Shoes')->first();
        $bagsCategory = ProductCategory::where('name', 'Bags')->first();

        // Create sample products
        $products = [
            [
                'name' => 'Elegant Dress',
                'slug' => 'elegant-dress',
                'description' => 'Beautiful elegant dress perfect for special occasions',
                'price' => 299000,
                'stock' => 25,
                'category_id' => $clothingCategory->id,
                'status' => 'active',
                'sku' => 'ELD-001',
            ],
            [
                'name' => 'Casual T-Shirt',
                'slug' => 'casual-t-shirt',
                'description' => 'Comfortable cotton t-shirt for everyday wear',
                'price' => 89000,
                'stock' => 50,
                'category_id' => $clothingCategory->id,
                'status' => 'active',
                'sku' => 'CTS-001',
            ],
            [
                'name' => 'Designer Handbag',
                'slug' => 'designer-handbag',
                'description' => 'Luxury designer handbag made from premium leather',
                'price' => 599000,
                'stock' => 15,
                'category_id' => $bagsCategory->id,
                'status' => 'active',
                'sku' => 'DHB-001',
            ],
            [
                'name' => 'Gold Necklace',
                'slug' => 'gold-necklace',
                'description' => 'Elegant gold necklace with beautiful pendant',
                'price' => 1299000,
                'stock' => 8,
                'category_id' => $accessoriesCategory->id,
                'status' => 'active',
                'sku' => 'GN-001',
            ],
            [
                'name' => 'Running Shoes',
                'slug' => 'running-shoes',
                'description' => 'Comfortable running shoes for sports and exercise',
                'price' => 399000,
                'stock' => 30,
                'category_id' => $shoesCategory->id,
                'status' => 'active',
                'sku' => 'RS-001',
            ],
            [
                'name' => 'Leather Jacket',
                'slug' => 'leather-jacket',
                'description' => 'Stylish leather jacket for a trendy look',
                'price' => 799000,
                'stock' => 12,
                'category_id' => $clothingCategory->id,
                'status' => 'active',
                'sku' => 'LJ-001',
            ],
        ];

        foreach ($products as $productData) {
            Product::firstOrCreate(
                ['sku' => $productData['sku']],
                $productData
            );
        }
    }
}
