<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseLocationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StockReceivingController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');
});

// Stock Receiving
Route::middleware('auth')->group(function () {
    Route::get('/stock-receiving', [StockReceivingController::class, 'create'])
        ->name('stock-receiving.create');

    Route::post('/stock-receiving', [StockReceivingController::class, 'store'])
        ->name('stock-receiving.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Dashboard
Route::get('/', [DashboardController::class, 'index'])
    ->middleware('auth')
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

// Inventory
Route::get('/inventory', [InventoryController::class, 'index'])
    ->name('inventory.index');

Route::get('/stock-movements', [StockMovementController::class, 'index'])
    ->name('stock-movements.index');


Route::get('/stock-transfers/create', [StockTransferController::class, 'create'])
    ->name('stock-transfers.create');

Route::post('/stock-transfers', [StockTransferController::class, 'store'])
    ->name('stock-transfers.store');