<?php

namespace App\Livewire\Petis;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\PetisOpname;
use App\Models\PetisRawMaterialEntry;
use App\Models\PetisRawMaterialInitialStock;
use App\Models\Warehouse;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class PrintRawMaterials extends Component
{
    #[Url]
    public $warehouse;

    #[Url]
    public $month;

    public function mount()
    {
        abort_unless(auth()->user()->can('petis_productions:read'), 403);
        $this->month = $this->month ?: Carbon::today()->format('Y-m');
        if (!$this->warehouse) {
            $this->warehouse = Warehouse::first()?->id;
        }
    }

    #[Computed]
    public function currentWarehouse()
    {
        return Warehouse::find($this->warehouse);
    }

    #[Computed]
    public function rawItems()
    {
        $cat = ItemCategory::where('name', 'Bahan Baku Petis')->first();
        if (!$cat) return collect();
        return Item::where('item_category_id', $cat->id)->orderBy('code')->get();
    }

    #[Computed]
    public function monthlyData()
    {
        if (!$this->warehouse) return [];

        $items = $this->rawItems;
        if ($items->isEmpty()) return [];

        $startOfMonth = Carbon::parse($this->month . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($this->month . '-01')->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        $entries = PetisRawMaterialEntry::where('warehouse_id', $this->warehouse)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        $opnames = PetisOpname::where('warehouse_id', $this->warehouse)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        // Stok Awal (Saldo Awal Buku)
        $explicitInitials = PetisRawMaterialInitialStock::where('warehouse_id', $this->warehouse)
            ->where('month', $this->month)
            ->get()
            ->keyBy('item_id');

        $prevMonth = $startOfMonth->copy()->subMonth()->format('Y-m');
        $prevInitials = PetisRawMaterialInitialStock::where('warehouse_id', $this->warehouse)
            ->where('month', $prevMonth)
            ->get()
            ->keyBy('item_id');

        $prevEntries = PetisRawMaterialEntry::where('warehouse_id', $this->warehouse)
            ->whereBetween('date', [
                $startOfMonth->copy()->subMonth()->startOfMonth()->format('Y-m-d'),
                $startOfMonth->copy()->subDay()->format('Y-m-d')
            ])->get();

        $initialStocks = [];
        foreach ($items as $item) {
            if (isset($explicitInitials[$item->id])) {
                $initialStocks[$item->id] = (float) $explicitInitials[$item->id]->initial_stock;
            } elseif (isset($prevInitials[$item->id]) || $prevEntries->where('item_id', $item->id)->isNotEmpty()) {
                $initPrev = (float) ($prevInitials[$item->id]?->initial_stock ?? 0);
                $purPrev = (float) $prevEntries->where('item_id', $item->id)->where('type', 'purchase')->sum('quantity');
                $usePrev = (float) $prevEntries->where('item_id', $item->id)->where('type', 'usage')->sum('quantity');
                $salPrev = (float) $prevEntries->where('item_id', $item->id)->where('type', 'sale')->sum('quantity');
                $initialStocks[$item->id] = $initPrev + $purPrev - ($usePrev + $salPrev);
            } else {
                $initialStocks[$item->id] = 0;
            }
        }

        $days = [];
        $runningStock = $initialStocks;
        $totals = [
            'purchase' => array_fill_keys($items->pluck('id')->toArray(), 0),
            'usage'    => array_fill_keys($items->pluck('id')->toArray(), 0),
            'sale'     => array_fill_keys($items->pluck('id')->toArray(), 0),
            'count_purchase' => array_fill_keys($items->pluck('id')->toArray(), 0),
            'count_usage'    => array_fill_keys($items->pluck('id')->toArray(), 0),
            'count_sale'     => array_fill_keys($items->pluck('id')->toArray(), 0),
        ];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = $startOfMonth->copy()->day($day)->format('Y-m-d');
            $dayData = [
                'day' => $day,
                'date' => $currentDate,
                'is_sunday' => $startOfMonth->copy()->day($day)->isSunday(),
                'purchase' => [],
                'usage'    => [],
                'sale'     => [],
                'book_stock' => [],
                'opname'   => [],
                'diff'     => [],
            ];

            foreach ($items as $item) {
                $itemId = $item->id;
                $p = (float) ($entries->first(fn($e) => $e->item_id == $itemId && $e->date->format('Y-m-d') == $currentDate && $e->type === 'purchase')?->quantity ?? 0);
                $u = (float) ($entries->first(fn($e) => $e->item_id == $itemId && $e->date->format('Y-m-d') == $currentDate && $e->type === 'usage')?->quantity ?? 0);
                $s = (float) ($entries->first(fn($e) => $e->item_id == $itemId && $e->date->format('Y-m-d') == $currentDate && $e->type === 'sale')?->quantity ?? 0);

                $dayData['purchase'][$itemId] = $p;
                $dayData['usage'][$itemId] = $u;
                $dayData['sale'][$itemId] = $s;

                $totals['purchase'][$itemId] += $p;
                $totals['usage'][$itemId] += $u;
                $totals['sale'][$itemId] += $s;

                if ($p > 0) $totals['count_purchase'][$itemId]++;
                if ($u > 0) $totals['count_usage'][$itemId]++;
                if ($s > 0) $totals['count_sale'][$itemId]++;

                $prev = $runningStock[$itemId];
                $bookStock = $prev + $p - ($u + $s);
                $runningStock[$itemId] = $bookStock;
                $dayData['book_stock'][$itemId] = $bookStock;

                $op = $opnames->first(fn($o) => $o->item_id == $itemId && $o->date->format('Y-m-d') == $currentDate);
                $dayData['opname'][$itemId] = $op ? (float) $op->actual_stock : null;
                $dayData['diff'][$itemId] = $op !== null ? $bookStock - (float) $op->actual_stock : null;
            }

            $days[$day] = $dayData;
        }

        return [
            'days' => $days,
            'initial_stocks' => $initialStocks,
            'final_stocks' => $runningStock,
            'totals' => $totals,
        ];
    }

    public function render()
    {
        return view('livewire.petis.print-raw-materials')
            ->title('Cetak Sirkulasi Bahan Baku Petis')
            ->layout('layouts.print');
    }
}
