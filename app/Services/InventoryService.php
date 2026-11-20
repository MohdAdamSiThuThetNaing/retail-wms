<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function receive(
        Warehouse $warehouse,
        Product $product,
        int $quantity,
        ?int $actorId = null,
        ?string $reference = null
    ): WarehouseStock {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be at least 1.']);
        }

        return DB::transaction(function () use ($warehouse, $product, $quantity, $actorId, $reference) {
            $stock = WarehouseStock::query()->firstOrCreate(
                ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
                ['quantity' => 0, 'reorder_level' => 0]
            );

            $stock = WarehouseStock::query()
                ->whereKey($stock->id)
                ->lockForUpdate()
                ->firstOrFail();

            $stock->increment('quantity', $quantity);

            InventoryMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => 'receive',
                'quantity' => $quantity,
                'reference' => $reference,
                'created_by' => $actorId,
                'created_at' => now(),
            ]);

            return $stock->refresh();
        }, attempts: 3);
    }

    public function transfer(
        Warehouse $from,
        Warehouse $to,
        Product $product,
        int $quantity,
        ?int $actorId = null,
        ?string $reference = null
    ): void {
        if ($from->is($to)) {
            throw ValidationException::withMessages(['to_warehouse_id' => 'Source and destination must differ.']);
        }

        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be at least 1.']);
        }

        DB::transaction(function () use ($from, $to, $product, $quantity, $actorId, $reference) {
            // Lock rows in stable ID order to reduce deadlock risk when opposite transfers happen concurrently.
            $keys = [
                ['warehouse_id' => $from->id, 'product_id' => $product->id],
                ['warehouse_id' => $to->id, 'product_id' => $product->id],
            ];
            usort($keys, fn ($a, $b) => $a['warehouse_id'] <=> $b['warehouse_id']);

            $locked = [];
            foreach ($keys as $key) {
                $row = WarehouseStock::query()
                    ->where($key)
                    ->lockForUpdate()
                    ->first();

                if (!$row) {
                    $row = WarehouseStock::create($key + ['quantity' => 0, 'reorder_level' => 0]);
                    $row = WarehouseStock::query()->whereKey($row->id)->lockForUpdate()->firstOrFail();
                }
                $locked[$row->warehouse_id] = $row;
            }

            $source = $locked[$from->id];
            $destination = $locked[$to->id];

            if ($source->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock in the source warehouse.',
                ]);
            }

            $source->decrement('quantity', $quantity);
            $destination->increment('quantity', $quantity);

            foreach ([[$from, 'transfer_out'], [$to, 'transfer_in']] as [$warehouse, $type]) {
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'reference' => $reference,
                    'created_by' => $actorId,
                    'created_at' => now(),
                ]);
            }
        }, attempts: 3);
    }
}
