<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseLocationController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

// Products
Route::resource('products', ProductController::class);

// Categories
Route::get('/categories', [ProductCategoryController::class, 'index'])
    ->name('categories.index');

Route::post('/categories', [ProductCategoryController::class, 'store'])
    ->name('categories.store');

Route::put('/categories/{category}', [ProductCategoryController::class, 'update'])
    ->name('categories.update');

Route::delete('/categories/{category}', [ProductCategoryController::class, 'destroy'])
    ->name('categories.destroy');

// Warehouses
Route::resource('warehouses', WarehouseController::class);

// Warehouse Locations
Route::get(
    '/warehouses/{warehouse}/locations',
    [WarehouseLocationController::class, 'index']
)->name('warehouses.locations.index');

Route::post(
    '/warehouses/{warehouse}/locations',
    [WarehouseLocationController::class, 'store']
)->name('warehouses.locations.store');

Route::put(
    '/warehouse-locations/{location}',
    [WarehouseLocationController::class, 'update']
)->name('warehouse-locations.update');

Route::delete(
    '/warehouse-locations/{location}',
    [WarehouseLocationController::class, 'destroy']
)->name('warehouse-locations.destroy');

Route::get('/_session-debug', function () {
    $file = null;
    $line = null;

    $headersAlreadySent = headers_sent($file, $line);

    return response()->json([
        'headers_already_sent' => $headersAlreadySent,
        'output_started_in_file' => $file,
        'output_started_at_line' => $line,
    ])
    ->header('X-WMS-Debug', 'route-reached')
    ->cookie('wms_manual_test', 'works', 120);
});