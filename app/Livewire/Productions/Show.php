<?php

namespace App\Livewire\Productions;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Production;
use App\Models\Team;
use App\Models\Warehouse;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Flux\Flux;
use Illuminate\Support\Facades\DB;

class Show extends Component
{
    public Warehouse $warehouse;
    public $date;
    public $selected_category_id;
    public $global_notes = '';
    
    public $grid = [];

    public function mount(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
        $this->date = Carbon::today()->format('Y-m-d');
        $this->selected_category_id = ItemCategory::where('name', 'TNE')->first()?->id;

        $this->loadGrid();
    }

    public function updated($property)
    {
        if (in_array($property, ['date', 'selected_category_id'])) {
            $this->loadGrid();
        }

        if (str_starts_with($property, 'grid.')) {
            $parts = explode('.', $property);
            if (count($parts) >= 2) {
                $teamId = $parts[1];
                $this->saveRow($teamId);
            }
        }
    }

    public function updatedGlobalNotes($value)
    {
        Production::where('warehouse_id', $this->warehouse->id)
                  ->where('item_category_id', $this->selected_category_id)
                  ->whereDate('date', $this->date)
                  ->update(['notes' => $value]);
    }

    public function loadGrid()
    {
        $this->grid = [];
        
        if (!$this->selected_category_id) {
            return;
        }

        $teams = Team::where('warehouse_id', $this->warehouse->id)->orderBy('name')->get();
        $resultItems = $this->result_items;

        $existingProductions = Production::with('results')
            ->where('warehouse_id', $this->warehouse->id)
            ->where('date', $this->date)
            ->where('item_category_id', $this->selected_category_id)
            ->get()
            ->keyBy('team_name');
            
        $catName = strtolower(ItemCategory::find($this->selected_category_id)?->name ?? '');
        $defaultRawItemId = null;
        if (str_contains($catName, 'tne')) {
            $defaultRawItemId = Item::where('name', 'Candi')->first()?->id;
        } elseif (str_contains($catName, 'aren')) {
            $defaultRawItemId = Item::where('item_category_id', $this->selected_category_id)->where('name', 'LOS')->first()?->id;
        }

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
                'production_id' => $prod ? $prod->id : null,
                'team_name' => $team->name,
                'raw_item_id' => $prod ? $prod->raw_item_id : $defaultRawItemId,
                'raw_quantity' => $prod ? $prod->raw_quantity : '',
                'results' => $results,
            ];
        }
    }

    public function saveRow($teamId)
    {
        abort_unless(auth()->user()->can('productions:create'), 403);

        if (!isset($this->grid[$teamId])) return;

        $row = $this->grid[$teamId];
        $rawQty = (float)($row['raw_quantity'] ?: 0);
        
        $totalResultQty = 0;
        foreach ($row['results'] as $qty) {
            $totalResultQty += (float)($qty ?: 0);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($teamId, $row, $rawQty, $totalResultQty) {
            if ($rawQty <= 0 && $totalResultQty <= 0) {
                if ($row['production_id']) {
                    $prod = Production::find($row['production_id']);
                    if ($prod) {
                        $this->deleteProduction($prod);
                        $this->grid[$teamId]['production_id'] = null;
                    }
                }
                return;
            }

            $prod = null;
            if ($row['production_id']) {
                $prod = Production::find($row['production_id']);
                if ($prod) {
                    $this->reverseMutations($prod);
                    $prod->update([
                        'raw_quantity' => $rawQty,
                        'raw_item_id' => $row['raw_item_id'],
                        'notes' => $this->global_notes,
                    ]);
                    $prod->results()->delete();
                }
            }
            
            if (!$prod) {
                $prod = Production::create([
                    'warehouse_id' => $this->warehouse->id,
                    'item_category_id' => $this->selected_category_id,
                    'team_name' => $row['team_name'],
                    'date' => $this->date,
                    'raw_item_id' => $row['raw_item_id'],
                    'raw_quantity' => $rawQty,
                    'user_id' => auth()->id(),
                    'notes' => $this->global_notes,
                ]);
                $this->grid[$teamId]['production_id'] = $prod->id;
            }

            foreach ($row['results'] as $itemId => $qty) {
                if ((float)$qty > 0) {
                    $prod->results()->create([
                        'item_id' => $itemId,
                        'quantity' => (float)$qty,
                    ]);
                }
            }
            
            $prod->load('results.item');
            $prod->processMutations();
        });
    }

    public function reverseMutations(Production $prod)
    {
        foreach ($prod->stockMutations as $mutation) {
            $stock = \App\Models\WarehouseStock::firstOrCreate(
                ['warehouse_id' => $mutation->warehouse_id, 'item_id' => $mutation->item_id],
                ['current_stock' => 0, 'physical_stock' => 0]
            );
            $stock = \App\Models\WarehouseStock::where('id', $stock->id)->lockForUpdate()->first();
            if ($mutation->type === 'in') {
                $stock->current_stock -= $mutation->quantity;
            } else {
                $stock->current_stock += $mutation->quantity;
            }
            $stock->save();
            $mutation->delete();
        }
    }

    public function deleteProduction(Production $prod)
    {
        $this->reverseMutations($prod);
        $prod->results()->delete();
        $prod->delete();
    }

    #[Computed]
    public function raw_items()
    {
        if (!$this->selected_category_id) return collect();
        $category = ItemCategory::find($this->selected_category_id);
        if (!$category) return collect();

        $query = Item::with('category')->orderBy('name');
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

    #[Computed]
    public function spreadsheet_title()
    {
        $catName = strtolower(ItemCategory::find($this->selected_category_id)?->name ?? '');
        if (str_contains($catName, 'tne')) {
            return 'TNE NAGA TERBANG';
        } elseif (str_contains($catName, 'aren')) {
            return 'AREN NAGA TERBANG';
        }
        return strtoupper($catName);
    }

    #[Computed]
    public function categories()
    {
        return ItemCategory::where('name', 'like', '%TNE%')
            ->orWhere('name', 'like', '%AREN%')
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    #[Computed]
    public function result_items()
    {
        if (!$this->selected_category_id) return collect();
        return Item::where('item_category_id', $this->selected_category_id)->orderBy('weight', 'asc')->get();
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
        return view('livewire.productions.show');
    }
}
