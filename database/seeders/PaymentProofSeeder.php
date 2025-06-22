<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentProof;
use App\Models\Order;
use App\Models\Admin;

class PaymentProofSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create an admin for verification
        $admin = Admin::first();
        if (!$admin) {
            $admin = Admin::create([
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]);
        }

        // Create dummy orders if they don't exist
        $this->createDummyOrders();

        // Create payment proofs with various statuses
        $paymentProofs = [
            [
                'order_id' => 1001,
                'file_path' => 'payment-proofs/proof-1001.jpg',
                'status' => 'pending',
                'verified_at' => null,
                'verified_by' => null,
                'notes' => null,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'order_id' => 1002,
                'file_path' => 'payment-proofs/proof-1002.jpg',
                'status' => 'verified',
                'verified_at' => now()->subDays(3),
                'verified_by' => $admin->id,
                'notes' => 'Payment verified by admin',
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(3),
            ],
            [
                'order_id' => 1003,
                'file_path' => 'payment-proofs/proof-1003.jpg',
                'status' => 'rejected',
                'verified_at' => null,
                'verified_by' => null,
                'notes' => 'Bukti pembayaran tidak jelas, mohon upload ulang dengan kualitas yang lebih baik',
                'created_at' => now()->subDays(6),
                'updated_at' => now()->subDays(2),
            ],
            [
                'order_id' => 1004,
                'file_path' => 'payment-proofs/proof-1004.jpg',
                'status' => 'pending',
                'verified_at' => null,
                'verified_by' => null,
                'notes' => null,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'order_id' => 1005,
                'file_path' => 'payment-proofs/proof-1005.jpg',
                'status' => 'verified',
                'verified_at' => now()->subDays(1),
                'verified_by' => $admin->id,
                'notes' => 'Payment verified by admin',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(1),
            ],
            [
                'order_id' => 1006,
                'file_path' => 'payment-proofs/proof-1006.jpg',
                'status' => 'pending',
                'verified_at' => null,
                'verified_by' => null,
                'notes' => null,
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subHours(12),
            ],
            [
                'order_id' => 1007,
                'file_path' => 'payment-proofs/proof-1007.jpg',
                'status' => 'rejected',
                'verified_at' => null,
                'verified_by' => null,
                'notes' => 'Nominal transfer tidak sesuai dengan total pesanan',
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(1),
            ],
            [
                'order_id' => 1008,
                'file_path' => 'payment-proofs/proof-1008.jpg',
                'status' => 'verified',
                'verified_at' => now()->subHours(6),
                'verified_by' => $admin->id,
                'notes' => 'Payment verified by admin',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subHours(6),
            ],
            [
                'order_id' => 1009,
                'file_path' => 'payment-proofs/proof-1009.jpg',
                'status' => 'pending',
                'verified_at' => null,
                'verified_by' => null,
                'notes' => null,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
            [
                'order_id' => 1010,
                'file_path' => 'payment-proofs/proof-1010.jpg',
                'status' => 'pending',
                'verified_at' => null,
                'verified_by' => null,
                'notes' => null,
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
        ];

        // Insert payment proofs
        foreach ($paymentProofs as $proof) {
            PaymentProof::updateOrCreate(
                ['order_id' => $proof['order_id']],
                $proof
            );
        }

        // Create dummy files for testing
        $this->createDummyFiles();

        $this->command->info('Payment proofs seeded successfully!');
    }

    private function createDummyOrders()
    {
        // Check if Order model exists
        if (!class_exists('App\Models\Order')) {
            return;
        }

        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Create dummy orders if they don't exist
        $orders = [
            ['id' => 1001, 'user_id' => 1, 'total_amount' => 150000, 'total_price' => 150000, 'status' => 'pending', 'order_number' => 'ORD-1001', 'payment_method' => 'bank_transfer', 'shipping_address' => 'Test Address 1'],
            ['id' => 1002, 'user_id' => 1, 'total_amount' => 250000, 'total_price' => 250000, 'status' => 'processing', 'order_number' => 'ORD-1002', 'payment_method' => 'bank_transfer', 'shipping_address' => 'Test Address 2'],
            ['id' => 1003, 'user_id' => 1, 'total_amount' => 350000, 'total_price' => 350000, 'status' => 'pending', 'order_number' => 'ORD-1003', 'payment_method' => 'bank_transfer', 'shipping_address' => 'Test Address 3'],
            ['id' => 1004, 'user_id' => 1, 'total_amount' => 450000, 'total_price' => 450000, 'status' => 'pending', 'order_number' => 'ORD-1004', 'payment_method' => 'bank_transfer', 'shipping_address' => 'Test Address 4'],
            ['id' => 1005, 'user_id' => 1, 'total_amount' => 550000, 'total_price' => 550000, 'status' => 'processing', 'order_number' => 'ORD-1005', 'payment_method' => 'bank_transfer', 'shipping_address' => 'Test Address 5'],
            ['id' => 1006, 'user_id' => 1, 'total_amount' => 650000, 'total_price' => 650000, 'status' => 'pending', 'order_number' => 'ORD-1006', 'payment_method' => 'bank_transfer', 'shipping_address' => 'Test Address 6'],
            ['id' => 1007, 'user_id' => 1, 'total_amount' => 750000, 'total_price' => 750000, 'status' => 'pending', 'order_number' => 'ORD-1007', 'payment_method' => 'bank_transfer', 'shipping_address' => 'Test Address 7'],
            ['id' => 1008, 'user_id' => 1, 'total_amount' => 850000, 'total_price' => 850000, 'status' => 'processing', 'order_number' => 'ORD-1008', 'payment_method' => 'bank_transfer', 'shipping_address' => 'Test Address 8'],
            ['id' => 1009, 'user_id' => 1, 'total_amount' => 950000, 'total_price' => 950000, 'status' => 'pending', 'order_number' => 'ORD-1009', 'payment_method' => 'bank_transfer', 'shipping_address' => 'Test Address 9'],
            ['id' => 1010, 'user_id' => 1, 'total_amount' => 1050000, 'total_price' => 1050000, 'status' => 'pending', 'order_number' => 'ORD-1010', 'payment_method' => 'bank_transfer', 'shipping_address' => 'Test Address 10'],
        ];

        foreach ($orders as $order) {
            try {
                DB::table('orders')->insertOrIgnore([
                    'id' => $order['id'],
                    'user_id' => $order['user_id'],
                    'total_amount' => $order['total_amount'],
                    'total_price' => $order['total_price'],
                    'status' => $order['status'],
                    'order_number' => $order['order_number'],
                    'payment_method' => $order['payment_method'],
                    'shipping_address' => $order['shipping_address'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Table might not exist, continue without orders
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function createDummyFiles()
    {
        // Create storage directory if it doesn't exist
        if (!file_exists(storage_path('app/public/payment-proofs'))) {
            mkdir(storage_path('app/public/payment-proofs'), 0755, true);
        }

        // Create a simple 1x1 pixel image for testing
        $dummyContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        
        for ($i = 1001; $i <= 1010; $i++) {
            $filePath = storage_path("app/public/payment-proofs/proof-{$i}.jpg");
            if (!file_exists($filePath)) {
                file_put_contents($filePath, $dummyContent);
            }
        }
    }
}
