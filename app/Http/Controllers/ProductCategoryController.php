<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::withCount('products')
            ->orderBy('name')
            ->paginate(15);

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:product_categories,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        ProductCategory::create($data);

        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, ProductCategory $category)
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:120',
                Rule::unique('product_categories', 'name')->ignore($category->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $category->update($data);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(ProductCategory $category)
    {
        if ($category->products()->exists()) {
            return back()->with(
                'error',
                'This category contains products. Reassign those products before deleting it.'
            );
        }

        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}
