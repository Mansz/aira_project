<?php

namespace App\Services;

use App\Models\WhatsAppMessage;
use App\Models\WhatsAppAutoReply;
use App\Models\Order;
use App\Models\LiveStream;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WhatsAppService
{
    protected $baseUrl;
    protected $apiKey;
    protected $deviceId;
    protected $storeName;
    protected $storePhone;

    public function __construct()
    {
        $this->baseUrl = config('whatsapp.gateway_url');
        $this->apiKey = config('whatsapp.api_key');
        $this->deviceId = config('whatsapp.device_id');
        $this->storeName = config('whatsapp.store_name');
        $this->storePhone = config('whatsapp.store_phone');
    }

    /**
     * Send a WhatsApp message
     */
    public function sendMessage($phoneNumber, $message, $metadata = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->baseUrl . '/api/send', [
                'phone' => $this->formatPhoneNumber($phoneNumber),
                'message' => $message,
                'device_id' => $this->deviceId,
            ]);

            $whatsappMessage = WhatsAppMessage::create([
                'message_id' => $response->json('message_id'),
                'phone_number' => $phoneNumber,
                'message' => $message,
                'status' => $response->successful() ? 'sent' : 'failed',
                'direction' => 'outbound',
                'metadata' => array_merge($metadata, [
                    'response' => $response->json(),
                    'sent_at' => now()->toIso8601String(),
                ]),
            ]);

            if (!$response->successful()) {
                Log::error('WhatsApp message failed', [
                    'phone' => $phoneNumber,
                    'error' => $response->json(),
                ]);
                return false;
            }

            return $whatsappMessage;
        } catch (\Exception $e) {
            Log::error('WhatsApp service error', [
                'message' => $e->getMessage(),
                'phone' => $phoneNumber,
            ]);
            return false;
        }
    }

    /**
     * Send broadcast message to users
     */
    public function sendBroadcast($message, $filters = [], $metadata = [])
    {
        $query = User::whereNotNull('phone_number');

        // Apply filters
        if (!empty($filters['min_orders'])) {
            $query->has('orders', '>=', $filters['min_orders']);
        }
        if (!empty($filters['last_order_after'])) {
            $query->whereHas('orders', function ($q) use ($filters) {
                $q->where('created_at', '>=', $filters['last_order_after']);
            });
        }
        if (!empty($filters['user_ids'])) {
            $query->whereIn('id', $filters['user_ids']);
        }

        $users = $query->get();
        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            $result = $this->sendMessage($user->phone_number, $message, array_merge($metadata, [
                'type' => 'broadcast',
                'user_id' => $user->id,
            ]));

            if ($result) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return [
            'total' => $users->count(),
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    /**
     * Send order status notification
     */
    public function sendOrderStatus(Order $order)
    {
        if (!$order->user || !$order->user->phone_number) {
            return false;
        }

        $template = config('whatsapp.templates.order_status.' . $order->status);
        if (!$template) {
            return false;
        }

        $message = strtr($template, [
            '{order_number}' => $order->order_number,
            '{tracking_number}' => $order->tracking_number ?? 'N/A',
        ]);

        return $this->sendMessage($order->user->phone_number, $message, [
            'order_id' => $order->id,
            'type' => 'order_status',
            'status' => $order->status,
        ]);
    }

    /**
     * Send payment status notification
     */
    public function sendPaymentStatus(Order $order, $status)
    {
        if (!$order->user || !$order->user->phone_number) {
            return false;
        }

        $template = config('whatsapp.templates.payment.' . $status);
        if (!$template) {
            return false;
        }

        $message = strtr($template, [
            '{order_number}' => $order->order_number,
        ]);

        return $this->sendMessage($order->user->phone_number, $message, [
            'order_id' => $order->id,
            'type' => 'payment_status',
            'status' => $status,
        ]);
    }

    /**
     * Process incoming message
     */
    public function processIncomingMessage($from, $message, $messageId = null)
    {
        // Store incoming message
        WhatsAppMessage::create([
            'message_id' => $messageId,
            'phone_number' => $from,
            'message' => $message,
            'status' => 'received',
            'direction' => 'inbound',
            'metadata' => [
                'received_at' => now()->toIso8601String(),
            ],
        ]);

        // Get active auto-replies from cache or database
        $autoReplies = Cache::remember('whatsapp_auto_replies', 300, function () {
            return WhatsAppAutoReply::active()->get();
        });

        foreach ($autoReplies as $autoReply) {
            if ($autoReply->matches($message)) {
                // Extract order number if present
                preg_match('/#(\d+)/', $message, $matches);
                $variables = [];

                if (isset($matches[1])) {
                    $order = Order::where('order_number', 'LIKE', "%{$matches[1]}")->first();
                    if ($order) {
                        $variables = [
                            '{order_number}' => $order->order_number,
                            '{order_status}' => $order->status,
                            '{tracking_number}' => $order->tracking_number ?? 'N/A',
                            '{order_total}' => number_format($order->total, 0, ',', '.'),
                        ];
                    }
                }

                $response = $autoReply->getFormattedResponse($variables);

                $this->sendMessage($from, $response, [
                    'type' => 'auto_reply',
                    'auto_reply_id' => $autoReply->id,
                    'original_message_id' => $messageId,
                    'variables' => $variables,
                ]);
                
                return true;
            }
        }

        return false;
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhoneNumber($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        if (substr($number, 0, 1) === '0') {
            $number = '62' . substr($number, 1);
        } elseif (substr($number, 0, 2) !== '62') {
            $number = '62' . $number;
        }
        return $number;
    }
}