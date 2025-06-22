<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function index()
    {
        $orderItems = OrderItem::with(['order', 'product'])->latest()->paginate(10);
        return view('admin.order_items.index', compact('orderItems'));
    }

    public function create()
    {
        return view('admin.order_items.create'); // Form untuk menambah order item
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        $orderItem = OrderItem::create($request->all());

        return redirect()->route('admin.order_items.index')->with('success', 'Order item created successfully.');
    }

    public function edit(OrderItem $orderItem)
    {
        return view('admin.order_items.edit', compact('orderItem')); // Form untuk mengedit order item
    }

    public function update(Request $request, OrderItem $orderItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $orderItem->update($request->all());

        return redirect()->route('admin.order_items.index')->with('success', 'Order item updated successfully.');
    }

    public function destroy(OrderItem $orderItem)
    {
        $orderItem->delete();

        return redirect()->route('admin.order_items.index')->with('success', 'Order item deleted successfully.');
    }
}