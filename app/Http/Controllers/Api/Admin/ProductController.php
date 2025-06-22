<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with('category');

            // Filter by category
            if ($request->filled('category')) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('name', $request->category);
                });
            }

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $products = $query->orderBy('created_at', 'desc')->get();

            $formattedProducts = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category ? $product->category->name : 'Uncategorized',
                    'price' => (float) $product->price,
                    'stock' => $product->stock,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'status' => $product->status,
                    'created_at' => $product->created_at->toISOString(),
                    'updated_at' => $product->updated_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedProducts,
                'message' => 'Products retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:product_categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = 'active';
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('products', $imageName, 'public');
                $data['image'] = $imagePath;
            }

            $product = Product::create($data);
            $product->load('category');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category ? $product->category->name : 'Uncategorized',
                    'category_id' => $product->category_id,
                    'price' => (float) $product->price,
                    'stock' => $product->stock,
                    'weight' => $product->weight,
                    'color' => $product->color,
                    'size' => $product->size,
                    'sku' => $product->sku,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'status' => $product->status,
                    'created_at' => $product->created_at->toISOString(),
                    'updated_at' => $product->updated_at->toISOString(),
                ],
                'message' => 'Product created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        try {
            $product->load('category');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category ? $product->category->name : 'Uncategorized',
                    'category_id' => $product->category_id,
                    'price' => (float) $product->price,
                    'stock' => $product->stock,
                    'weight' => $product->weight,
                    'color' => $product->color,
                    'size' => $product->size,
                    'sku' => $product->sku,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'status' => $product->status,
                    'created_at' => $product->created_at->toISOString(),
                    'updated_at' => $product->updated_at->toISOString(),
                ],
                'message' => 'Product retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:product_categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('products', $imageName, 'public');
                $data['image'] = $imagePath;
            }

            $product->update($data);
            $product->load('category');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category ? $product->category->name : 'Uncategorized',
                    'category_id' => $product->category_id,
                    'price' => (float) $product->price,
                    'stock' => $product->stock,
                    'weight' => $product->weight,
                    'color' => $product->color,
                    'size' => $product->size,
                    'sku' => $product->sku,
                    'image' => $product->image ? asset('storage/' . $product->image) : null,
                    'status' => $product->status,
                    'created_at' => $product->created_at->toISOString(),
                    'updated_at' => $product->updated_at->toISOString(),
                ],
                'message' => 'Product updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        try {
            // Check if product has related orders
            if ($product->orderItems()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product that has been ordered'
                ], 422);
            }

            // Delete image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product categories
     */
    public function categories()
    {
        try {
            $categories = \App\Models\ProductCategory::all(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Categories retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
