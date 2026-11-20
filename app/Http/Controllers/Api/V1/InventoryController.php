<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReceiveStockRequest;
use App\Http\Requests\TransferStockRequest;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function receive(ReceiveStockRequest $request)
    {
        $data = $request->validated();
        $stock = $this->inventory->receive(
            Warehouse::findOrFail($data['warehouse_id']),
            Product::findOrFail($data['product_id']),
            $data['quantity'],
            $request->user()->id,
            $data['reference'] ?? null
        );

        return response()->json(['data' => $stock], 201);
    }

    public function transfer(TransferStockRequest $request)
    {
        $data = $request->validated();
        $this->inventory->transfer(
            Warehouse::findOrFail($data['from_warehouse_id']),
            Warehouse::findOrFail($data['to_warehouse_id']),
            Product::findOrFail($data['product_id']),
            $data['quantity'],
            $request->user()->id,
            $data['reference'] ?? null
        );

        return response()->json(['message' => 'Stock transferred successfully.']);
    }

    public function movements(Request $request)
    {
        $movements = InventoryMovement::query()
            ->with(['product:id,sku,name', 'warehouse:id,code,name', 'creator:id,name'])
            ->when($request->integer('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->integer('warehouse_id'), fn ($q, $id) => $q->where('warehouse_id', $id))
            ->latest('created_at')
            ->paginate(min($request->integer('per_page', 25), 100));

        return response()->json($movements);
    }
}
