<?php

use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);

    Route::get('/warehouses', [WarehouseController::class, 'index']);
    Route::get('/warehouses/{warehouse}/stock', [WarehouseController::class, 'stock']);

    Route::post('/inventory/receive', [InventoryController::class, 'receive']);
    Route::post('/inventory/transfer', [InventoryController::class, 'transfer']);
    Route::get('/inventory/movements', [InventoryController::class, 'movements']);
});
