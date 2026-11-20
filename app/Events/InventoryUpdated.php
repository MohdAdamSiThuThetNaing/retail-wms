<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class InventoryUpdated implements ShouldBroadcast
{
    public function __construct(
        public int $warehouseId,
        public int $productId,
        public string $warehouseCode,
        public string $warehouseName,
        public string $productSku,
        public string $productName,
        public int $quantityOnHand,
        public int $reorderLevel,
        public string $movementType,
        public ?string $reference,
        public string $occurredAt,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                "warehouses.{$this->warehouseId}.inventory"
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'inventory.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'warehouse_id' => $this->warehouseId,
            'warehouse_code' => $this->warehouseCode,
            'warehouse_name' => $this->warehouseName,
            'product_id' => $this->productId,
            'product_sku' => $this->productSku,
            'product_name' => $this->productName,
            'quantity_on_hand' => $this->quantityOnHand,
            'reorder_level' => $this->reorderLevel,
            'movement_type' => $this->movementType,
            'reference' => $this->reference,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
