<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_name',
                'value' => 'AIRA Live Shopping',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Nama Website'
            ],
            [
                'key' => 'site_description',
                'value' => 'Platform Live Shopping Terpercaya',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Deskripsi Website'
            ],
            
            // Notification Settings
            [
                'key' => 'notification_new_order',
                'value' => true,
                'type' => 'boolean',
                'group' => 'notification',
                'description' => 'Notifikasi Order Baru'
            ],
            [
                'key' => 'notification_payment_received',
                'value' => true,
                'type' => 'boolean',
                'group' => 'notification',
                'description' => 'Notifikasi Pembayaran Diterima'
            ],
            
            // WhatsApp Settings
            [
                'key' => 'whatsapp_enabled',
                'value' => true,
                'type' => 'boolean',
                'group' => 'whatsapp',
                'description' => 'Aktifkan WhatsApp'
            ],
            [
                'key' => 'whatsapp_number',
                'value' => '',
                'type' => 'string',
                'group' => 'whatsapp',
                'description' => 'Nomor WhatsApp Admin'
            ],
            
            // Streaming Settings
            [
                'key' => 'streaming_auto_record',
                'value' => true,
                'type' => 'boolean',
                'group' => 'streaming',
                'description' => 'Rekam Live Streaming Otomatis'
            ],
            [
                'key' => 'streaming_chat_enabled',
                'value' => true,
                'type' => 'boolean',
                'group' => 'streaming',
                'description' => 'Aktifkan Chat Live Streaming'
            ],
            
            // Shipping Settings
            [
                'key' => 'shipping_origin_city',
                'value' => '',
                'type' => 'string',
                'group' => 'shipping',
                'description' => 'Kota Asal Pengiriman'
            ],
            [
                'key' => 'shipping_default_weight',
                'value' => 1000,
                'type' => 'number',
                'group' => 'shipping',
                'description' => 'Berat Default (gram)'
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
