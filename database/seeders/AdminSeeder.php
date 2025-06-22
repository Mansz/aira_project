<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $existingAdmin = DB::table('admins')->where('email', 'admin@example.com')->first();
        if (!$existingAdmin) {
            DB::table('admins')->insert([
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'permissions' => json_encode([
                    'manage_admins',
                    'manage_products',
                    'manage_orders',
                    'manage_payments',
                    'manage_shipping',
                    'manage_customers',
                    'manage_streaming',
                    'manage_whatsapp',
                    'manage_settings',
                    'view_analytics',
                    'manage_content',
                ]),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
