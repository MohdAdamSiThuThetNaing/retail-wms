<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class StockReceivingController extends Controller
{
    public function create(): View
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'sku', 'name', 'unit']);

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('stock-receiving.create', compact(
            'products',
            'warehouses'
        ));
    }

    public function store(
        Request $request,
        InventoryService $inventory
    ): RedirectResponse {
        $validated = $request->validate([
            'warehouse_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')
                    ->where('is_active', true),
            ],
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')
                    ->where('is_active', true),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);
        $product = Product::findOrFail($validated['product_id']);

        $inventory->receive(
            warehouse: $warehouse,
            product: $product,
            quantity: (int) $validated['quantity'],
            actorId: $request->user()->id,
            reference: $validated['reference'] ?? null,
        );

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Stock received successfully.');
    }
}
