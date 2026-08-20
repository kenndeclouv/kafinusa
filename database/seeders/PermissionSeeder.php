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
            'permissions'
        ];

        // Aksi standar CRUD
        $actions = ['create', 'read', 'read-self', 'update', 'delete'];

        foreach ($features as $feature) {
            foreach ($actions as $action) {
                // Untuk master data mungkin read-self tidak relevan, tapi kita daftarkan saja agar konsisten
                Permission::updateOrCreate(['name' => "{$feature}:{$action}"]);
            }
        }

        // Custom permissions
        $customPermissions = [
            'notifications:send',
            'logs.view',
            'logs.delete',
            'logs.export',
            'system_monitor.view',
        ];

        foreach ($customPermissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }
    }
}
