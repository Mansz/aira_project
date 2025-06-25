<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Models\LiveOrder;
use App\Models\LiveVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StreamingController extends Controller
{
    public function index()
    {
        try {
            $streams = LiveStream::with(['products', 'vouchers'])
                ->latest()
                ->get()
                ->map(function ($stream) {
                    return [
                        'id' => $stream->id,
                        'title' => $stream->title,
                        'description' => $stream->description,
                        'status' => $stream->status,
                        'start_time' => $stream->start_time?->toISOString(),
                        'end_time' => $stream->end_time?->toISOString(),
                        'viewer_count' => $stream->viewer_count,
                        'products_count' => $stream->products->count(),
                        'vouchers_count' => $stream->vouchers->count(),
                    ];
                });

            return response()->json([
                'data' => $streams,
                'message' => 'Live streams retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve live streams',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stats()
    {
        try {
            // Active stream stats
            $activeStream = LiveStream::where('status', 'active')->first();
            
            $stats = [
                'active_stream' => null,
                'total_streams' => LiveStream::count(),
                'total_viewers' => LiveStream::sum('viewer_count') ?? 0,
                'total_orders' => LiveOrder::count(),
                'total_revenue' => LiveOrder::sum('total_amount') ?? 0,
                'total_vouchers' => LiveVoucher::count(),
                'vouchers_used' => LiveVoucher::whereHas('liveOrders')->count(),
            ];

            if ($activeStream) {
                $stats['active_stream'] = [
                    'id' => $activeStream->id,
                    'title' => $activeStream->title,
                    'viewer_count' => $activeStream->viewer_count ?? 0,
                    'start_time' => $activeStream->start_time?->toISOString(),
                    'duration' => $activeStream->duration ?? 0,
                    'orders_count' => $activeStream->orders()->count(),
                    'revenue' => $activeStream->orders()->sum('total_amount') ?? 0,
                ];
            }

            // Monthly stats (last 6 months)
            $monthlyStats = LiveStream::where('created_at', '>=', now()->subMonths(6))
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as streams_count'),
                    DB::raw('COALESCE(SUM(viewer_count), 0) as total_viewers')
                )
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'month' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                        'streams_count' => $item->streams_count,
                        'total_viewers' => $item->total_viewers,
                    ];
                });

            $stats['monthly_stats'] = $monthlyStats;

            return response()->json([
                'data' => $stats,
                'message' => 'Streaming stats retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve streaming stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function start(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            // Check if there's already an active stream
            if (LiveStream::where('status', 'active')->exists()) {
                return response()->json([
                    'message' => 'There is already an active live stream'
                ], 400);
            }

            $stream = LiveStream::create([
                'title' => $request->title,
                'description' => $request->description,
                'status' => 'active',
                'start_time' => now(),
                'viewer_count' => 0,
            ]);

            return response()->json([
                'data' => [
                    'id' => $stream->id,
                    'title' => $stream->title,
                    'status' => $stream->status,
                    'start_time' => $stream->start_time->toISOString(),
                ],
                'message' => 'Live stream started successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to start live stream',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function end()
    {
        try {
            $stream = LiveStream::where('status', 'active')->first();

            if (!$stream) {
                return response()->json([
                    'message' => 'No active live stream found'
                ], 404);
            }

            $stream->update([
                'status' => 'ended',
                'end_time' => now(),
            ]);

            return response()->json([
                'data' => [
                    'id' => $stream->id,
                    'title' => $stream->title,
                    'status' => $stream->status,
                    'end_time' => $stream->end_time->toISOString(),
                    'duration' => $stream->start_time->diffInMinutes($stream->end_time),
                ],
                'message' => 'Live stream ended successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to end live stream',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function active()
    {
        try {
            $stream = LiveStream::with(['products', 'vouchers'])
                ->where('status', 'active')
                ->first();

            if (!$stream) {
                return response()->json([
                    'message' => 'No active live stream found'
                ], 404);
            }

            return response()->json([
                'data' => [
                    'id' => $stream->id,
                    'title' => $stream->title,
                    'description' => $stream->description,
                    'status' => $stream->status,
                    'start_time' => $stream->start_time->toISOString(),
                    'viewer_count' => $stream->viewer_count,
                    'products' => $stream->products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'stock' => $product->stock,
                        ];
                    }),
                    'vouchers' => $stream->vouchers->map(function ($voucher) {
                        return [
                            'id' => $voucher->id,
                            'code' => $voucher->code,
                            'discount_type' => $voucher->discount_type,
                            'discount_value' => $voucher->discount_value,
                            'used_count' => $voucher->liveOrders()->count(),
                        ];
                    }),
                ],
                'message' => 'Active live stream retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve active live stream',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function orders()
    {
        try {
            $orders = LiveOrder::with(['buyer', 'order', 'voucher'])
                ->latest()
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'user_name' => $order->buyer?->name ?? 'Guest',
                        'total_amount' => $order->total_amount,
                        'discount_amount' => $order->discount_amount,
                        'final_amount' => $order->getFinalAmount(),
                        'voucher_code' => $order->voucher?->code ?? null,
                        'order_details' => $order->order_details,
                        'created_at' => $order->created_at->toISOString(),
                    ];
                });

            return response()->json([
                'data' => $orders,
                'message' => 'Live orders retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve live orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function confirmLiveOrder($orderId)
    {
        try {
            $liveOrder = LiveOrder::findOrFail($orderId);
            
            // Update the related order status if it exists
            if ($liveOrder->order) {
                $liveOrder->order->update(['status' => 'confirmed']);
            }

            return response()->json([
                'data' => $liveOrder->fresh(['buyer', 'order', 'voucher']),
                'message' => 'Live order confirmed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to confirm live order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateLiveOrderStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|string|in:pending,confirmed,cancelled,shipped,delivered'
        ]);

        try {
            $liveOrder = LiveOrder::findOrFail($orderId);
            
            // Update the related order status if it exists
            if ($liveOrder->order) {
                $liveOrder->order->update(['status' => $request->status]);
            }

            return response()->json([
                'data' => $liveOrder->fresh(['buyer', 'order', 'voucher']),
                'message' => 'Live order status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update live order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function vouchers()
    {
        try {
            $vouchers = LiveVoucher::with('liveStream')->latest()->get()->map(function ($voucher) {
                return [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'discount_type' => $voucher->discount_type,
                    'discount_value' => $voucher->discount_value,
                    'description' => $voucher->description,
                    'start_time' => $voucher->start_time?->toISOString(),
                    'end_time' => $voucher->end_time?->toISOString(),
                    'active' => $voucher->active,
                    'is_valid' => $voucher->isValid(),
                    'used_count' => $voucher->liveOrders()->count(),
                    'live_stream' => $voucher->liveStream ? [
                        'id' => $voucher->liveStream->id,
                        'title' => $voucher->liveStream->title,
                    ] : null
                ];
            });

            return response()->json([
                'data' => $vouchers,
                'message' => 'Live vouchers retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve live vouchers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function comments(Request $request)
    {
        try {
            $streamId = $request->get('stream_id');
            $query = \App\Models\LiveComment::with('user');

            if ($streamId) {
                $query->where('live_stream_id', $streamId);
            }

            $comments = $query->latest()->get()->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'user_name' => $comment->user?->name ?? 'Guest',
                    'comment' => $comment->content,
                    'created_at' => $comment->created_at->toISOString(),
                ];
            });

            return response()->json([
                'data' => $comments,
                'message' => 'Live comments retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve live comments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteComment($commentId)
    {
        try {
            $comment = \App\Models\LiveComment::findOrFail($commentId);
            $comment->delete();

            return response()->json([
                'message' => 'Live comment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete live comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStreamToken(Request $request)
    {
        $request->validate([
            'stream_title' => 'required|string'
        ]);

        try {
            // In a real implementation, you would generate a proper ZEGOCLOUD token
            // For now, we'll return a mock token
            $token = 'mock_token_' . time();

            return response()->json([
                'data' => [
                    'token' => $token,
                    'stream_title' => $request->stream_title,
                ],
                'message' => 'Stream token generated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate stream token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pinProduct(Request $request)
    {
        $request->validate([
            'live_stream_id' => 'required|exists:live_streams,id',
            'product_id' => 'required|exists:products,id'
        ]);

        try {
            $stream = LiveStream::findOrFail($request->live_stream_id);
            $stream->products()->sync([$request->product_id]);

            return response()->json([
                'message' => 'Product pinned to stream successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to pin product to stream',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function unpinProduct(Request $request)
    {
        $request->validate([
            'live_stream_id' => 'required|exists:live_streams,id'
        ]);

        try {
            $stream = LiveStream::findOrFail($request->live_stream_id);
            $stream->products()->detach();

            return response()->json([
                'message' => 'Product unpinned from stream successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to unpin product from stream',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function saveAnalytics(Request $request)
    {
        $request->validate([
            'live_stream_id' => 'required|exists:live_streams,id',
            'total_comments' => 'required|integer|min:0',
            'active_users' => 'required|integer|min:0'
        ]);

        try {
            \App\Models\LiveAnalytics::create([
                'live_stream_id' => $request->live_stream_id,
                'total_comments' => $request->total_comments,
                'active_users' => $request->active_users,
                'recorded_at' => now(),
            ]);

            return response()->json([
                'message' => 'Stream analytics saved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save stream analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:live_vouchers,code',
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'required|numeric|min:0',
            'live_stream_id' => 'required|exists:live_streams,id',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        try {
            $voucher = LiveVoucher::create([
                'code' => strtoupper($request->code),
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'live_stream_id' => $request->live_stream_id,
                'description' => $request->description,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'active' => true
            ]);

            return response()->json([
                'data' => $voucher->fresh(['liveStream']),
                'message' => 'Voucher created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create voucher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateVoucher(Request $request, $id)
    {
        $voucher = LiveVoucher::findOrFail($id);

        $request->validate([
            'code' => 'required|string|unique:live_vouchers,code,' . $id,
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'required|numeric|min:0',
            'live_stream_id' => 'required|exists:live_streams,id',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'active' => 'boolean'
        ]);

        try {
            $voucher->update([
                'code' => strtoupper($request->code),
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'live_stream_id' => $request->live_stream_id,
                'description' => $request->description,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'active' => $request->active ?? $voucher->active
            ]);

            return response()->json([
                'data' => $voucher->fresh(['liveStream']),
                'message' => 'Voucher updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update voucher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteVoucher($id)
    {
        try {
            $voucher = LiveVoucher::findOrFail($id);

            if ($voucher->liveOrders()->exists()) {
                return response()->json([
                    'message' => 'Cannot delete voucher that has been used in orders'
                ], 400);
            }

            $voucher->delete();

            return response()->json([
                'message' => 'Voucher deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete voucher',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $stream = LiveStream::with(['products', 'vouchers'])->findOrFail($id);

            return response()->json([
                'data' => [
                    'id' => $stream->id,
                    'title' => $stream->title,
                    'description' => $stream->description,
                    'status' => $stream->status,
                    'start_time' => $stream->start_time?->toISOString(),
                    'end_time' => $stream->end_time?->toISOString(),
                    'viewer_count' => $stream->viewer_count,
                    'products' => $stream->products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'stock' => $product->stock,
                        ];
                    }),
                    'vouchers' => $stream->vouchers->map(function ($voucher) {
                        return [
                            'id' => $voucher->id,
                            'code' => $voucher->code,
                            'discount_type' => $voucher->discount_type,
                            'discount_value' => $voucher->discount_value,
                            'used_count' => $voucher->liveOrders()->count(),
                        ];
                    }),
                ],
                'message' => 'Live stream retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve live stream',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $stream = LiveStream::findOrFail($id);
            
            $stream->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            return response()->json([
                'data' => [
                    'id' => $stream->id,
                    'title' => $stream->title,
                    'description' => $stream->description,
                    'status' => $stream->status,
                    'start_time' => $stream->start_time?->toISOString(),
                    'end_time' => $stream->end_time?->toISOString(),
                    'viewer_count' => $stream->viewer_count,
                ],
                'message' => 'Live stream updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update live stream',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $stream = LiveStream::findOrFail($id);
            
            // Check if stream is currently active
            if ($stream->status === 'active') {
                return response()->json([
                    'message' => 'Cannot delete an active live stream'
                ], 400);
            }
            
            $stream->delete();

            return response()->json([
                'message' => 'Live stream deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete live stream',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        try {
            $activeStream = LiveStream::where('status', 'active')->first();
            
            if (!$activeStream) {
                return response()->json([
                    'message' => 'No active live stream found'
                ], 404);
            }

            // In a real implementation, you would broadcast this message via WebSocket
            // For now, we'll just store it as a comment
            \App\Models\LiveComment::create([
                'live_stream_id' => $activeStream->id,
                'user_id' => $request->user()->id ?? null,
                'content' => $request->message,
            ]);

            return response()->json([
                'message' => 'Message sent successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
