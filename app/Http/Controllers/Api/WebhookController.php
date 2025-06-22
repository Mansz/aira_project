<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebhookLog;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Handle incoming webhook requests
     */
    public function handle(Request $request)
    {
        // Verify webhook signature
        if (!$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $payload = $request->all();
        $headers = $request->headers->all();
        $ipAddress = $request->ip();

        // Log the webhook request
        $webhookLog = WebhookLog::create([
            'source' => 'whatsapp_gateway',
            'event_type' => $payload['type'] ?? 'unknown',
            'payload' => $payload,
            'headers' => $headers,
            'ip_address' => $ipAddress,
            'status' => 'pending',
        ]);

        try {
            // Check if this is a WhatsApp message
            if (isset($payload['type']) && $payload['type'] === 'message') {
                $message = $payload['message'] ?? '';
                
                // Check for order status query
                if (preg_match('/cek(?:\s+)?pesanan(?:\s+)?(\d+)/i', $message, $matches)) {
                    $orderId = $matches[1];
                    $response = $this->handleOrderStatusQuery($orderId);
                    
                    // Send WhatsApp response
                    $this->whatsappService->sendMessage($payload['from'], $response);
                }
            }

            $webhookLog->update([
                'status' => 'success',
                'response' => ['message' => 'Webhook processed successfully'],
                'processed_at' => now(),
            ]);

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'webhook_log_id' => $webhookLog->id,
            ]);

            $webhookLog->update([
                'status' => 'failed',
                'response' => ['error' => $e->getMessage()],
                'processed_at' => now(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Verify webhook signature
     */
    protected function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Signature');
        if (!$signature) {
            return false;
        }

        $secret = config('whatsapp.webhook_secret');
        $payload = $request->getContent();
        
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Handle order status query
     */
    protected function handleOrderStatusQuery(string $orderId): string
    {
        $order = Order::find($orderId);
        
        if (!$order) {
            return "Maaf, pesanan dengan ID {$orderId} tidak ditemukan.";
        }

        $statusMessages = [
            'pending' => 'sedang menunggu pembayaran',
            'processing' => 'sedang diproses',
            'shipped' => 'sedang dalam pengiriman',
            'delivered' => 'telah diterima',
            'cancelled' => 'telah dibatalkan'
        ];

        $status = $statusMessages[$order->status] ?? $order->status;
        
        return "Status pesanan #{$orderId}: {$status}";
    }
}