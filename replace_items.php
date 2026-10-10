<?php
$file = 'app/Livewire/Reports/PrintStockCirculation.php';
$content = file_get_contents($file);

$oldRaw = <<<'PHP'
    #[Computed]
    public function rawItems()
    {
        if (!$this->item_category_id) return collect();
        $category = ItemCategory::find($this->item_category_id);
        $catName = strtolower($category->name ?? '');

        $query = Item::with('category')->orderBy('name');
        if (str_contains($catName, 'tne')) {
            $query->whereHas('category', fn($q) => $q->where('name', 'LOS'));
        } elseif (str_contains($catName, 'aren')) {
            $query->where('item_category_id', $category->id);
        } else {
            $query->whereHas('category', fn($q) => $q->where('name', 'LOS'));
        }
        return $this->sortItems($query->get(), 'LOS');
    }
PHP;

$newRaw = <<<'PHP'
    #[Computed]
    public function rawItems()
    {
        if (!$this->item_category_id) return collect();
        $category = ItemCategory::find($this->item_category_id);
        $catName = strtolower($category->name ?? '');

        $query = Item::with('category')->orderBy('name');
        if (str_contains($catName, 'tne')) {
            $query->whereHas('category', fn($q) => $q->where('name', 'LOS'));
        } elseif (str_contains($catName, 'aren')) {
            $query->where('item_category_id', $category->id)->where('name', 'like', '%LOS%');
        } else {
            $query->whereHas('category', fn($q) => $q->where('name', 'LOS'));
        }
        return $this->sortItems($query->get(), 'LOS');
    }
PHP;

$oldResult = <<<'PHP'
    #[Computed]
    public function resultItems()
    {
        if (!$this->item_category_id) return collect();
        $category = ItemCategory::find($this->item_category_id);
        $items = Item::where('item_category_id', $this->item_category_id)->get();
        if ($category) {
            return $this->sortItems($items, $category->name);
        }
        return $items;
    }
PHP;

$newResult = <<<'PHP'
    #[Computed]
    public function resultItems()
    {
        if (!$this->item_category_id) return collect();
        $category = ItemCategory::find($this->item_category_id);
        $catName = strtolower($category->name ?? '');

        $query = Item::where('item_category_id', $this->item_category_id);
        if (str_contains($catName, 'aren')) {
            $query->where('name', 'not like', '%LOS%');
        }
        $items = $query->get();
        if ($category) {
            return $this->sortItems($items, $category->name);
        }
        return $items;
    }
PHP;

$content = str_replace($oldRaw, $newRaw, $content);
$content = str_replace($oldResult, $newResult, $content);

file_put_contents($file, $content);
echo "Items fixed";
