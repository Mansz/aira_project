<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Gateway Configuration
    |--------------------------------------------------------------------------
    */

    // WhatsApp Gateway API URL
    'gateway_url' => env('WHATSAPP_GATEWAY_URL', 'https://api.whatsapp.com'),

    // API Key for authentication
    'api_key' => env('WHATSAPP_API_KEY'),

    // Device ID for the WhatsApp instance
    'device_id' => env('WHATSAPP_DEVICE_ID'),

    // Webhook secret for signature validation
    'webhook_secret' => env('WHATSAPP_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Store Information
    |--------------------------------------------------------------------------
    */

    // Store name to use in message templates
    'store_name' => env('STORE_NAME', 'Aira Store'),

    // Store phone number for customer service
    'store_phone' => env('STORE_PHONE'),

    // Customer service hours
    'customer_service_hours' => env('STORE_CS_HOURS', '09:00 - 17:00 WIB'),

    /*
    |--------------------------------------------------------------------------
    | Message Templates
    |--------------------------------------------------------------------------
    */

    'templates' => [
        // Order status templates
        'order_status' => [
            'pending' => 'Pesanan #{order_number} sedang menunggu pembayaran. Silakan lakukan pembayaran sesuai instruksi yang telah diberikan.',
            'processing' => 'Pesanan #{order_number} sedang diproses. Kami akan segera mengirimkan pesanan Anda.',
            'shipped' => 'Pesanan #{order_number} telah dikirim dengan nomor resi {tracking_number}. Silakan pantau status pengiriman Anda.',
            'completed' => 'Pesanan #{order_number} telah selesai. Terima kasih telah berbelanja di {store_name}!',
            'cancelled' => 'Pesanan #{order_number} telah dibatalkan. Untuk informasi lebih lanjut, silakan hubungi customer service kami.',
        ],

        // Payment status templates
        'payment' => [
            'received' => 'Pembayaran untuk pesanan #{order_number} telah kami terima. Pesanan Anda akan segera diproses.',
            'rejected' => 'Maaf, pembayaran untuk pesanan #{order_number} tidak dapat kami verifikasi. Silakan kirim bukti pembayaran yang valid.',
        ],

        // Default auto-replies
        'default_replies' => [
            [
                'keyword' => 'hi|hello|halo',
                'is_regex' => true,
                'response' => 'Halo! Selamat datang di {store_name}. Ada yang bisa kami bantu?',
                'is_active' => true,
            ],
            [
                'keyword' => 'cs|customer service|bantuan',
                'is_regex' => true,
                'response' => 'Customer service kami siap membantu pada jam {cs_hours}. Silakan hubungi {store_phone}.',
                'is_active' => true,
            ],
            [
                'keyword' => 'status pesanan|cek pesanan|tracking',
                'is_regex' => true,
                'response' => 'Untuk cek status pesanan, silakan kirim nomor pesanan Anda dengan format: #(nomor pesanan)',
                'is_active' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */

    // Cache duration for auto-replies in seconds (5 minutes)
    'cache_duration' => 300,
];