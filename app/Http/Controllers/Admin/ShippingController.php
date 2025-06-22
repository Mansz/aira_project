<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingAddress;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function index()
    {
        $shippingAddresses = ShippingAddress::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.shipping.index', compact('shippingAddresses'));
    }

    public function show(ShippingAddress $shippingAddress)
    {
        return view('admin.shipping.show', compact('shippingAddress'));
    }

    public function edit(ShippingAddress $shippingAddress)
    {
        return view('admin.shipping.edit', compact('shippingAddress'));
    }

    public function update(Request $request, ShippingAddress $shippingAddress)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'notes' => 'nullable|string',
        ]);

        $shippingAddress->update($validated);

        return redirect()
            ->route('admin.shipping.index')
            ->with('success', 'Shipping address updated successfully.');
    }

    public function destroy(ShippingAddress $shippingAddress)
    {
        $shippingAddress->delete();
        
        return redirect()
            ->route('admin.shipping.index')
            ->with('success', 'Shipping address deleted successfully.');
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'weight' => 'required|numeric',
            'distance' => 'required|numeric',
        ]);

        $cost = $this->calculateShippingCost($validated['weight'], $validated['distance']);

        return response()->json(['cost' => $cost]);
    }

    public function updateShipping(Request $request, $order)
    {
        $validated = $request->validate([
            'shipping_address_id' => 'required|exists:shipping_addresses,id',
        ]);

        // Logika untuk memperbarui pengiriman, misalnya:
        // $order = Order::findOrFail($order);
        // $order->shipping_address_id = $validated['shipping_address_id'];
        // $order->save();

        return redirect()->route('admin.shipping.index')->with('success', 'Shipping updated successfully.');
    }

    public function updateCosts(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'new_cost' => 'required|numeric',
        ]);

        // Logika untuk memperbarui biaya pengiriman di order
        // $order = Order::findOrFail($validated['order_id']);
        // $order->shipping_cost = $validated['new_cost'];
        // $order->save();

        return response()->json(['message' => 'Shipping cost updated successfully.']);
    }

    public function updateTracking(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'tracking_number' => 'required|string|max:255',
        ]);

        // Logika untuk memperbarui nomor pelacakan
        // $order = Order::findOrFail($validated['order_id']);
        // $order->tracking_number = $validated['tracking_number'];
        // $order->save();

        return response()->json(['message' => 'Tracking updated successfully.']);
    }

    private function calculateShippingCost($weight, $distance)
    {
        return $weight * $distance * 0.1; // Contoh logika perhitungan
    }
}