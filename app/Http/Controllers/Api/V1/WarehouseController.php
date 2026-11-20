<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;

class WarehouseController extends Controller
{
    public function index()
    {
        return response()->json(
            Warehouse::query()->where('is_active', true)->orderBy('name')->get()
        );
    }

    public function stock(Warehouse $warehouse)
    {
        $stock = $warehouse->stocks()
            ->with('product:id,sku,name,unit_price')
            ->orderBy('product_id')
            ->paginate(50);

        return response()->json($stock);
    }
}
