<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Fitur berdasarkan ERD dan menu
        $features = [
            'markets',
            'customers',
            'customer_categories',
            'items',
            'item_categories',
            'employees',
            'order_books',
            'orders',
            'order_items',
            'sales_schedules',
            'users',
            'roles',
            'permissions',
            'warehouses',
            'stock_mutations',
            'warehouse_stocks',
            'productions',
            // Modul Khusus Petis
            'petis_productions',
            'petis_cookings',
            'petis_raw_materials',
            'petis_cookers',
        ];

        // Aksi standar CRUD
        $actions = ['create', 'read', 'read-self', 'update', 'delete'];

        foreach ($features as $feature) {
            foreach ($actions as $action) {
                // Daftarkan aksi agar konsisten dan granular
                Permission::firstOrCreate(['name' => "{$feature}:{$action}"]);
            }
        }

        $customPermissions = [
            'notifications:send',
            'logs.view',
            'logs.delete',
            'logs.export',
            'system_monitor.view',
            'backups:read',
            'backups:create',
            'backups:delete',
            'order_books:bypass-lock',
            'stock_mutations:adjustment',
            'order_books:manage-shipments',
            'order_books:print-deliveries',
            'reports:daily-recap',
            'reports:stock-circulation',
            'reports:daily-production',
            'reports:petis-recap',
        ];

        foreach ($customPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
