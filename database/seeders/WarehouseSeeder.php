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

        // Seed 4 Teams for Gudang Saos (Tim A - D)
        $saos = \App\Models\Warehouse::where('name', 'Gudang Saos')->first();
        if ($saos) {
            $saosTeams = ['Tim A', 'Tim B', 'Tim C', 'Tim D'];
            foreach ($saosTeams as $teamName) {
                \App\Models\Team::updateOrCreate(
                    ['name' => $teamName],
                    ['warehouse_id' => $saos->id]
                );
            }
        }

        // Seed 4 Teams for Gudang Njambon (Tim E - H)
        $njambon = \App\Models\Warehouse::where('name', 'Gudang Njambon')->first();
        if ($njambon) {
            $njambonTeams = ['Tim E', 'Tim F', 'Tim G', 'Tim H'];
            foreach ($njambonTeams as $teamName) {
                \App\Models\Team::updateOrCreate(
                    ['name' => $teamName],
                    ['warehouse_id' => $njambon->id]
                );
            }
        }
    }
}
