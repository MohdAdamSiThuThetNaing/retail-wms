<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $central = Warehouse::firstOrCreate(
            ['code' => 'WH-BKK-01'],
            ['name' => 'Bangkok Central Warehouse', 'address' => 'Bangkok', 'is_active' => true]
        );

        Warehouse::firstOrCreate(
            ['code' => 'WH-BKK-02'],
            ['name' => 'Bangkok East Warehouse', 'address' => 'Bangkok', 'is_active' => true]
        );

        Product::firstOrCreate(
            ['sku' => 'SKU-10001'],
            ['name' => 'Premium Cotton T-Shirt', 'description' => 'Demo inventory item', 'unit_price' => 390, 'is_active' => true]
        );

        Product::firstOrCreate(
            ['sku' => 'SKU-10002'],
            ['name' => 'Leather Wallet', 'description' => 'Demo inventory item', 'unit_price' => 590, 'is_active' => true]
        );
    }
}
