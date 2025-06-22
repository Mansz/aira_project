<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing orders from OrderSeeder
        $orders = DB::table('orders')->orderBy('id')->limit(5)->pluck('id')->toArray();
        
        // If no orders exist, skip shipment seeding
        if (empty($orders)) {
            $this->command->info('No orders found. Skipping shipment seeding.');
            return;
        }

        // Sample shipment data
        $shipments = [
            [
                'order_number' => 'SHP-001',
                'order_id' => $orders[0],
                'customer_name' => 'John Doe',
                'customer_phone' => '+62 812 3456 7890',
                'address_street' => 'Jl. Sudirman No. 123',
                'address_city' => 'Jakarta Pusat',
                'address_province' => 'DKI Jakarta',
                'address_postal_code' => '10110',
                'courier_name' => 'JNE',
                'courier_service' => 'REG',
                'courier_tracking_number' => 'JNE123456789',
                'status' => 'processing',
                'weight' => 1.5,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'order_number' => 'SHP-002',
                'order_id' => $orders[1],
                'customer_name' => 'Jane Smith',
                'customer_phone' => '+62 813 4567 8901',
                'address_street' => 'Jl. Gatot Subroto No. 456',
                'address_city' => 'Jakarta Selatan',
                'address_province' => 'DKI Jakarta',
                'address_postal_code' => '12190',
                'courier_name' => 'TIKI',
                'courier_service' => 'ONS',
                'courier_tracking_number' => 'TIKI987654321',
                'status' => 'in_transit',
                'weight' => 2.0,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(1),
            ],
            [
                'order_number' => 'SHP-003',
                'order_id' => $orders[2],
                'customer_name' => 'Bob Johnson',
                'customer_phone' => '+62 814 5678 9012',
                'address_street' => 'Jl. Thamrin No. 789',
                'address_city' => 'Jakarta Pusat',
                'address_province' => 'DKI Jakarta',
                'address_postal_code' => '10230',
                'courier_name' => 'POS Indonesia',
                'courier_service' => 'Kilat Khusus',
                'courier_tracking_number' => 'POS555666777',
                'status' => 'out_for_delivery',
                'weight' => 0.8,
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subHours(6),
            ],
            [
                'order_number' => 'SHP-004',
                'order_id' => $orders[3],
                'customer_name' => 'Alice Brown',
                'customer_phone' => '+62 815 6789 0123',
                'address_street' => 'Jl. Kuningan No. 321',
                'address_city' => 'Jakarta Selatan',
                'address_province' => 'DKI Jakarta',
                'address_postal_code' => '12940',
                'courier_name' => 'J&T Express',
                'courier_service' => 'EZ',
                'courier_tracking_number' => 'JT888999000',
                'status' => 'delivered',
                'weight' => 3.2,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(1),
            ],
            [
                'order_number' => 'SHP-005',
                'order_id' => $orders[4],
                'customer_name' => 'Charlie Wilson',
                'customer_phone' => '+62 816 7890 1234',
                'address_street' => 'Jl. Casablanca No. 654',
                'address_city' => 'Jakarta Selatan',
                'address_province' => 'DKI Jakarta',
                'address_postal_code' => '12870',
                'courier_name' => 'SiCepat',
                'courier_service' => 'REG',
                'courier_tracking_number' => 'SC111222333',
                'status' => 'processing',
                'weight' => 1.0,
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subHours(12),
            ],
        ];

        // Insert shipments and their items
        foreach ($shipments as $shipment) {
            $shipmentId = DB::table('shipments')->insertGetId($shipment);
            
            // Add sample items for each shipment
            $items = [];
            switch($shipment['order_number']) {
                case 'SHP-001':
                    $items = [
                        ['item_name' => 'T-Shirt Lengan Pendek', 'quantity' => 2, 'weight' => 0.5],
                        ['item_name' => 'Celana Jeans', 'quantity' => 1, 'weight' => 1.0],
                    ];
                    break;
                case 'SHP-002':
                    $items = [
                        ['item_name' => 'Sepatu Running', 'quantity' => 1, 'weight' => 1.2],
                        ['item_name' => 'Kaos Kaki Sport', 'quantity' => 3, 'weight' => 0.3],
                        ['item_name' => 'Topi Baseball', 'quantity' => 1, 'weight' => 0.5],
                    ];
                    break;
                case 'SHP-003':
                    $items = [
                        ['item_name' => 'Buku Novel', 'quantity' => 2, 'weight' => 0.4],
                        ['item_name' => 'Pembatas Buku', 'quantity' => 4, 'weight' => 0.4],
                    ];
                    break;
                case 'SHP-004':
                    $items = [
                        ['item_name' => 'Tas Ransel', 'quantity' => 1, 'weight' => 2.0],
                        ['item_name' => 'Botol Minum', 'quantity' => 2, 'weight' => 0.6],
                        ['item_name' => 'Payung Lipat', 'quantity' => 1, 'weight' => 0.6],
                    ];
                    break;
                case 'SHP-005':
                    $items = [
                        ['item_name' => 'Kemeja Formal', 'quantity' => 1, 'weight' => 0.5],
                        ['item_name' => 'Dasi', 'quantity' => 1, 'weight' => 0.5],
                    ];
                    break;
            }
            
            foreach ($items as $item) {
                DB::table('shipment_items')->insert([
                    'shipment_id' => $shipmentId,
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'weight' => $item['weight'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Shipments and items seeded successfully!');
    }
}
