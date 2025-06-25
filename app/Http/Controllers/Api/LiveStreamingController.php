<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Models\LiveComment;
use App\Models\LiveVoucher;
use App\Services\ZegoCloudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LiveStreamingController extends Controller
{
    protected $zegoCloud;

    public function __construct(ZegoCloudService $zegoCloud)
    {
        $this->zegoCloud = $zegoCloud;
    }

    public function getActiveStreams()
    {
        $streams = LiveStream::where('status', 'live')
            ->with(['user:id,name', 'products:id,name,price,image_url'])
            ->get();

        return response()->json($streams);
    }

    public function getStreamDetails($streamId)
    {
        $stream = LiveStream::with(['user:id,name', 'products:id,name,price,image_url'])
            ->findOrFail($streamId);

        return response()->json($stream);
    }

    public function joinStream($streamId)
    {
        $stream = LiveStream::findOrFail($streamId);
        $stream->incrementViewerCount();

        $token = $this->zegoCloud->generateToken(
            auth()->id(),
            auth()->user()->name,
            $stream->room_id
        );

        return response()->json([
            'stream' => $stream,
            'token' => $token
        ]);
    }

    public function leaveStream($streamId)
    {
        $stream = LiveStream::findOrFail($streamId);
        $stream->decrementViewerCount();

        return response()->json(['message' => 'Successfully left the stream']);
    }

    public function getComments($streamId)
    {
        $comments = LiveComment::where('live_stream_id', $streamId)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($comments);
    }

    public function sendComment(Request $request, $streamId)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $stream = LiveStream::findOrFail($streamId);

        // Parse comment to check if it's an order
        $orderData = LiveComment::parseOrderComment($request->content);
        
        $comment = $stream->comments()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
            'is_order' => $orderData['is_order'],
            'order_code' => $orderData['order_code'] ?? null,
            'order_quantity' => $orderData['order_quantity'] ?? null,
        ]);

        $comment->load('user:id,name');

        // Broadcast the comment to all viewers (you can implement this using Laravel WebSockets or Pusher)
        // broadcast(new NewLiveComment($comment))->toOthers();

        return response()->json($comment);
    }

    public function getStreamProducts($streamId)
    {
        $stream = LiveStream::with('products:id,name,price,image_url')
            ->findOrFail($streamId);

        return response()->json($stream->products);
    }

    public function getActiveVouchers()
    {
        try {
            $vouchers = LiveVoucher::with('liveStream:id,title')
                ->where('active', true)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->latest()
                ->get()
                ->map(function ($voucher) {
                    return [
                        'id' => $voucher->id,
                        'code' => $voucher->code,
                        'discount_type' => $voucher->discount_type,
                        'discount_value' => $voucher->discount_value,
                        'description' => $voucher->description,
                        'start_time' => $voucher->start_time?->toISOString(),
                        'end_time' => $voucher->end_time?->toISOString(),
                        'is_valid' => $voucher->isValid(),
                        'live_stream' => $voucher->liveStream ? [
                            'id' => $voucher->liveStream->id,
                            'title' => $voucher->liveStream->title,
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $vouchers,
                'message' => 'Active vouchers retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active vouchers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
