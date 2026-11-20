<?php

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === $id;
});

Broadcast::channel(
    'warehouses.{warehouseId}.inventory',
    function (User $user, int $warehouseId): bool {
        return Warehouse::query()
            ->whereKey($warehouseId)
            ->where('is_active', true)
            ->exists();
    }
);
