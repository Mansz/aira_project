<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FCMService
{
    protected $serverKey;
    protected $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        $this->serverKey = config('services.fcm.server_key');
        
        if (!$this->serverKey) {
            Log::warning('FCM server key not configured. FCM notifications will be disabled.');
        }
    }

    public function sendNotification($token, $title, $body, $data = [])
    {
        if (!$this->serverKey) {
            Log::warning('FCM notification skipped: server key not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json'
            ])->post($this->fcmUrl, [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => '1',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ])
            ]);

            if ($response->successful()) {
                Log::info('FCM notification sent successfully', [
                    'token' => $token,
                    'title' => $title,
                    'body' => $body
                ]);
                return true;
            }

            Log::error('FCM notification failed', [
                'token' => $token,
                'error' => $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('FCM notification error', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public static function getStatusIcon($status)
    {
        return match($status) {
            'Menunggu Pembayaran' => '🕒',
            'Menunggu Konfirmasi' => '🕒',
            'Diproses' => '🔄',
            'Dikirim' => '🚚',
            'Selesai' => '✅',
            'Dibatalkan' => '❌',
            default => '📦'
        };
    }

    public static function getStatusMessage($orderId, $status)
    {
        return match($status) {
            'Menunggu Pembayaran' => "Pesanan #{$orderId} menunggu pembayaran",
            'Menunggu Konfirmasi' => "Pembayaran untuk pesanan #{$orderId} sedang diverifikasi",
            'Diproses' => "Pesanan #{$orderId} sedang diproses",
            'Dikirim' => "Pesanan #{$orderId} sedang dalam pengiriman",
            'Selesai' => "Pesanan #{$orderId} telah selesai",
            'Dibatalkan' => "Pesanan #{$orderId} dibatalkan",
            default => "Update status pesanan #{$orderId}"
        };
    }

    public static function getStatusColor($status)
    {
        return match($status) {
            'Menunggu Pembayaran', 'Menunggu Konfirmasi' => 'warning',
            'Diproses' => 'info',
            'Dikirim' => 'primary',
            'Selesai' => 'success',
            'Dibatalkan' => 'danger',
            default => 'secondary'
        };
    }

    public function sendBatchNotification($tokens, $title, $body, $data = [])
    {
        if (empty($tokens)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json'
            ])->post($this->fcmUrl, [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => '1',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ])
            ]);

            if ($response->successful()) {
                Log::info('Batch FCM notification sent successfully', [
                    'tokens_count' => count($tokens),
                    'title' => $title
                ]);
                return true;
            }

            Log::error('Batch FCM notification failed', [
                'tokens_count' => count($tokens),
                'error' => $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Batch FCM notification error', [
                'tokens_count' => count($tokens),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendTopicNotification($topic, $title, $body, $data = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json'
            ])->post($this->fcmUrl, [
                'to' => '/topics/' . $topic,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => '1',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ],
                'data' => array_merge($data, [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ])
            ]);

            if ($response->successful()) {
                Log::info('Topic FCM notification sent successfully', [
                    'topic' => $topic,
                    'title' => $title
                ]);
                return true;
            }

            Log::error('Topic FCM notification failed', [
                'topic' => $topic,
                'error' => $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Topic FCM notification error', [
                'topic' => $topic,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
