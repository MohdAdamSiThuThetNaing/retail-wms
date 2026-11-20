<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Products and categories
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',

            // Warehouses and locations
            'warehouses.view',
            'warehouses.create',
            'warehouses.edit',
            'warehouses.delete',

            'warehouse-locations.manage',

            // Inventory
            'inventory.view',
            'inventory.receive',
            'inventory.transfer',
            'inventory.adjust',

            // Stock movements
            'stock-movements.view',

            // Suppliers and purchase orders
            'suppliers.view',
            'suppliers.manage',
            'purchase-orders.view',
            'purchase-orders.create',
            'purchase-orders.approve',

            // Customers and sales
            'customers.view',
            'customers.manage',
            'sales-orders.view',
            'sales-orders.create',
            'sales-orders.manage',

            // Reports
            'reports.view',
            'reports.export',

            // Users and settings
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.manage',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'Super Admin' => $permissions,

            'Admin' => $permissions,

            'Warehouse Manager' => [
                'dashboard.view',
                'products.view',
                'categories.view',
                'warehouses.view',
                'warehouse-locations.manage',
                'inventory.view',
                'inventory.receive',
                'inventory.transfer',
                'inventory.adjust',
                'stock-movements.view',
                'suppliers.view',
                'purchase-orders.view',
                'purchase-orders.create',
                'purchase-orders.approve',
                'reports.view',
            ],

            'Warehouse Staff' => [
                'dashboard.view',
                'products.view',
                'warehouses.view',
                'inventory.view',
                'inventory.receive',
                'inventory.transfer',
                'stock-movements.view',
            ],

            'Sales Manager' => [
                'dashboard.view',
                'products.view',
                'customers.view',
                'customers.manage',
                'sales-orders.view',
                'sales-orders.create',
                'sales-orders.manage',
                'reports.view',
                'reports.export',
            ],

            'Sales Staff' => [
                'dashboard.view',
                'products.view',
                'customers.view',
                'sales-orders.view',
                'sales-orders.create',
            ],

            'Accountant' => [
                'dashboard.view',
                'purchase-orders.view',
                'sales-orders.view',
                'reports.view',
                'reports.export',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($rolePermissions);
        }

        // Assign Super Admin to the existing administrator account.
        $admin = User::find(1);

        if ($admin) {
            $admin->assignRole('Super Admin');
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
