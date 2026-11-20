<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'type' => ['nullable', 'in:receive,transfer_out,transfer_in'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $movements = InventoryMovement::query()
            ->with([
                'product:id,sku,name',
                'warehouse:id,code,name',
                'creator:id,name',
            ])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['product_id'] ?? null,
                fn ($query, $id) => $query->where('product_id', $id)
            )
            ->when($filters['warehouse_id'] ?? null,
                fn ($query, $id) => $query->where('warehouse_id', $id)
            )
            ->when($filters['type'] ?? null,
                fn ($query, $type) => $query->where('type', $type)
            )
            ->when($filters['start_date'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', '>=', $date)
            )
            ->when($filters['end_date'] ?? null,
                fn ($query, $date) => $query->whereDate('created_at', '<=', $date)
            )
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $products = Product::orderBy('name')
            ->get(['id', 'sku', 'name']);

        $warehouses = Warehouse::orderBy('name')
            ->get(['id', 'code', 'name']);

        $stats = [
            'total' => InventoryMovement::count(),
            'received' => InventoryMovement::where('type', 'receive')->sum('quantity'),
            'transferred_out' => InventoryMovement::where('type', 'transfer_out')->sum('quantity'),
            'transferred_in' => InventoryMovement::where('type', 'transfer_in')->sum('quantity'),
        ];

        return view('stock-movements.index', compact(
            'movements',
            'products',
            'warehouses',
            'filters',
            'stats'
        ));
    }
}
