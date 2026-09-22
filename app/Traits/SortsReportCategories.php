<?php

namespace App\Traits;

trait SortsReportCategories
{
    public function sortCategoriesAndItems($categories)
    {
        $categoryOrder = [
            'GARAM',
            'TNE',
            'TNE POLOS',
            'LOS',
            'PETIS',
            'SOHUN',
            'AREN',
            'TRASI'
        ];

        $itemOrder = [
            'GARAM' => ['B-32', 'K-20', 'G-20', 'KPL 1/4', 'KPL 1/2'],
            'TNE' => ['2 Kg', '3 Kg', 'KK 10', 'KK 20', '4K/10', '4K/20', '1/4 4,5', '1/2 4,5', '1/4 5', '1/2 5', '8 Kg', '9K/10', '9K/20', '10K/10', '10K/20'],
            'TNE POLOS' => ['PLS 1/2', 'PLS 1'],
            'LOS' => ['AGR 50', 'AGR 25', 'DS 50', 'DS 25', 'JGKR', 'JAWA', 'JMR', 'KRKTU', 'DLL'],
            'PETIS' => ['KI', 'Rf'],
            'SOHUN' => ['125', '75', '150', '300'],
            'AREN' => ['1/4 KCL', '1/2 KCL', '1/4 BSR', '1/2 BSR', 'LOS'],
            'TRASI' => ['A J', 'A W', 'LYR']
        ];

        $sortedCategories = $categories->sortBy(function ($category) use ($categoryOrder) {
            $index = array_search(strtoupper($category->name), $categoryOrder);
            return $index === false ? 999 : $index;
        })->values();

        foreach ($sortedCategories as $category) {
            $catName = strtoupper($category->name);
            
            if ($category->relationLoaded('items') && isset($itemOrder[$catName])) {
                $orderArr = array_map('strtoupper', $itemOrder[$catName]);
                
                $sortedItems = $category->items->sortBy(function ($item) use ($orderArr) {
                    $index = array_search(strtoupper($item->name), $orderArr);
                    if ($index === false) {
                        // Fallback order for unlisted items
                        return 999 + $item->id;
                    }
                    return $index;
                })->values();
                $category->setRelation('items', $sortedItems);
            } elseif ($category->relationLoaded('items')) {
                $category->setRelation('items', $category->items->sortBy('name')->values());
            }
        }

        return $sortedCategories;
    }

    public function sortItems($items, $categoryName)
    {
        $itemOrder = [
            'GARAM' => ['B-32', 'K-20', 'G-20', 'KPL 1/4', 'KPL 1/2'],
            'TNE' => ['2 Kg', '3 Kg', 'KK 10', 'KK 20', '4K/10', '4K/20', '1/4 4,5', '1/2 4,5', '1/4 5', '1/2 5', '8 Kg', '9K/10', '9K/20', '10K/10', '10K/20'],
            'TNE POLOS' => ['PLS 1/2', 'PLS 1'],
            'LOS' => ['AGR 50', 'AGR 25', 'DS 50', 'DS 25', 'JGKR', 'JAWA', 'JMR', 'KRKTU', 'DLL'],
            'PETIS' => ['KI', 'Rf'],
            'SOHUN' => ['125', '75', '150', '300'],
            'AREN' => ['1/4 KCL', '1/2 KCL', '1/4 BSR', '1/2 BSR', 'LOS'],
            'TRASI' => ['A J', 'A W', 'LYR']
        ];

        $catName = strtoupper($categoryName);

        if (isset($itemOrder[$catName])) {
            $orderArr = array_map('strtoupper', $itemOrder[$catName]);
            
            return $items->sortBy(function ($item) use ($orderArr) {
                $index = array_search(strtoupper($item->name), $orderArr);
                if ($index === false) {
                    return 999 + $item->id;
                }
                return $index;
            })->values();
        }

        return $items->sortBy('name')->values();
    }
}
