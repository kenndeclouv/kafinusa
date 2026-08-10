<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CustomerCategory;

class CustomerCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['code' => 'ECER', 'name' => 'Eceran'],
            ['code' => 'TBB', 'name' => 'Toko Bahan Bakso'],
            ['code' => 'SLP', 'name' => 'SLP'],
            ['code' => 'UKM', 'name' => 'Usaha Kecil Menengah'],
            ['code' => 'GROS', 'name' => 'Grosir'],
        ];

        foreach ($categories as $category) {
            CustomerCategory::firstOrCreate(['code' => $category['code']], $category);
        }
    }
}
