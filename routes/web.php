<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Events\RealtimeTestNotification;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseLocationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockReceivingController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseOrderApprovalController;
use App\Http\Controllers\PurchaseOrderReceivingController;
use App\Http\Controllers\CustomerController;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');

});

/*
|--------------------------------------------------------------------------
| Realtime Test Route
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->post('/realtime-test', function (Request $request) {

    event(new RealtimeTestNotification(
        userId: (int) $request->user()->id,
        message: 'Hello! Your Laravel Reverb WebSocket is working!',
        sentAt: now()->toISOString(),
    ));

    return response()->json([
        'success' => true,
        'message' => 'Broadcast queued successfully.',
    ]);

})->name('realtime-test');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('can:dashboard.view')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    Route::resource('products', ProductController::class)
        ->middlewareFor(['index', 'show'], 'can:products.view')
        ->middlewareFor(['create', 'store'], 'can:products.create')
        ->middlewareFor(['edit', 'update'], 'can:products.edit')
        ->middlewareFor('destroy', 'can:products.delete');

    /*
    |--------------------------------------------------------------------------
    | Product Categories
    |--------------------------------------------------------------------------
    */

    Route::resource('categories', ProductCategoryController::class)
        ->middlewareFor(['index', 'show'], 'can:categories.view')
        ->middlewareFor(['create', 'store'], 'can:categories.create')
        ->middlewareFor(['edit', 'update'], 'can:categories.edit')
        ->middlewareFor('destroy', 'can:categories.delete');

    /*
    |--------------------------------------------------------------------------
    | Warehouses
    |--------------------------------------------------------------------------
    */

    Route::resource('warehouses', WarehouseController::class)
        ->middlewareFor(['index', 'show'], 'can:warehouses.view')
        ->middlewareFor(['create', 'store'], 'can:warehouses.create')
        ->middlewareFor(['edit', 'update'], 'can:warehouses.edit')
        ->middlewareFor('destroy', 'can:warehouses.delete');

    /*
    |--------------------------------------------------------------------------
    | Warehouse Locations
    |--------------------------------------------------------------------------
    */

    Route::get('/warehouse-locations', [WarehouseLocationController::class, 'index'])
        ->middleware('can:warehouse-locations.manage')
        ->name('warehouse-locations.index');

    Route::post('/warehouse-locations', [WarehouseLocationController::class, 'store'])
        ->middleware('can:warehouse-locations.manage')
        ->name('warehouse-locations.store');

    Route::put('/warehouse-locations/{warehouseLocation}', [WarehouseLocationController::class, 'update'])
        ->middleware('can:warehouse-locations.manage')
        ->name('warehouse-locations.update');

    Route::delete('/warehouse-locations/{warehouseLocation}', [WarehouseLocationController::class, 'destroy'])
        ->middleware('can:warehouse-locations.manage')
        ->name('warehouse-locations.destroy');

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */

    Route::get('/inventory', [InventoryController::class, 'index'])
        ->middleware('can:inventory.view')
        ->name('inventory.index');

    /*
    |--------------------------------------------------------------------------
    | Stock Receiving
    |--------------------------------------------------------------------------
    */

    Route::get('/stock-receiving', [StockReceivingController::class, 'create'])
        ->middleware('can:inventory.receive')
        ->name('stock-receiving.create');

    Route::post('/stock-receiving', [StockReceivingController::class, 'store'])
        ->middleware('can:inventory.receive')
        ->name('stock-receiving.store');

    /*
    |--------------------------------------------------------------------------
    | Stock Movements
    |--------------------------------------------------------------------------
    */

    Route::get('/stock-movements', [StockMovementController::class, 'index'])
        ->middleware('can:stock-movements.view')
        ->name('stock-movements.index');

    /*
    |--------------------------------------------------------------------------
    | Stock Transfers
    |--------------------------------------------------------------------------
    */

    Route::get('/stock-transfers/create', [StockTransferController::class, 'create'])
        ->middleware('can:inventory.transfer')
        ->name('stock-transfers.create');

    Route::post('/stock-transfers', [StockTransferController::class, 'store'])
        ->middleware('can:inventory.transfer')
        ->name('stock-transfers.store');

    /*
    |--------------------------------------------------------------------------
    | Supplier Management
    |--------------------------------------------------------------------------
    */

    Route::resource('suppliers', SupplierController::class)
        ->middlewareFor(['index', 'show'], 'can:suppliers.view')
        ->middlewareFor(['create', 'store'], 'can:suppliers.create')
        ->middlewareFor(['edit', 'update'], 'can:suppliers.edit')
        ->middlewareFor('destroy', 'can:suppliers.delete');

    /*
    |--------------------------------------------------------------------------
    | Purchase Order Management
    |--------------------------------------------------------------------------
    */

    Route::resource('purchase-orders', PurchaseOrderController::class)
        ->except(['destroy'])
        ->middlewareFor(['index', 'show'], 'can:purchase-orders.view')
        ->middlewareFor(['create', 'store'], 'can:purchase-orders.create')
        ->middlewareFor(['edit', 'update'], 'can:purchase-orders.create');

    Route::post('/purchase-orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit'])
        ->middleware('can:purchase-orders.create')
        ->name('purchase-orders.submit');

    /*
    |--------------------------------------------------------------------------
    | Purchase Order Approval
    |--------------------------------------------------------------------------
    */

    Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderApprovalController::class, 'approve'])
        ->middleware('can:purchase-orders.approve')
        ->name('purchase-orders.approve');

    Route::post('/purchase-orders/{purchaseOrder}/reject', [PurchaseOrderApprovalController::class, 'reject'])
        ->middleware('can:purchase-orders.approve')
        ->name('purchase-orders.reject');

    /*
    |--------------------------------------------------------------------------
    | Purchase Order Receiving
    |--------------------------------------------------------------------------
    */

    Route::get('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderReceivingController::class, 'create'])
        ->middleware('can:inventory.receive')
        ->name('purchase-orders.receive.create');

    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderReceivingController::class, 'store'])
        ->middleware('can:inventory.receive')
        ->name('purchase-orders.receive.store');

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    Route::get('/notifications', [NotificationController::class, 'history'])
        ->name('notifications.history');

    Route::get('/notifications/data', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unread-count');

    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

});

/*
|--------------------------------------------------------------------------
| Customer Management
|--------------------------------------------------------------------------
*/

Route::resource('customers', CustomerController::class)
    ->middlewareFor(['index', 'show'], 'can:customers.view')
    ->middlewareFor(
        ['create', 'store', 'edit', 'update', 'destroy'],
        'can:customers.manage'
    );