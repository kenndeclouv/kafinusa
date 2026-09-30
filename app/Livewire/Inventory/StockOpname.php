<?php

namespace App\Livewire\Inventory;

use App\Models\Warehouse;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\StockOpname as StockOpnameModel;
use App\Models\WarehouseStock;
use App\Models\StockMutation;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Flux;

#[Layout('layouts.app')]
class StockOpname extends Component
{
    public $warehouse_id;
    public $item_category_id;
    public $month;
    public $year;

    public $warehouses = [];
    public $categories = [];
    public $months = [];
    public $years = [];
    
    // Store input values here
    public $actualStocks = [];
    public $notes = [];

    public function mount()
    {
        $this->warehouses = Warehouse::pluck('name', 'id')->toArray();
        $this->categories = ItemCategory::orderBy('name')->pluck('name', 'id')->toArray();
        
        for ($m = 1; $m <= 12; $m++) {
            $this->months[$m] = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
        }
        
        for ($y = now()->year - 2; $y <= now()->year; $y++) {
            $this->years[$y] = (string) $y;
        }
        
        $this->warehouse_id = array_key_first($this->warehouses);
        $this->item_category_id = array_key_first($this->categories);
        $this->month = now()->month;
        $this->year = now()->year;
        
        $this->loadOpnames();
    }

    public function updated($property)
    {
        if (in_array($property, ['warehouse_id', 'item_category_id', 'month', 'year'])) {
            $this->loadOpnames();
        }
    }

    public function getItemsProperty()
    {
        if (!$this->item_category_id) return collect();
        return Item::where('item_category_id', $this->item_category_id)->orderBy('name')->get();
    }

    public function loadOpnames()
    {
        $this->actualStocks = [];
        $this->notes = [];

        if (!$this->warehouse_id || !$this->item_category_id) return;

        $items = $this->items;
        $opnames = StockOpnameModel::where('warehouse_id', $this->warehouse_id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->whereIn('item_id', $items->pluck('id'))
            ->get()
            ->keyBy('item_id');

        foreach ($items as $item) {
            if ($opnames->has($item->id)) {
                $this->actualStocks[$item->id] = $opnames[$item->id]->actual_stock / ($item->weight ?? 1); // convert back to raw units for input
                $this->notes[$item->id] = $opnames[$item->id]->notes;
            } else {
                $this->actualStocks[$item->id] = ''; // empty means not opnamed yet
                $this->notes[$item->id] = '';
            }
        }
    }

    public function getSystemStock($itemId)
    {
        // For display only (if they want to see what system expects)
        // If they view current month, just show real WarehouseStock
        if ($this->month == now()->month && $this->year == now()->year) {
            $stock = WarehouseStock::where('warehouse_id', $this->warehouse_id)
                ->where('item_id', $itemId)
                ->value('current_stock') ?? 0;
            return $stock;
        }
        
        // Otherwise try to find existing opname system_stock
        $op = StockOpnameModel::where('warehouse_id', $this->warehouse_id)
            ->where('item_id', $itemId)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->first();
            
        return $op ? ($op->system_stock / ($this->items->firstWhere('id', $itemId)->weight ?? 1)) : 0;
    }

    public function save()
    {
        $this->authorize('permissions:stock_mutations:create');

        $isCurrentMonth = ($this->month == now()->month && $this->year == now()->year);

        foreach ($this->items as $item) {
            if (!isset($this->actualStocks[$item->id]) || $this->actualStocks[$item->id] === '') {
                continue;
            }

            $actual = (float) $this->actualStocks[$item->id] * ($item->weight ?? 1);
            $sys = $this->getSystemStock($item->id) * ($item->weight ?? 1);
            
            $opname = StockOpnameModel::updateOrCreate(
                [
                    'warehouse_id' => $this->warehouse_id,
                    'item_id' => $item->id,
                    'month' => $this->month,
                    'year' => $this->year,
                ],
                [
                    'system_stock' => $sys,
                    'actual_stock' => $actual,
                    'difference' => $actual - $sys,
                    'notes' => $this->notes[$item->id] ?? null,
                    'recorded_by_id' => auth()->id(),
                ]
            );

            // Only adjust realtime stock if opname is for CURRENT month
            if ($isCurrentMonth && $actual != $sys) {
                // Adjust WarehouseStock
                WarehouseStock::updateOrCreate(
                    [
                        'warehouse_id' => $this->warehouse_id,
                        'item_id' => $item->id,
                    ],
                    ['current_stock' => $this->actualStocks[$item->id]] // raw unit
                );

                // Record adjustment mutation
                $diff = $this->actualStocks[$item->id] - $this->getSystemStock($item->id);
                StockMutation::create([
                    'warehouse_id' => $this->warehouse_id,
                    'item_id' => $item->id,
                    'type' => $diff > 0 ? 'in' : 'out',
                    'quantity' => abs($diff),
                    'mutation_date' => now(),
                    'reference_type' => \App\Models\StockOpname::class,
                    'reference_id' => $opname->id,
                    'transaction_category' => 'Penyesuaian Opname',
                    'notes' => 'Opname diff: ' . ($this->notes[$item->id] ?? '-'),
                    'user_id' => auth()->id(),
                ]);
            }
        }

        Flux::toast(
            heading: 'Tersimpan',
            text: 'Stock Opname berhasil disimpan.',
            variant: 'success'
        );
        
        $this->loadOpnames();
    }

    public function render()
    {
        return view('livewire.inventory.stock-opname');
    }
}
