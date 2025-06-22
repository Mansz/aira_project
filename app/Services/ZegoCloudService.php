<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ZegoCloudService
{
    protected $appId;
    protected $serverSecret;
    protected $baseUrl;

    public function __construct()
    {
        $this->appId = config('services.zego.app_id');
        $this->serverSecret = config('services.zego.server_secret');
        $this->baseUrl = 'https://rtc-api.zego.im';
    }

    public function generateToken($userId, $userName, $roomId, $role = 1)
    {
        $timestamp = time();
        $nonce = rand(100000, 999999);

        $payload = [
            'app_id' => $this->appId,
            'user_id' => (string)$userId,
            'room_id' => $roomId,
            'privilege' => [
                'stream' => [
                    'publish' => $role === 1, // Host can publish
                    'play' => true // Everyone can play
                ],
                'chat' => [
                    'send' => true,
                    'receive' => true
                ]
            ],
            'stream_id_prefix' => '',
            'user_name' => $userName,
            'nonce' => $nonce,
            'timestamp' => $timestamp,
            'expired_time' => $timestamp + 7200 // Token valid for 2 hours
        ];

        $signature = $this->generateSignature($payload);
        $payload['signature'] = $signature;

        return base64_encode(json_encode($payload));
    }

    protected function generateSignature($payload)
    {
        $stringToSign = implode('', [
            $payload['app_id'],
            $payload['user_id'],
            $payload['room_id'],
            $payload['privilege']['stream']['publish'] ? '1' : '0',
            $payload['privilege']['stream']['play'] ? '1' : '0',
            $payload['privilege']['chat']['send'] ? '1' : '0',
            $payload['privilege']['chat']['receive'] ? '1' : '0',
            $payload['stream_id_prefix'],
            $payload['user_name'],
            $payload['nonce'],
            $payload['timestamp'],
            $payload['expired_time']
        ]);

        return hash_hmac('sha256', $stringToSign, $this->serverSecret);
    }

    public function createRoom($title, $hostId)
    {
        $roomId = 'room_' . uniqid();
        $streamId = 'stream_' . uniqid();

        return [
            'room_id' => $roomId,
            'stream_id' => $streamId
        ];
    }

    public function endRoom($roomId)
    {
        // You can implement room cleanup logic here if needed
        return true;
    }

    public function getRoomStatus($roomId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->appId . ':' . $this->serverSecret)
            ])->get($this->baseUrl . '/rooms/' . $roomId);

            return $response->json();
        } catch (\Exception $e) {
            return null;
        }
    }
}
