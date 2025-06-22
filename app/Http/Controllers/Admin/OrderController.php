<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Search by order ID or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.product', 'paymentProof']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Order::getStatusList()))
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $order->status;
            $newStatus = $request->status;

            $order->update(['status' => $newStatus]);

            // Send notification to user
            if ($order->fcm_token) {
                $this->fcmService->sendNotification(
                    $order->fcm_token,
                    $this->fcmService->getStatusIcon($newStatus),
                    $this->fcmService->getStatusMessage($order->id, $newStatus),
                    ['order_id' => $order->id]
                );
            }

            DB::commit();

            return back()->with('success', 'Status pesanan berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui status pesanan');
        }
    }

    public function verifyPayment(Order $order)
    {
        try {
            DB::beginTransaction();

            if (!$order->paymentProof) {
                throw new \Exception('Bukti pembayaran tidak ditemukan');
            }

            $order->paymentProof->verify(auth()->user());
            
            DB::commit();

            return back()->with('success', 'Pembayaran berhasil diverifikasi');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memverifikasi pembayaran');
        }
    }

    public function rejectPayment(Request $request, Order $order)
    {
        $request->validate([
            'notes' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            if (!$order->paymentProof) {
                throw new \Exception('Bukti pembayaran tidak ditemukan');
            }

            $order->paymentProof->reject(auth()->user(), $request->notes);

            DB::commit();

            return back()->with('success', 'Pembayaran berhasil ditolak');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak pembayaran');
        }
    }

    public function uploadShippingProof(Request $request, Order $order)
    {
        $request->validate([
            'tracking_number' => 'required|string|max:50',
            'shipping_courier' => 'required|string|max:50',
            'shipping_proof' => 'nullable|image|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $data = $request->only(['tracking_number', 'shipping_courier']);

            // Handle shipping proof upload
            if ($request->hasFile('shipping_proof')) {
                // Delete old file if exists
                if ($order->shipping_proof_path) {
                    Storage::disk('public')->delete($order->shipping_proof_path);
                }

                $data['shipping_proof_path'] = $request->file('shipping_proof')
                    ->store('shipping_proofs', 'public');
            }

            $order->update($data);

            // Update status to "Dikirim" if not already
            if ($order->status === 'Diproses') {
                $order->update(['status' => 'Dikirim']);

                // Send notification to user
                if ($order->fcm_token) {
                    $this->fcmService->sendNotification(
                        $order->fcm_token,
                        "🚚",
                        "Pesanan #{$order->id} sedang dalam pengiriman\nNo. Resi: {$order->tracking_number}",
                        [
                            'order_id' => $order->id,
                            'tracking_number' => $order->tracking_number,
                            'shipping_courier' => $order->shipping_courier
                        ]
                    );
                }
            }

            DB::commit();

            return back()->with('success', 'Informasi pengiriman berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui informasi pengiriman');
        }
    }

    public function destroy(Order $order)
    {
        try {
            DB::beginTransaction();

            // Delete payment proof if exists
            if ($order->paymentProof) {
                Storage::disk('public')->delete($order->paymentProof->path);
                $order->paymentProof->delete();
            }

            // Delete shipping proof if exists
            if ($order->shipping_proof_path) {
                Storage::disk('public')->delete($order->shipping_proof_path);
            }

            // Restore product stock
            foreach ($order->orderItems as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            $order->delete();

            DB::commit();

            return redirect()
                ->route('admin.orders.index')
                ->with('success', 'Pesanan berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus pesanan');
        }
    }
}
