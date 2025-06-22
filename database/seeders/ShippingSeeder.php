<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\ShippingAddress;

class ShippingSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get existing users or create some
        $users = User::all();
        if ($users->isEmpty()) {
            $users = User::factory(3)->create();
        }

        // Create shipping addresses for users
        foreach ($users as $user) {
            if (!$user->shippingAddresses()->exists()) {
                ShippingAddress::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'phone' => '+62812345678' . $user->id,
                    'address' => 'Jl. Example No. ' . (100 + $user->id),
                    'city' => ['Jakarta', 'Surabaya', 'Bandung'][($user->id - 1) % 3],
                    'province' => ['DKI Jakarta', 'Jawa Timur', 'Jawa Barat'][($user->id - 1) % 3],
                    'postal_code' => '1234' . $user->id,
                    'is_default' => true,
                ]);
            }
        }

        // Create orders with shipping information
        $shippingStatuses = [
            Order::SHIPPING_STATUS_PROCESSING,
            Order::SHIPPING_STATUS_IN_TRANSIT,
            Order::SHIPPING_STATUS_OUT_FOR_DELIVERY,
            Order::SHIPPING_STATUS_DELIVERED,
        ];

        $couriers = [
            ['name' => 'JNE', 'service' => 'REG'],
            ['name' => 'SiCepat', 'service' => 'BEST'],
            ['name' => 'J&T', 'service' => 'EXPRESS'],
            ['name' => 'Pos Indonesia', 'service' => 'REGULER'],
            ['name' => 'TIKI', 'service' => 'ONS'],
        ];

        foreach ($users as $index => $user) {
            $shippingAddress = $user->shippingAddresses()->first();
            $courier = $couriers[$index % count($couriers)];
            $shippingStatus = $shippingStatuses[$index % count($shippingStatuses)];

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => rand(100000, 500000),
                'shipping_address' => $shippingAddress->address . ', ' . $shippingAddress->city . ', ' . $shippingAddress->province . ' ' . $shippingAddress->postal_code,
                'payment_method' => ['Bank Transfer', 'Credit Card', 'E-Wallet'][rand(0, 2)],
                'status' => 'Diproses',
                'shipping_status' => $shippingStatus,
                'courier_name' => $courier['name'],
                'courier_service' => $courier['service'],
                'tracking_number' => strtoupper($courier['name']) . rand(100000000, 999999999),
                'shipping_cost' => rand(10000, 50000),
                'estimated_delivery_date' => now()->addDays(rand(1, 7)),
                'delivered_at' => $shippingStatus === Order::SHIPPING_STATUS_DELIVERED ? now()->subDays(rand(1, 3)) : null,
                'fcm_token' => 'dummy_fcm_token_' . $user->id,
            ]);

            // Add the shipping_address_id relationship
            if ($shippingAddress) {
                $order->update(['shipping_address_id' => $shippingAddress->id]);
            }
        }

        // Create additional orders for more test data
        for ($i = 0; $i < 7; $i++) {
            $user = $users->random();
            $shippingAddress = $user->shippingAddresses()->first();
            $courier = $couriers[rand(0, count($couriers) - 1)];
            $shippingStatus = $shippingStatuses[rand(0, count($shippingStatuses) - 1)];

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => rand(100000, 500000),
                'shipping_address' => $shippingAddress->address . ', ' . $shippingAddress->city . ', ' . $shippingAddress->province . ' ' . $shippingAddress->postal_code,
                'payment_method' => ['Bank Transfer', 'Credit Card', 'E-Wallet'][rand(0, 2)],
                'status' => ['Diproses', 'Dikirim', 'Selesai'][rand(0, 2)],
                'shipping_status' => $shippingStatus,
                'courier_name' => $courier['name'],
                'courier_service' => $courier['service'],
                'tracking_number' => strtoupper($courier['name']) . rand(100000000, 999999999),
                'shipping_cost' => rand(10000, 50000),
                'estimated_delivery_date' => now()->addDays(rand(1, 7)),
                'delivered_at' => $shippingStatus === Order::SHIPPING_STATUS_DELIVERED ? now()->subDays(rand(1, 3)) : null,
                'fcm_token' => 'dummy_fcm_token_' . $user->id,
            ]);

            if ($shippingAddress) {
                $order->update(['shipping_address_id' => $shippingAddress->id]);
            }
        }
    }
}
