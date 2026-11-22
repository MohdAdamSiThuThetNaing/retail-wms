<?php

namespace App\Services;

use App\Events\InventoryUpdated;
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
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be at least 1.',
            ]);
        }

        $stock = DB::transaction(function () use (
            $warehouse,
            $product,
            $quantity,
            $actorId,
            $reference
        ) {
            $stock = WarehouseStock::query()->firstOrCreate(
                [
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => 0,
                    'reorder_level' => 0,
                ]
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

        // Dispatch only after the transaction has committed.
        $this->broadcastStockUpdate(
            $stock,
            'receive',
            $reference
        );

        return $stock;
    }

    /**
     * Issue stock from a warehouse.
     */
    public function issue(
        Warehouse $warehouse,
        Product $product,
        int $quantity,
        ?int $actorId = null,
        ?string $reference = null
    ): WarehouseStock {
        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be at least 1.',
            ]);
        }

        $stock = DB::transaction(function () use (
            $warehouse, $product, $quantity, $actorId, $reference
        ) {
            $stock = WarehouseStock::query()
                ->where('warehouse_id', $warehouse->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            if (!$stock || $stock->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock in the selected warehouse.',
                ]);
            }

            $stock->decrement('quantity', $quantity);

            InventoryMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'type' => 'issue',
                'quantity' => $quantity,
                'reference' => $reference,
                'created_by' => $actorId,
                'created_at' => now(),
            ]);

            return $stock->refresh();
        }, attempts: 3);

        $this->broadcastStockUpdate($stock, 'issue', $reference);

        return $stock;
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
            throw ValidationException::withMessages([
                'to_warehouse_id' => 'Source and destination must differ.',
            ]);
        }

        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be at least 1.',
            ]);
        }

        $updatedStocks = DB::transaction(function () use (
            $from,
            $to,
            $product,
            $quantity,
            $actorId,
            $reference
        ) {
            // Lock rows in stable ID order to reduce deadlock risk.
            $keys = [
                [
                    'warehouse_id' => $from->id,
                    'product_id' => $product->id,
                ],
                [
                    'warehouse_id' => $to->id,
                    'product_id' => $product->id,
                ],
            ];

            usort(
                $keys,
                fn ($a, $b) => $a['warehouse_id'] <=> $b['warehouse_id']
            );

            $locked = [];

            foreach ($keys as $key) {
                $row = WarehouseStock::query()
                    ->where($key)
                    ->lockForUpdate()
                    ->first();

                if (!$row) {
                    $row = WarehouseStock::create(
                        $key + [
                            'quantity' => 0,
                            'reorder_level' => 0,
                        ]
                    );

                    $row = WarehouseStock::query()
                        ->whereKey($row->id)
                        ->lockForUpdate()
                        ->firstOrFail();
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

            foreach (
                [
                    [$from, 'transfer_out'],
                    [$to, 'transfer_in'],
                ] as [$warehouse, $type]
            ) {
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

            return [
                'source' => $source->refresh(),
                'destination' => $destination->refresh(),
            ];
        }, attempts: 3);

        // Both events are dispatched after a successful commit.
        $this->broadcastStockUpdate(
            $updatedStocks['source'],
            'transfer_out',
            $reference
        );

        $this->broadcastStockUpdate(
            $updatedStocks['destination'],
            'transfer_in',
            $reference
        );
    }

    private function broadcastStockUpdate(
        WarehouseStock $stock,
        string $movementType,
        ?string $reference
    ): void {
        $stock->loadMissing(['warehouse', 'product']);

        event(new InventoryUpdated(
            warehouseId: (int) $stock->warehouse_id,
            productId: (int) $stock->product_id,
            warehouseCode: $stock->warehouse->code,
            warehouseName: $stock->warehouse->name,
            productSku: $stock->product->sku,
            productName: $stock->product->name,
            quantityOnHand: (int) $stock->quantity,
            reorderLevel: (int) $stock->reorder_level,
            movementType: $movementType,
            reference: $reference,
            occurredAt: now()->toISOString(),
        ));
    }
}
