<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // For simplicity, cart is stored in session. In production, use database or cache.

    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        return response()->json($cart);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $request->session()->get('cart', []);

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }

        $request->session()->put('cart', $cart);

        return response()->json(['message' => 'Product added to cart', 'cart' => $cart]);
    }
}
