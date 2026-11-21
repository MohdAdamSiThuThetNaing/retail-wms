<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Product catalog with aggregated inventory quantities.
        $query = Product::with('category')
            ->withSum('stocks', 'quantity')
            ->withSum('stocks', 'reorder_level');

        // Search products.
        if ($request->filled('q')) {
            $search = $request->q;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Filter by category.
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by product status.
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        $products = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Active product categories.
        $categories = ProductCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Inventory statistics across ALL products and warehouses.
        $inventoryProducts = Product::query()
            ->withSum('stocks', 'quantity')
            ->withSum('stocks', 'reorder_level')
            ->get();

        $stats = [
            'total' => Product::count(),

            'active' => Product::where('is_active', true)->count(),

            'inactive' => Product::where('is_active', false)->count(),

            'total_units' => (int) $inventoryProducts->sum(
                fn ($product) => (int) ($product->stocks_sum_quantity ?? 0)
            ),

            'low_stock' => $inventoryProducts->filter(
                function ($product) {
                    $quantity = (int) ($product->stocks_sum_quantity ?? 0);
                    $reorderLevel = (int) ($product->stocks_sum_reorder_level ?? 0);

                    return $quantity > 0
                        && $quantity <= $reorderLevel;
                }
            )->count(),

            'out_of_stock' => $inventoryProducts->filter(
                fn ($product) =>
                    (int) ($product->stocks_sum_quantity ?? 0) <= 0
            )->count(),
        ];

        return view('products.index', compact(
            'products',
            'categories',
            'stats'
        ));
    }

    public function create()
    {
        $categories = ProductCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'sku' => ['required', 'string', 'max:64', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', 'string', 'max:30'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')
                ->store('products', 'public');
        }

        unset($data['image']);

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('category', 'stocks.warehouse');

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = ProductCategory::orderBy('name')->get();

        return view('products.edit', compact(
            'product',
            'categories'
        ));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'sku' => [
                'required',
                'string',
                'max:64',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')->ignore($product->id),
            ],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', 'string', 'max:30'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')
                ->store('products', 'public');
        }

        unset($data['image']);

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if (
            $product->stocks()->exists() ||
            $product->movements()->exists()
        ) {
            return back()->with(
                'error',
                'This product has inventory records. Deactivate it instead of deleting it.'
            );
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
