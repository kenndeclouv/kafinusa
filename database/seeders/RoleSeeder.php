<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $superadmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'markets:create',
            'markets:read',
            'markets:update',
            'markets:delete',
            'customers:create',
            'customers:read',
            'customers:update',
            'customers:delete',
            'customer_categories:create',
            'customer_categories:read',
            'customer_categories:update',
            'customer_categories:delete',
            'items:create',
            'items:read',
            'items:update',
            'items:delete',
            'item_categories:create',
            'item_categories:read',
            'item_categories:update',
            'item_categories:delete',
            'employees:create',
            'employees:read',
            'employees:update',
            'employees:delete',
            'order_books:create',
            'order_books:read',
            'order_books:update',
            'order_books:delete',
            'sales_schedules:create',
            'sales_schedules:read',
            'sales_schedules:update',
            'sales_schedules:delete',
            'orders:create',
            'orders:read',
            'orders:update',
            'orders:delete',
            'order_items:create',
            'order_items:read',
            'order_items:update',
            'order_items:delete',
            'users:read',
            'users:create',
            'users:update',
            'users:delete',
        ]);

        $sales = Role::firstOrCreate(['name' => 'sales']);
        $sales->syncPermissions([
            'markets:read',
            'customers:read',
            'items:read',
            'order_books:read-self',
            'order_books:create',
            'order_books:update',
            'orders:read-self',
            'orders:create',
            'orders:update',
            'orders:delete',
            'order_items:read-self',
            'order_items:create',
            'order_items:update',
            'order_items:delete',
        ]);
    }
}
