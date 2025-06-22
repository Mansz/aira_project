<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppAutoReply;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WhatsAppController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Display WhatsApp dashboard
     */
    public function index()
    {
        $messages = WhatsAppMessage::latest()
            ->paginate(20);

        return view('admin.whatsapp.index', compact('messages'));
    }

    /**
     * Display auto-replies management page
     */
    public function autoReplies()
    {
        $autoReplies = WhatsAppAutoReply::latest()
            ->paginate(20);

        return view('admin.whatsapp.auto-replies.index', compact('autoReplies'));
    }

    /**
     * Store a new auto-reply
     */
    public function storeAutoReply(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
            'response' => 'required|string',
            'is_regex' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $autoReply = WhatsAppAutoReply::create($validated);

        // Clear cache to refresh auto-replies
        Cache::forget('whatsapp_auto_replies');

        return response()->json([
            'message' => 'Auto reply created successfully',
            'auto_reply' => $autoReply,
        ]);
    }

    /**
     * Update an auto-reply
     */
    public function updateAutoReply(Request $request, WhatsAppAutoReply $autoReply)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
            'response' => 'required|string',
            'is_regex' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $autoReply->update($validated);

        // Clear cache to refresh auto-replies
        Cache::forget('whatsapp_auto_replies');

        return response()->json([
            'message' => 'Auto reply updated successfully',
            'auto_reply' => $autoReply,
        ]);
    }

    /**
     * Delete an auto-reply
     */
    public function destroyAutoReply(WhatsAppAutoReply $autoReply)
    {
        $autoReply->delete();
        Cache::forget('whatsapp_auto_replies');

        return response()->json([
            'message' => 'Auto reply deleted successfully'
        ]);
    }

    /**
     * Display broadcast page
     */
    public function broadcast()
    {
        $broadcasts = WhatsAppMessage::where('metadata->type', 'broadcast')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.whatsapp.broadcast.index', compact('broadcasts'));
    }

    /**
     * Preview broadcast recipients
     */
    public function previewBroadcast(Request $request)
    {
        $query = User::whereNotNull('phone_number')
            ->withCount('orders')
            ->with(['orders' => function ($q) {
                $q->latest();
            }]);

        if ($request->input('has_orders')) {
            $query->has('orders');
        }

        if ($request->input('recent_orders')) {
            $query->whereHas('orders', function ($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            });
        }

        $recipients = $query->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'phone_number' => $user->phone_number,
                    'orders_count' => $user->orders_count,
                    'last_order_date' => $user->orders->first() 
                        ? $user->orders->first()->created_at->format('d M Y')
                        : null,
                ];
            });

        return response()->json([
            'recipients' => $recipients
        ]);
    }

    /**
     * Send broadcast message
     */
    public function sendBroadcast(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'filters' => 'array',
        ]);

        $filters = [
            'min_orders' => $request->input('filters.has_orders') ? 1 : 0,
            'last_order_after' => $request->input('filters.recent_orders') 
                ? now()->subDays(30) 
                : null,
        ];

        $result = $this->whatsappService->sendBroadcast(
            $validated['message'],
            $filters,
            ['initiated_by' => auth()->id()]
        );

        return response()->json([
            'message' => 'Broadcast message sent successfully',
            'result' => $result
        ]);
    }

    /**
     * Show message logs
     */
    public function logs()
    {
        $logs = WhatsAppMessage::with('user')
            ->latest()
            ->paginate(50);

        $stats = [
            'total' => WhatsAppMessage::count(),
            'sent' => WhatsAppMessage::where('direction', 'outbound')->count(),
            'received' => WhatsAppMessage::where('direction', 'inbound')->count(),
            'failed' => WhatsAppMessage::where('status', 'failed')->count(),
        ];

        return view('admin.whatsapp.logs', compact('logs', 'stats'));
    }

    /**
     * Show conversation with a specific number
     */
    public function conversation($phoneNumber)
    {
        $messages = WhatsAppMessage::where('phone_number', $phoneNumber)
            ->latest()
            ->paginate(50);

        $user = User::where('phone_number', $phoneNumber)->first();

        return view('admin.whatsapp.conversation', compact('messages', 'phoneNumber', 'user'));
    }

    /**
     * Send a message in conversation
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $this->whatsappService->sendMessage(
            $validated['phone_number'],
            $validated['message'],
            ['sent_by_admin' => auth()->id()]
        );

        return response()->json([
            'status' => $result ? 'success' : 'error',
            'message' => $result ? 'Message sent successfully' : 'Failed to send message'
        ]);
    }

    public function export(Request $request)
{
    $messages = WhatsAppMessage::all(); // Ambil semua pesan

    // Buat file CSV atau format lain sesuai kebutuhan
    $csvFileName = 'whatsapp_messages_' . date('Ymd') . '.csv';
    $handle = fopen('php://output', 'w');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $csvFileName . '"');

    // Tulis header CSV
    fputcsv($handle, ['Date', 'Phone Number', 'User', 'Message', 'Direction', 'Status']);

    foreach ($messages as $message) {
        fputcsv($handle, [
            $message->created_at,
            $message->phone_number,
            optional($message->user)->name,
            $message->message,
            $message->direction,
            $message->status,
        ]);
    }

    fclose($handle);
    exit; // Hentikan eksekusi setelah menulis file
}
}