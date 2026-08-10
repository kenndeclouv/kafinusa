<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ItemCategory;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'GARAM'],
            ['name' => 'TNE'],
            ['name' => 'TNE POLOS'],
            ['name' => 'LOS'],
            ['name' => 'PETIS'],
            ['name' => 'SOHUN'],
            ['name' => 'AREN'],
            ['name' => 'TRASI'],
        ];

        foreach ($categories as $category) {
            ItemCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
