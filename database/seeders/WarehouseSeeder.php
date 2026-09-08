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
            ['name' => 'Gudang Rumah', 'description' => 'Gudang Utama di Rumah'],
            ['name' => 'Gudang CMB', 'description' => 'Gudang CMB'],
            ['name' => 'Gudang Pakis', 'description' => 'Gudang Cabang Pakis'],
        ];

        foreach ($warehouses as $warehouse) {
            \App\Models\Warehouse::updateOrCreate(
                ['name' => $warehouse['name']],
                ['description' => $warehouse['description']]
            );
        }
    }
}
