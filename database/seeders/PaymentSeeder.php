<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin for verification
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

        // Get existing orders to create payments for
        $orders = DB::table('orders')->orderBy('id')->limit(5)->get();
        
        if ($orders->isEmpty()) {
            $this->command->info('No orders found. Skipping payment seeding.');
            return;
        }

        // Create payments with various statuses
        $payments = [];
        $statuses = ['pending', 'verified', 'rejected', 'pending', 'verified'];
        $paymentMethods = ['bank_transfer', 'credit_card', 'bank_transfer', 'bank_transfer', 'credit_card'];
        $amounts = [150000, 250000, 350000, 450000, 550000];

        foreach ($orders as $index => $order) {
            $payment = [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $amounts[$index] ?? 150000,
                'payment_method' => $paymentMethods[$index] ?? 'bank_transfer',
                'status' => $statuses[$index] ?? 'pending',
                'created_at' => now()->subDays(5 - $index),
                'updated_at' => now()->subDays(5 - $index),
            ];

            if ($payment['payment_method'] === 'bank_transfer') {
                $payment['bank_name'] = ['BCA', 'Mandiri', 'BNI'][rand(0, 2)];
                $payment['bank_account_number'] = '123456789' . $index;
                $payment['bank_account_holder'] = 'Customer ' . ($index + 1);
            } else {
                $payment['card_type'] = ['Visa', 'Mastercard'][rand(0, 1)];
                $payment['card_last4'] = '424' . $index;
            }

            if ($payment['status'] === 'verified') {
                $payment['verified_at'] = now()->subDays(3 - $index);
                $payment['verified_by'] = $admin->id;
            }

            $payments[] = $payment;
        }

        foreach ($payments as $payment) {
            Payment::updateOrCreate(
                [
                    'order_id' => $payment['order_id'],
                    'user_id' => $payment['user_id'],
                ],
                $payment
            );
        }
    }
}
