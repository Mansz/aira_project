<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test user if none exists
        $user = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Test Customer',
                'password' => bcrypt('password123'),
                'phone' => '081234567890',
            ]
        );

        // Create some test products if none exist
        if (Product::count() === 0) {
            $products = [
                [
                    'name' => 'Product 1',
                    'slug' => 'product-1',
                    'price' => 100000,
                    'stock' => 100,
                    'description' => 'Test product 1',
                ],
                [
                    'name' => 'Product 2',
                    'slug' => 'product-2',
                    'price' => 200000,
                    'stock' => 100,
                    'description' => 'Test product 2',
                ],
            ];

            foreach ($products as $product) {
                Product::create($product);
            }
        }

        // Create test orders
        $products = Product::all();
        
        for ($i = 1; $i <= 5; $i++) {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'total_price' => 0,
                'total_amount' => 0,
                'shipping_address' => 'Jl. Test No. ' . $i . ', Jakarta',
                'payment_method' => 'bank_transfer',
                'status' => ['pending', 'processing', 'shipped', 'delivered'][rand(0, 3)],
                'fcm_token' => null,
            ]);

            // Add random products to order
            foreach ($products as $product) {
                if (rand(0, 1)) {
                    $quantity = rand(1, 3);
                    $order->orderItems()->create([
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'notes' => 'Test order item',
                    ]);

                    $order->total_amount += $quantity;
                    $order->total_price += ($quantity * $product->price);
                }
            }

            $order->save();
        }
    }
}
