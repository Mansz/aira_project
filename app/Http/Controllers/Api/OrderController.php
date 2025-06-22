<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentProof;
use App\Models\Product;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function userOrders(Request $request)
    {
        $orders = Order::with(['orderItems.product'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'total_price' => $order->total_price,
                    'status' => $order->status,
                    'status_icon' => $order->status_icon,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'tracking_number' => $order->tracking_number,
                    'shipping_courier' => $order->shipping_courier,
                    'items' => $order->orderItems->map(function ($item) {
                        return [
                            'product_name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'image_url' => $item->product->image_url
                        ];
                    })
                ];
            });

        return response()->json(['orders' => $orders]);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->load(['orderItems.product', 'paymentProof']);

        return response()->json([
            'order' => [
                'id' => $order->id,
                'total_price' => $order->total_price,
                'shipping_address' => $order->shipping_address,
                'payment_method' => $order->payment_method,
                'status' => $order->status,
                'status_icon' => $order->status_icon,
                'tracking_number' => $order->tracking_number,
                'shipping_courier' => $order->shipping_courier,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'image_url' => $item->product->image_url,
                        'notes' => $item->notes
                    ];
                }),
                'payment_proof' => $order->paymentProof ? [
                    'image_url' => $order->paymentProof->image_url,
                    'verified_at' => $order->paymentProof->verified_at?->format('Y-m-d H:i:s')
                ] : null
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'fcm_token' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $totalPrice = 0;
            $items = [];

            // Validate stock and calculate total price
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'message' => "Stok tidak cukup untuk produk {$product->name}"
                    ], 422);
                }

                $totalPrice += $product->price * $item['quantity'];
                $items[] = [
                    'product' => $product,
                    'quantity' => $item['quantity']
                ];
            }

            // Create order
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_price' => $totalPrice,
                'shipping_address' => $request->shipping_address,
                'payment_method' => $request->payment_method,
                'status' => 'Menunggu Pembayaran',
                'fcm_token' => $request->fcm_token
            ]);

            // Create order items and update stock
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['product']->price,
                    'notes' => $item['notes'] ?? null
                ]);

                $item['product']->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'order' => [
                    'id' => $order->id,
                    'total_price' => $order->total_price,
                    'status' => $order->status
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat pesanan'], 500);
        }
    }

    public function uploadPaymentProof(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'proof' => 'required|image|max:2048'
        ]);

        try {
            DB::beginTransaction();

            if ($order->paymentProof) {
                Storage::disk('public')->delete($order->paymentProof->path);
                $order->paymentProof->delete();
            }

            $path = $request->file('proof')->store('payment_proofs', 'public');

            PaymentProof::create([
                'order_id' => $order->id,
                'path' => $path
            ]);

            $order->update([
                'status' => 'Menunggu Konfirmasi'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Bukti pembayaran berhasil diunggah',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengunggah bukti pembayaran'], 500);
        }
    }

    public function updateFCMToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string'
        ]);

        try {
            Order::where('user_id', Auth::id())
                ->whereNotNull('fcm_token')
                ->update(['fcm_token' => $request->fcm_token]);

            return response()->json(['message' => 'Token FCM berhasil diperbarui']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui token FCM'], 500);
        }
    }

    public function complete(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $order->complete();
            return response()->json([
                'message' => 'Pesanan berhasil diselesaikan',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function submitComplaint(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'description' => 'required|string',
            'photo' => 'required|image|max:5120', // max 5MB
        ]);

        if ($order->status !== 'Dikirim' && $order->status !== 'Selesai') {
            return response()->json([
                'message' => 'Komplain hanya dapat diajukan untuk pesanan yang sudah dikirim atau selesai'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $path = $request->file('photo')->store('complaints', 'public');

            $complaint = $order->complaints()->create([
                'description' => $request->description,
                'photo_path' => $path,
                'status' => 'Pending'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Komplain berhasil diajukan',
                'complaint' => [
                    'id' => $complaint->id,
                    'description' => $complaint->description,
                    'photo_url' => $complaint->photo_url,
                    'status' => $complaint->status,
                    'created_at' => $complaint->created_at->format('Y-m-d H:i:s')
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengajukan komplain'
            ], 500);
        }
    }

    public function trackShipment(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$order->tracking_number || !$order->shipping_courier) {
            return response()->json([
                'message' => 'Informasi pengiriman belum tersedia'
            ], 404);
        }

        // Here you would integrate with shipping courier APIs
        // For now, return mock tracking data
        return response()->json([
            'tracking' => [
                'courier' => $order->shipping_courier,
                'tracking_number' => $order->tracking_number,
                'status' => $order->status,
                'estimated_delivery' => now()->addDays(3)->format('Y-m-d'),
                'history' => [
                    [
                        'date' => now()->subDays(1)->format('Y-m-d H:i:s'),
                        'description' => 'Paket dalam perjalanan',
                        'location' => 'Jakarta'
                    ],
                    [
                        'date' => now()->subDays(2)->format('Y-m-d H:i:s'),
                        'description' => 'Paket telah dipickup oleh kurir',
                        'location' => 'Bandung'
                    ]
                ]
            ]
        ]);
    }

    public function cancel(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($order->status, ['Menunggu Pembayaran', 'Menunggu Konfirmasi'])) {
            return response()->json([
                'message' => 'Pesanan tidak dapat dibatalkan'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Restore product stock
            foreach ($order->orderItems as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            // Delete payment proof if exists
            if ($order->paymentProof) {
                Storage::disk('public')->delete($order->paymentProof->path);
                $order->paymentProof->delete();
            }

            $order->update(['status' => 'Dibatalkan']);

            DB::commit();

            return response()->json([
                'message' => 'Pesanan berhasil dibatalkan',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membatalkan pesanan'], 500);
        }
    }
}
