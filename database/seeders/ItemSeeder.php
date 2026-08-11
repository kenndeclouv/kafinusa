<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = \App\Models\ItemCategory::pluck('id', 'name')->toArray();

        $jsonFile = __DIR__ . '/data_items.json';
        $items = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

        foreach ($items as $index => $itemData) {
            $categoryId = $categories[$itemData['category']] ?? 1;
            
            // Generate a simple code, e.g., GARAM-B32, KEMASAN-2KG
            $code = strtoupper(substr($itemData['category'], 0, 3)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

            Item::firstOrCreate(
                ['name' => $itemData['name'], 'item_category_id' => $categoryId],
                [
                    'code' => $code,
                    'weight' => $itemData['weight'] * 1000,
                    'prices' => [
                        'umum' => $itemData['umum'] ?? 0,
                        'promo' => $itemData['promo'] ?? 0,
                        'khusus' => $itemData['khusus'] ?? 0,
                    ],
                ]
            );
        }
    }
}
