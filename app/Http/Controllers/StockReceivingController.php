<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\NotificationService;
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
        InventoryService $inventory,
        NotificationService $notifications
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

        $quantity = (int) $validated['quantity'];
        $actorId = $request->user()->id;

        // InventoryService commits the stock transaction and
        // broadcasts InventoryUpdated after the transaction.
        $inventory->receive(
            warehouse: $warehouse,
            product: $product,
            quantity: $quantity,
            actorId: $actorId,
            reference: $validated['reference'] ?? null,
        );

        // Notify active inventory users after successful receiving.
        $notifications->notifyPermissions(
            permissions: ['inventory.view'],
            category: 'inventory',
            title: 'Stock Received',
            message: "{$quantity} units of {$product->name} ({$product->sku}) received at {$warehouse->name}.",
            url: route('inventory.index'),
            metadata: [
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->name,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $quantity,
                'reference' => $validated['reference'] ?? null,
                'action' => 'stock_received',
            ],
            exceptUserId: $actorId
        );

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Stock received successfully.');
    }
}
