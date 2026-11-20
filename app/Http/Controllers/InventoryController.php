<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $warehouseId = $request->query('warehouse_id');
        $status = $request->query('status');

        $stocks = WarehouseStock::query()
            ->with([
                'product:id,category_id,sku,barcode,name,unit,cost_price,unit_price',
                'product.category:id,name',
                'warehouse:id,code,name',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%");
                    });
                });
            })
            ->when($warehouseId, fn ($query) =>
                $query->where('warehouse_id', $warehouseId)
            )
            ->when($status === 'low', fn ($query) =>
                $query->whereColumn('quantity', '<=', 'reorder_level')
                    ->where('quantity', '>', 0)
            )
            ->when($status === 'out', fn ($query) =>
                $query->where('quantity', 0)
            )
            ->when($status === 'healthy', fn ($query) =>
                $query->whereColumn('quantity', '>', 'reorder_level')
            )
            ->join('products', 'warehouse_stocks.product_id', '=', 'products.id')
            ->select('warehouse_stocks.*')
            ->orderBy('products.name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'products' => Product::count(),

            'total_stock' => WarehouseStock::sum('quantity'),

            'low_stock' => WarehouseStock::whereColumn(
                'quantity',
                '<=',
                'reorder_level'
            )->where('quantity', '>', 0)->count(),

            'out_of_stock' => WarehouseStock::where('quantity', 0)->count(),

            'stock_value' => DB::table('warehouse_stocks')
                ->join('products', 'warehouse_stocks.product_id', '=', 'products.id')
                ->sum(DB::raw('warehouse_stocks.quantity * products.cost_price')),
        ];

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('inventory.index', compact(
            'stocks',
            'stats',
            'warehouses',
            'search',
            'warehouseId',
            'status'
        ));
    }
}
