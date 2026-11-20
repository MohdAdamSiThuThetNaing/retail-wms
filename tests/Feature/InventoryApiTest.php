<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_receive_stock(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $warehouse = Warehouse::create(['code' => 'WH-01', 'name' => 'Main', 'is_active' => true]);
        $product = Product::create(['sku' => 'SKU-01', 'name' => 'Demo', 'unit_price' => 10]);

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
}
