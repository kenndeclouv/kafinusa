<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            ['name' => 'Gudang Utama', 'description' => 'Gudang Utama'],
            ['name' => 'Gudang Saos', 'description' => 'Gudang Saos'],
            ['name' => 'Gudang Njambon', 'description' => 'Gudang Njambon'],
        ];

        foreach ($warehouses as $warehouse) {
            \App\Models\Warehouse::updateOrCreate(
                ['name' => $warehouse['name']],
                ['description' => $warehouse['description']]
            );
        }
    }
}
