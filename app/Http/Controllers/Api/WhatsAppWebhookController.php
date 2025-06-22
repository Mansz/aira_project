<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessage;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Handle incoming webhook from WhatsApp Gateway
     */
    public function handle(Request $request)
    {
        Log::info('WhatsApp webhook received', $request->all());

        try {
            $data = $request->all();
            
            // Validate webhook data
            if (!isset($data['from']) || !isset($data['message'])) {
                return response()->json(['status' => 'error', 'message' => 'Invalid webhook data'], 400);
            }

            // Create inbound message record
            $message = WhatsAppMessage::create([
                'message_id' => $data['message_id'] ?? null,
                'phone_number' => $this->formatPhoneNumber($data['from']),
                'message' => $data['message'],
                'status' => 'received',
                'direction' => 'inbound',
                'metadata' => array_merge($data, [
                    'received_at' => now()->toIso8601String(),
                ]),
            ]);

            // Try to associate with user
            $user = User::where('phone_number', 'LIKE', '%' . $message->phone_number)->first();
            if ($user) {
                $message->update(['user_id' => $user->id]);
            }

            // Process auto-replies
            $this->whatsappService->processIncomingMessage(
                $message->phone_number,
                $message->message,
                $message->message_id
            );

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error', [
                'message' => $e->getMessage(),
                'data' => $request->all(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Handle message status updates
     */
    public function status(Request $request)
    {
        Log::info('WhatsApp status update received', $request->all());

        try {
            $data = $request->all();
            
            // Validate status update data
            if (!isset($data['message_id']) || !isset($data['status'])) {
                return response()->json(['status' => 'error', 'message' => 'Invalid status data'], 400);
            }

            // Update message status
            $message = WhatsAppMessage::where('message_id', $data['message_id'])->first();
            if ($message) {
                $message->update([
                    'status' => $data['status'],
                    'metadata' => array_merge($message->metadata ?? [], [
                        'status_update' => [
                            'status' => $data['status'],
                            'timestamp' => now()->toIso8601String(),
                        ]
                    ]),
                ]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('WhatsApp status webhook error', [
                'message' => $e->getMessage(),
                'data' => $request->all(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Format phone number to standard format
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