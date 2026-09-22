<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Production;
use App\Models\ItemCategory;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use App\Traits\SortsReportCategories;

class DailyProduction extends Component
{
    use SortsReportCategories;

    public $date;
    public $warehouse_id;

    public function mount()
    {
        $this->date = date('Y-m-d');
        // Get the first warehouse as default
        $this->warehouse_id = \App\Models\Warehouse::first()->id ?? null;
    }

    #[Computed]
    public function categories()
    {
        $categories = ItemCategory::with(['items' => function($q) {
            // Do not force orderBy here, trait will handle it
        }])->get();
        return $this->sortCategoriesAndItems($categories);
    }

    #[Computed]
    public function warehouses()
    {
        return \App\Models\Warehouse::orderBy('name')->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function reportData()
    {
        Gate::authorize('productions:read');

        if (!$this->date || !$this->warehouse_id) {
            return collect();
        }

        // Get all productions on this date for the selected warehouse
        $productions = Production::with(['user', 'rawItem', 'results.item'])
            ->where('date', $this->date)
            ->where('warehouse_id', $this->warehouse_id)
            ->orderBy('id', 'asc') // So it displays sequentially like NO 1, 2, 3...
            ->get();

        $data = [];

        foreach ($productions as $index => $prod) {
            $row = [
                'no' => $index + 1,
                'user' => $prod->user,
                'raw_quantity' => $prod->raw_quantity,
                'notes' => $prod->notes,
                'items' => [] // Keyed by item_id
            ];

            foreach ($prod->results as $result) {
                $row['items'][$result->item_id] = $result->quantity;
            }

            $data[] = $row;
        }

        return collect($data);
    }

    public function render()
    {
        return view('livewire.reports.daily-production')
            ->title('Daftar Produksi Harian');
    }
}
