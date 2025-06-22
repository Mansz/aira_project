<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LiveStream;
use App\Models\LiveVoucher;
use App\Models\LiveOrder;
use App\Models\Product;
use App\Models\User;

class LiveStreamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some sample live streams
        $streams = [
            [
                'user_id' => 1,
                'title' => 'Flash Sale Batik Collection',
                'description' => 'Special discount for traditional batik collection',
                'status' => 'ended',
                'room_id' => 'room_' . uniqid(),
                'stream_key' => 'key_' . uniqid(),
                'scheduled_at' => now()->subDays(2),
                'started_at' => now()->subDays(2),
                'ended_at' => now()->subDays(2)->addHours(2),
                'viewer_count' => 1250,
            ],
            [
                'user_id' => 1,
                'title' => 'New Arrival Fashion Show',
                'description' => 'Showcasing our latest fashion collection',
                'status' => 'ended',
                'room_id' => 'room_' . uniqid(),
                'stream_key' => 'key_' . uniqid(),
                'scheduled_at' => now()->subDays(1),
                'started_at' => now()->subDays(1),
                'ended_at' => now()->subDays(1)->addHours(3),
                'viewer_count' => 890,
            ],
            [
                'user_id' => 1,
                'title' => 'Weekend Special Sale',
                'description' => 'Weekend exclusive deals and discounts',
                'status' => 'live',
                'room_id' => 'room_' . uniqid(),
                'stream_key' => 'key_' . uniqid(),
                'scheduled_at' => now()->subHours(1),
                'started_at' => now()->subHours(1),
                'ended_at' => null,
                'viewer_count' => 456,
            ],
        ];

        foreach ($streams as $streamData) {
            $stream = LiveStream::create($streamData);

            // Create vouchers for each stream
            $vouchers = [
                [
                    'code' => 'LIVE' . $stream->id . '10',
                    'discount_type' => 'percentage',
                    'discount_value' => 10,
                    'description' => '10% discount for live stream viewers',
                    'live_stream_id' => $stream->id,
                    'start_time' => $stream->started_at,
                    'end_time' => $stream->ended_at ?? now()->addHours(24),
                    'active' => true,
                ],
                [
                    'code' => 'FLASH' . $stream->id,
                    'discount_type' => 'amount',
                    'discount_value' => 50000,
                    'description' => 'Rp 50.000 discount for flash sale',
                    'live_stream_id' => $stream->id,
                    'start_time' => $stream->started_at,
                    'end_time' => $stream->ended_at ?? now()->addHours(24),
                    'active' => true,
                ],
            ];

            foreach ($vouchers as $voucherData) {
                LiveVoucher::create($voucherData);
            }

            // Create some sample orders for ended streams
            if ($stream->status === 'ended') {
                $users = User::limit(3)->get();
                $orders = \App\Models\Order::limit(3)->get();
                
                foreach ($users as $index => $user) {
                    if (isset($orders[$index])) {
                        LiveOrder::create([
                            'order_id' => $orders[$index]->id,
                            'live_stream_id' => $stream->id,
                            'buyer_id' => $user->id,
                            'total_amount' => rand(100000, 500000),
                            'voucher_id' => $stream->vouchers->random()->id,
                            'discount_amount' => rand(10000, 50000),
                            'order_details' => [
                                'items' => [
                                    [
                                        'product_name' => 'Sample Product',
                                        'quantity' => rand(1, 3),
                                        'price' => rand(50000, 200000),
                                    ]
                                ]
                            ],
                        ]);
                    }
                }
            }

            // Associate some products with the stream
            $products = Product::limit(3)->get();
            if ($products->count() > 0) {
                $stream->products()->attach($products->pluck('id'));
            }
        }
    }
}
