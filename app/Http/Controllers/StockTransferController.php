<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferStockRequest;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function create(): View
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'sku', 'name']);

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('stock-transfers.create', compact(
            'products',
            'warehouses'
        ));
    }

    public function store(
        TransferStockRequest $request,
        InventoryService $inventory,
        NotificationService $notifications
    ): RedirectResponse {
        $data = $request->validated();

        $fromWarehouse = Warehouse::findOrFail(
            $data['from_warehouse_id']
        );

        $toWarehouse = Warehouse::findOrFail(
            $data['to_warehouse_id']
        );

        $product = Product::findOrFail($data['product_id']);

        $quantity = (int) $data['quantity'];
        $actorId = $request->user()->id;
        $reference = $data['reference'] ?? null;

        // Perform the inventory transfer.
        // InventoryUpdated events are handled by InventoryService.
        $inventory->transfer(
            from: $fromWarehouse,
            to: $toWarehouse,
            product: $product,
            quantity: $quantity,
            actorId: $actorId,
            reference: $reference
        );

        // Notify active inventory users after successful transfer.
        $notifications->notifyPermissions(
            permissions: ['inventory.view'],
            category: 'inventory',
            title: 'Stock Transferred',
            message: "{$quantity} units of {$product->name} ({$product->sku}) transferred from {$fromWarehouse->name} to {$toWarehouse->name}.",
            url: route('inventory.index'),
            metadata: [
                'action' => 'stock_transferred',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $quantity,
                'from_warehouse_id' => $fromWarehouse->id,
                'from_warehouse_name' => $fromWarehouse->name,
                'to_warehouse_id' => $toWarehouse->id,
                'to_warehouse_name' => $toWarehouse->name,
                'reference' => $reference,
            ],
            exceptUserId: $actorId
        );

        return redirect()
            ->route('stock-transfers.create')
            ->with('success', 'Stock transferred successfully.');
    }
}
