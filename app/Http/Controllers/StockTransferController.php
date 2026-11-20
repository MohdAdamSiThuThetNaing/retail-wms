<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferStockRequest;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
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
        InventoryService $inventory
    ): RedirectResponse {
        $data = $request->validated();

        $inventory->transfer(
            Warehouse::findOrFail($data['from_warehouse_id']),
            Warehouse::findOrFail($data['to_warehouse_id']),
            Product::findOrFail($data['product_id']),
            (int) $data['quantity'],
            $request->user()->id,
            $data['reference'] ?? null
        );

        return redirect()
            ->route('stock-transfers.create')
            ->with('success', 'Stock transferred successfully.');
    }
}
