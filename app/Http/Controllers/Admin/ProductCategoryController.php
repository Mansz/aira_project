<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::withCount('products')
            ->latest()
            ->paginate(10);
            
        return view('admin.products.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.products.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:1024'
        ]);

        try {
            $data = $request->except('icon');
            $data['slug'] = Str::slug($request->name);

            if ($request->hasFile('icon')) {
                $data['icon'] = $request->file('icon')->store('categories', 'public');
            }

            ProductCategory::create($data);

            return redirect()
                ->route('admin.products.categories.index')
                ->with('success', 'Kategori produk berhasil dibuat.');

        } catch (\Exception $e) {
            if (isset($data['icon'])) {
                Storage::disk('public')->delete($data['icon']);
            }

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat kategori: ' . $e->getMessage());
        }
    }

    public function edit(ProductCategory $category)
    {
        return view('admin.products.categories.edit', compact('category'));
    }

    public function update(Request $request, ProductCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:product_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:1024'
        ]);

        try {
            $data = $request->except('icon');
            $data['slug'] = Str::slug($request->name);

            if ($request->hasFile('icon')) {
                if ($category->icon) {
                    Storage::disk('public')->delete($category->icon);
                }
                $data['icon'] = $request->file('icon')->store('categories', 'public');
            }

            $category->update($data);

            return redirect()
                ->route('admin.products.categories.index')
                ->with('success', 'Kategori produk berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui kategori: ' . $e->getMessage());
        }
    }

    public function destroy(ProductCategory $category)
    {
        try {
            if ($category->products()->exists()) {
                throw new \Exception('Kategori masih memiliki produk terkait.');
            }

            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }

            $category->delete();

            return redirect()
                ->route('admin.products.categories.index')
                ->with('success', 'Kategori produk berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    public function toggleStatus(ProductCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        return back()->with('success', 
            $category->is_active ? 'Kategori berhasil diaktifkan.' : 'Kategori berhasil dinonaktifkan.'
        );
    }
}
