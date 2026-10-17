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

            Item::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $itemData['name'],
                    'item_category_id' => $categoryId,
                    'weight' => $itemData['weight'] * 1000,
                    'prices' => array_filter([
                        'umum' => $itemData['umum'] ?? 0,
                        'promo' => $itemData['promo'] ?? 0,
                        'khusus' => $itemData['khusus'] ?? 0,
                        'lain_lain' => $itemData['lain_lain'] ?? null,
                    ], fn($val) => $val !== null),
                ]
            );
        }

        // Seed 9 Bahan Baku Petis (sesuai lembar kerja Sirkulasi Petis)
        $bbCategory = \App\Models\ItemCategory::where('name', 'Bahan Baku Petis')->first();
        if ($bbCategory) {
            $rawMaterials = [
                ['code' => 'BB-001', 'name' => 'Pewarna'],
                ['code' => 'BB-002', 'name' => 'Sakarin'],
                ['code' => 'BB-003', 'name' => 'Gaplek'],
                ['code' => 'BB-004', 'name' => 'Gemblong'],
                ['code' => 'BB-005', 'name' => 'Gula'],
                ['code' => 'BB-006', 'name' => 'Garam'],
                ['code' => 'BB-007', 'name' => 'Sorbat'],
                ['code' => 'BB-008', 'name' => 'Kaleng'],
                ['code' => 'BB-009', 'name' => 'Tutup'],
            ];

            foreach ($rawMaterials as $rm) {
                Item::updateOrCreate(
                    ['code' => $rm['code']],
                    [
                        'name' => $rm['name'],
                        'item_category_id' => $bbCategory->id,
                        'weight' => 1000,
                        'prices' => ['umum' => 0],
                        'is_saleable' => true,
                    ]
                );
            }
        }

        // Seed Produk Jadi Petis (Petis Kaleng & Petis Refill)
        $petisCategory = \App\Models\ItemCategory::where('name', 'PETIS')->first();
        if ($petisCategory) {
            $petisItems = [
                ['code' => 'PETIS-KL', 'name' => 'Petis Kaleng (KI)', 'weight' => 9300],
                ['code' => 'PETIS-RF', 'name' => 'Petis Refill (Bal)', 'weight' => 5000],
            ];

            foreach ($petisItems as $pItem) {
                Item::updateOrCreate(
                    ['code' => $pItem['code']],
                    [
                        'name' => $pItem['name'],
                        'item_category_id' => $petisCategory->id,
                        'weight' => $pItem['weight'],
                        'prices' => ['umum' => 0],
                        'is_saleable' => true,
                    ]
                );
            }
        }
    }
}
