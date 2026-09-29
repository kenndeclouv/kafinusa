<?php

namespace App\Livewire\Productions;

use App\Models\Warehouse;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\Production;
use App\Models\Team;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PrintProduction extends Component
{
    public Warehouse $warehouse;
    public $date;
    public $category_id;
    
    public $grid = [];
    public $global_notes = '';

    public function mount(Warehouse $warehouse, $date, $category)
    {
        abort_unless(auth()->user() && auth()->user()->can('productions:read'), 403);

        $this->warehouse = $warehouse;
        $this->date = $date;
        $this->category_id = $category;

        $this->loadGrid();
    }

    #[Computed]
    public function category()
    {
        return ItemCategory::find($this->category_id);
    }

    #[Computed]
    public function result_items()
    {
        if (!$this->category_id) return collect();
        return Item::where('item_category_id', $this->category_id)->orderBy('weight', 'asc')->get();
    }

    #[Computed]
    public function raw_items()
    {
        if (!$this->category_id) return collect();
        $category = ItemCategory::find($this->category_id);
        if (!$category) return collect();

        $query = \App\Models\Item::with('category')->orderBy('name');
        $catName = strtolower($category->name);

        if (str_contains($catName, 'tne')) {
            $query->whereHas('category', fn($q) => $q->where('name', 'LOS'));
        } elseif (str_contains($catName, 'aren')) {
            $query->where('item_category_id', $category->id);
        } else {
            $query->whereHas('category', fn($q) => $q->where('name', 'LOS'));
        }

        return $query->get();
    }

    public function loadGrid()
    {
        $this->grid = [];
        $teams = Team::where('warehouse_id', $this->warehouse->id)->orderBy('id')->get();
        $resultItems = $this->result_items;

        $existingProductions = Production::with(['results'])
            ->where('warehouse_id', $this->warehouse->id)
            ->where('item_category_id', $this->category_id)
            ->whereDate('date', $this->date)
            ->get();

        $this->global_notes = $existingProductions->first()?->notes ?? '';

        foreach ($teams as $team) {
            $prod = $existingProductions->firstWhere('team_name', $team->name);
            
            $results = [];
            foreach ($resultItems as $item) {
                if ($prod) {
                    $res = $prod->results->firstWhere('item_id', $item->id);
                    $results[$item->id] = $res ? $res->quantity : '';
                } else {
                    $results[$item->id] = '';
                }
            }

            $this->grid[$team->id] = [
                'team_name' => $team->name,
                'raw_item_id' => $prod ? $prod->raw_item_id : null,
                'raw_quantity' => $prod ? $prod->raw_quantity : '',
                'results' => $results,
            ];
        }
    }

    #[Computed]
    public function summary()
    {
        $L = 0;
        $P = 0;

        $resultItems = $this->result_items->keyBy('id');
        $rawItems = collect($this->raw_items)->keyBy('id');

        foreach ($this->grid as $teamId => $row) {
            $rawQty = (float)($row['raw_quantity'] ?? 0);
            $rawItemId = $row['raw_item_id'] ?? null;
            
            if ($rawQty > 0 && $rawItemId && isset($rawItems[$rawItemId])) {
                $P += $rawQty * $rawItems[$rawItemId]->weight;
            }

            foreach ($row['results'] ?? [] as $itemId => $qty) {
                $qty = (float)$qty;
                if ($qty > 0 && isset($resultItems[$itemId])) {
                    $L += $qty * $resultItems[$itemId]->weight;
                }
            }
        }

        return [
            'L' => $L,
            'P' => $P,
            'selisih' => $L - $P,
        ];
    }

    public function formatQty($qty)
    {
        if ($qty == 0) return '0';
        $formatted = number_format($qty, 2, ',', '.');
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, ',');
        return $formatted;
    }

    public function render()
    {
        return view('livewire.productions.print-production')
            ->title("Cetak Produksi - {$this->warehouse->name}")
            ->layout('layouts.print');
    }
}
