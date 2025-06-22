<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of product categories
     */
    public function index(Request $request)
    {
        try {
            $query = ProductCategory::withCount('products');

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }

            $categories = $query->orderBy('created_at', 'desc')->get();

            $formattedCategories = $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'icon' => $category->icon ? asset('storage/' . $category->icon) : null,
                    'is_active' => $category->is_active,
                    'products_count' => $category->products_count,
                    'created_at' => $category->created_at->toISOString(),
                    'updated_at' => $category->updated_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedCategories,
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

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:product_categories,name',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
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
            $data['slug'] = Str::slug($request->name);

            // Handle icon upload
            if ($request->hasFile('icon')) {
                $iconPath = $request->file('icon')->store('categories', 'public');
                $data['icon'] = $iconPath;
            }

            $category = ProductCategory::create($data);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'icon' => $category->icon ? asset('storage/' . $category->icon) : null,
                    'is_active' => $category->is_active,
                    'products_count' => 0,
                ],
                'message' => 'Category created successfully'
            ], 201);

        } catch (\Exception $e) {
            // Clean up uploaded file if category creation fails
            if (isset($data['icon'])) {
                Storage::disk('public')->delete($data['icon']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified category
     */
    public function show(ProductCategory $category)
    {
        try {
            $category->loadCount('products');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'icon' => $category->icon ? asset('storage/' . $category->icon) : null,
                    'is_active' => $category->is_active,
                    'products_count' => $category->products_count,
                    'created_at' => $category->created_at->toISOString(),
                    'updated_at' => $category->updated_at->toISOString(),
                ],
                'message' => 'Category retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, ProductCategory $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:product_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'boolean',
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
            $data['slug'] = Str::slug($request->name);

            // Handle icon upload
            if ($request->hasFile('icon')) {
                // Delete old icon if exists
                if ($category->icon) {
                    Storage::disk('public')->delete($category->icon);
                }
                
                $iconPath = $request->file('icon')->store('categories', 'public');
                $data['icon'] = $iconPath;
            }

            $category->update($data);
            $category->loadCount('products');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'icon' => $category->icon ? asset('storage/' . $category->icon) : null,
                    'is_active' => $category->is_active,
                    'products_count' => $category->products_count,
                ],
                'message' => 'Category updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy(ProductCategory $category)
    {
        try {
            // Check if category has products
            if ($category->products()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category that has products associated with it'
                ], 422);
            }

            // Delete icon if exists
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(ProductCategory $category)
    {
        try {
            $category->update(['is_active' => !$category->is_active]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'is_active' => $category->is_active,
                ],
                'message' => $category->is_active ? 'Category activated successfully' : 'Category deactivated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle category status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
