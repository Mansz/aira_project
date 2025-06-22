<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment; // Model yang sesuai
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::all(); // Mengambil semua data pembayaran
        return view('admin.payments.index', compact('payments'));
    }

    public function store(Request $request)
    {
        // Validasi data dan simpan pembayaran
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0', // Menambahkan validasi minimum
            'status' => 'required|string|max:255', // Menambahkan validasi maksimum
            // Tambahkan validasi lainnya sesuai kebutuhan
        ]);

        Payment::create($validated);

        return redirect()->route('admin.payments.index')->with('success', 'Payment added successfully.');
    }

    public function edit(Payment $payment)
    {
        return view('admin.payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        // Validasi dan update data pembayaran
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0', // Menambahkan validasi minimum
            'status' => 'required|string|max:255', // Menambahkan validasi maksimum
        ]);

        $payment->update($validated);

        return redirect()->route('admin.payments.index')->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('admin.payments.index')->with('success', 'Payment deleted successfully.');
    }
}