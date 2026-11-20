<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->when($request->string('q')->isNotEmpty(), function ($query) use ($request) {
                $term = '%' . addcslashes($request->string('q')->toString(), '%_\\\\') . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('sku', 'like', $term)->orWhere('name', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 20), 100));

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:64', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        return response()->json(Product::create($validated), 201);
    }
}
