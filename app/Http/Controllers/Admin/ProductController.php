<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:20',
            'sku' => 'required|string|unique:products,sku',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            // Handle image upload
            $imagePath = $request->file('image')->store('products', 'public');

            // Create product
            Product::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'weight' => $request->weight,
                'color' => $request->color,
                'size' => $request->size,
                'sku' => $request->sku,
                'image' => $imagePath
            ]);

            // Redirect to index with success message
            return redirect()->route('admin.products.index')
                ->with('success', 'Produk berhasil ditambahkan.');

        } catch (\Exception $e) {
            // Delete uploaded image if creation fails
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            // Redirect back with error message
            return back()->with('error', 'Gagal menambahkan produk: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:20',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            $data = $request->except('image');
            $data['slug'] = Str::slug($request->name);

            // Handle image upload if new image is provided
            if ($request->hasFile('image')) {
                // Delete old image
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                // Store new image
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update product: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Product $product)
{
    try {
        // Delete product image
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');

    } catch (\Exception $e) {
        return back()->with('error', 'Failed to delete product: ' . $e->getMessage());
    }
}

    public function updateStock(Request $request, Product $product)
    {
        $request->validate([
            'stock' => 'required|integer|min:0'
        ]);

        try {
            $product->update(['stock' => $request->stock]);

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'new_stock' => $product->stock
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock: ' . $e->getMessage()
            ], 500);
        }
    }
}