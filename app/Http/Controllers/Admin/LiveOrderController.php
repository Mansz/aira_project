<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveOrder;
use App\Models\LiveStream;
use App\Models\LiveVoucher;
use Illuminate\Http\Request;

class LiveOrderController extends Controller
{
    public function index()
    {
        $orders = LiveOrder::with(['liveStream', 'buyer', 'voucher'])
            ->latest()
            ->paginate(10);

        return view('admin.streaming.orders.index', compact('orders'));
    }

    public function create()
{
    // Ambil data live streams, buyers, dan vouchers untuk ditampilkan di form
    $liveStreams = LiveStream::all(); // Ambil semua live streams
    $buyers = User::all(); // Ambil semua buyers
    $vouchers = LiveVoucher::all(); // Ambil semua vouchers

    return view('admin.streaming.orders.create', compact('liveStreams', 'buyers', 'vouchers'));
}

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
            'live_stream_id' => 'required|exists:live_streams,id',
            'buyer_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'voucher_id' => 'nullable|exists:live_vouchers,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'order_details' => 'required|json',
        ]);

        LiveOrder::create($request->all());

        return redirect()
            ->route('admin.streaming.orders.index')
            ->with('success', 'Order berhasil dibuat');
    }

    public function edit(LiveOrder $order)
{
    // Ambil data live streams, buyers, dan vouchers untuk ditampilkan di form edit
    $liveStreams = LiveStream::all(); // Ambil semua live streams
    $buyers = User::all(); // Ambil semua buyers
    $vouchers = LiveVoucher::all(); // Ambil semua vouchers

    return view('admin.streaming.orders.edit', compact('order', 'liveStreams', 'buyers', 'vouchers'));
}

    public function update(Request $request, LiveOrder $order)
    {
        $request->validate([
            'order_id' => 'required|string',
            'live_stream_id' => 'required|exists:live_streams,id',
            'buyer_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'voucher_id' => 'nullable|exists:live_vouchers,id',
            'discount_amount' => 'nullable|numeric|min:0',
            'order_details' => 'required|json',
        ]);

        $order->update($request->all());

        return redirect()
            ->route('admin.streaming.orders.index')
            ->with('success', 'Order berhasil diperbarui');
    }

    public function destroy(LiveOrder $order)
    {
        $order->delete();

        return redirect()
            ->route('admin.streaming.orders.index')
            ->with('success', 'Order berhasil dihapus');
    }
}