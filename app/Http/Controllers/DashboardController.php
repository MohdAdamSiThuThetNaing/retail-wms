<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\InventoryMovement;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'products' => Product::count(),
            'warehouses' => Warehouse::where('is_active', true)->count(),
            'total_stock' => WarehouseStock::sum('quantity'),
            'low_stock' => WarehouseStock::whereColumn(
                'quantity',
                '<=',
                'reorder_level'
            )->count(),
        ];

        $recentMovements = InventoryMovement::with([
            'product',
            'warehouse',
        ])
            ->latest('created_at')
            ->take(8)
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'recentMovements'
        ));
    }
}
