<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AdminSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            ShipmentSeeder::class,
            PaymentSettingSeeder::class,
            SettingSeeder::class,
            PaymentSeeder::class,
            PaymentProofSeeder::class,
        ]);
    }
}
