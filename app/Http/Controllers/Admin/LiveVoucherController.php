<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveVoucher;
use App\Models\LiveStream;
use App\Http\Requests\LiveVoucherRequest;
use Illuminate\Support\Facades\DB;

class LiveVoucherController extends Controller
{
    public function index()
    {
        $vouchers = LiveVoucher::with('liveStream')
            ->latest()
            ->paginate(10);
            
        return view('admin.streaming.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        $liveStreams = LiveStream::where('status', 'scheduled')
            ->orWhere('status', 'active')
            ->get();
            
        return view('admin.streaming.vouchers.create', compact('liveStreams'));
    }

    public function store(LiveVoucherRequest $request)
    {
        try {
            DB::beginTransaction();

            LiveVoucher::create($request->validated() + ['active' => true]);

            DB::commit();

            return redirect()
                ->route('admin.streaming.vouchers.index')
                ->with('success', 'Voucher berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat voucher: ' . $e->getMessage());
        }
    }

    public function edit(LiveVoucher $voucher)
    {
        $liveStreams = LiveStream::where('status', 'scheduled')
            ->orWhere('status', 'active')
            ->get();
            
        return view('admin.streaming.vouchers.edit', compact('voucher', 'liveStreams'));
    }

    public function update(LiveVoucherRequest $request, LiveVoucher $voucher)
    {
        try {
            DB::beginTransaction();

            $voucher->update($request->validated());

            DB::commit();

            return redirect()
                ->route('admin.streaming.vouchers.index')
                ->with('success', 'Voucher berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui voucher: ' . $e->getMessage());
        }
    }

    public function toggleStatus(LiveVoucher $voucher)
    {
        $voucher->update(['active' => !$voucher->active]);

        return back()->with('success', 
            $voucher->active ? 'Voucher berhasil diaktifkan' : 'Voucher berhasil dinonaktifkan'
        );
    }

    public function destroy(LiveVoucher $voucher)
    {
        try {
            if ($voucher->liveOrders()->exists()) {
                throw new \Exception('Voucher tidak dapat dihapus karena sudah digunakan dalam pesanan');
            }

            $voucher->delete();

            return redirect()
                ->route('admin.streaming.vouchers.index')
                ->with('success', 'Voucher berhasil dihapus');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus voucher: ' . $e->getMessage());
        }
    }
}
