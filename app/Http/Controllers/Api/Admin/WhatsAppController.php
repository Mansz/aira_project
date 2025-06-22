<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppAutoReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WhatsAppController extends Controller
{
    public function index(Request $request)
    {
        $query = WhatsAppMessage::with(['user', 'order'])
            ->latest();

        // Filter by direction
        if ($request->has('direction')) {
            $query->where('direction', $request->direction);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by phone number
        if ($request->has('phone')) {
            $query->where('phone_number', 'like', '%' . $request->phone . '%');
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $messages = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $messages,
            'message' => 'WhatsApp messages retrieved successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'message' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'metadata' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $message = WhatsAppMessage::create([
            'phone_number' => $request->phone_number,
            'message' => $request->message,
            'user_id' => $request->user_id,
            'order_id' => $request->order_id,
            'metadata' => $request->metadata,
            'direction' => 'outbound',
            'status' => 'pending'
        ]);

        // Here you would integrate with your WhatsApp service provider
        // to actually send the message

        return response()->json([
            'data' => $message,
            'message' => 'WhatsApp message created successfully'
        ], 201);
    }

    public function show(WhatsAppMessage $message)
    {
        $message->load(['user', 'order']);
        
        return response()->json([
            'data' => $message,
            'message' => 'WhatsApp message retrieved successfully'
        ]);
    }

    public function getAutoReplies(Request $request)
    {
        $query = WhatsAppAutoReply::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $autoReplies = $query->get();

        return response()->json([
            'data' => $autoReplies,
            'message' => 'Auto-replies retrieved successfully'
        ]);
    }

    public function storeAutoReply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|string',
            'response' => 'required|string',
            'is_regex' => 'boolean',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $autoReply = WhatsAppAutoReply::create($request->all());

        return response()->json([
            'data' => $autoReply,
            'message' => 'Auto-reply created successfully'
        ], 201);
    }

    public function updateAutoReply(Request $request, WhatsAppAutoReply $autoReply)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|string',
            'response' => 'required|string',
            'is_regex' => 'boolean',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $autoReply->update($request->all());

        return response()->json([
            'data' => $autoReply,
            'message' => 'Auto-reply updated successfully'
        ]);
    }

    public function deleteAutoReply(WhatsAppAutoReply $autoReply)
    {
        $autoReply->delete();

        return response()->json([
            'message' => 'Auto-reply deleted successfully'
        ]);
    }

    public function toggleAutoReply(WhatsAppAutoReply $autoReply)
    {
        $autoReply->update([
            'is_active' => !$autoReply->is_active
        ]);

        return response()->json([
            'data' => $autoReply,
            'message' => 'Auto-reply status toggled successfully'
        ]);
    }

    public function getStats()
    {
        $stats = [
            'total_messages' => WhatsAppMessage::count(),
            'total_sent' => WhatsAppMessage::where('direction', 'outbound')->count(),
            'total_received' => WhatsAppMessage::where('direction', 'inbound')->count(),
            'pending_messages' => WhatsAppMessage::where('status', 'pending')->count(),
            'failed_messages' => WhatsAppMessage::where('status', 'failed')->count(),
            'active_auto_replies' => WhatsAppAutoReply::where('is_active', true)->count()
        ];

        return response()->json([
            'data' => $stats,
            'message' => 'WhatsApp statistics retrieved successfully'
        ]);
    }
}
