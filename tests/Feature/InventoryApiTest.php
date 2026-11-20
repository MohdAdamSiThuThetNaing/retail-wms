<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_receive_stock(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $warehouse = Warehouse::create([
            'code' => 'WH-01',
            'name' => 'Main Warehouse',
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-01',
            'name' => 'Demo Product',
            'unit_price' => 10,
        ]);

        $response = $this->postJson('/api/v1/inventory/receive', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 12,
            'reference' => 'GRN-0001',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('warehouse_stocks', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 12,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'type' => 'receive',
            'quantity' => 12,
        ]);
    }

    public function test_authenticated_user_can_transfer_stock(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $from = Warehouse::create([
            'code' => 'WH-01',
            'name' => 'Source Warehouse',
        ]);

        $to = Warehouse::create([
            'code' => 'WH-02',
            'name' => 'Destination Warehouse',
        ]);

        $product = Product::create([
            'sku' => 'SKU-02',
            'name' => 'Transfer Product',
            'unit_price' => 20,
        ]);

        WarehouseStock::create([
            'warehouse_id' => $from->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'reorder_level' => 5,
        ]);

        $response = $this->postJson('/api/v1/inventory/transfer', [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'product_id' => $product->id,
            'quantity' => 20,
            'reference' => 'TRF-0001',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('warehouse_stocks', [
            'warehouse_id' => $from->id,
            'product_id' => $product->id,
            'quantity' => 30,
        ]);

        $this->assertDatabaseHas('warehouse_stocks', [
            'warehouse_id' => $to->id,
            'product_id' => $product->id,
            'quantity' => 20,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $from->id,
            'type' => 'transfer_out',
            'quantity' => 20,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'warehouse_id' => $to->id,
            'type' => 'transfer_in',
            'quantity' => 20,
        ]);
    }

    public function test_transfer_fails_when_stock_is_insufficient(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $from = Warehouse::create([
            'code' => 'WH-01',
            'name' => 'Source Warehouse',
        ]);

        $to = Warehouse::create([
            'code' => 'WH-02',
            'name' => 'Destination Warehouse',
        ]);

        $product = Product::create([
            'sku' => 'SKU-03',
            'name' => 'Limited Product',
            'unit_price' => 30,
        ]);

        WarehouseStock::create([
            'warehouse_id' => $from->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'reorder_level' => 2,
        ]);

        $response = $this->postJson('/api/v1/inventory/transfer', [
            'from_warehouse_id' => $from->id,
            'to_warehouse_id' => $to->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseHas('warehouse_stocks', [
            'warehouse_id' => $from->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $this->assertDatabaseMissing('warehouse_stocks', [
            'warehouse_id' => $to->id,
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_unauthenticated_user_cannot_receive_stock(): void
    {
        $response = $this->postJson('/api/v1/inventory/receive', []);

        $response->assertUnauthorized();
    }
}
