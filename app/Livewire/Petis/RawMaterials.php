<?php

namespace App\Livewire\Petis;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\PetisOpname;
use App\Models\PetisRawMaterialEntry;
use App\Models\Warehouse;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RawMaterials extends Component
{
    public $warehouse_id;
    public $month; // Format: 'Y-m', misal '2026-10'
    public $activeTab = 'rekap'; // rekap | purchase | usage | sale | stock | selisih

    // Inline grid state: grid.{type}.{day}.{item_id}
    public $grid = [
        'purchase' => [],
        'usage'    => [],
        'sale'     => [],
        'opname'   => [],
    ];

    // Inline initial stocks for row S AWAL: initial_stocks.{item_id}
    public $initial_stocks = [];

    public function mount()
    {
        abort_unless(auth()->user()->can('petis_productions:read'), 403);

        $petisWarehouse = Warehouse::where('name', 'Gudang Petis')->first() ?? Warehouse::first();
        $this->warehouse_id = $petisWarehouse?->id;

        $this->month = Carbon::today()->format('Y-m');

        $this->loadGrid();
    }

    public function updated($property, $value = null)
    {
        if (in_array($property, ['month', 'warehouse_id'])) {
            $this->loadGrid();
            unset($this->monthlyData);
            return;
        }

        if (str_starts_with($property, 'grid.')) {
            // format: grid.{type}.{day}.{itemId}
            $parts = explode('.', $property);
            if (count($parts) >= 4) {
                $type = $parts[1];
                $day = (int) $parts[2];
                $itemId = (int) $parts[3];
                $val = $this->grid[$type][$day][$itemId] ?? '';

                if ($type === 'opname') {
                    $this->saveOpnameCell($day, $itemId, $val);
                } else {
                    $this->saveCell($type, $day, $itemId, $val);
                }
                unset($this->monthlyData);
            }
        }

        if (str_starts_with($property, 'initial_stocks.')) {
            // format: initial_stocks.{itemId}
            $parts = explode('.', $property);
            if (count($parts) >= 2) {
                $itemId = (int) $parts[1];
                $val = $this->initial_stocks[$itemId] ?? '';
                $this->saveInitialStockCell($itemId, $val);
                unset($this->monthlyData);
            }
        }
    }

    public function loadGrid()
    {
        $this->grid = [
            'purchase' => [],
            'usage'    => [],
            'sale'     => [],
            'opname'   => [],
        ];
        $this->initial_stocks = [];

        if (!$this->warehouse_id) {
            return;
        }

        $startOfMonth = Carbon::parse($this->month . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;
        $items = $this->rawItems;

        if ($items->isEmpty()) {
            return;
        }

        // 1. Ambil semua entries bulan ini
        $entries = PetisRawMaterialEntry::where('warehouse_id', $this->warehouse_id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        // 2. Ambil semua opname bulan ini
        $opnames = PetisOpname::where('warehouse_id', $this->warehouse_id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        // 3. Stok Awal
        $lastMonthEnd = $startOfMonth->copy()->subDay()->format('Y-m-d');
        $prevOpnames = PetisOpname::where('warehouse_id', $this->warehouse_id)
            ->where('date', '<=', $lastMonthEnd)
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('item_id');

        foreach ($items as $item) {
            $opnameDay1 = $opnames->where('item_id', $item->id)->where('date', $startOfMonth->format('Y-m-d'))->first();
            if ($opnameDay1 !== null) {
                $this->initial_stocks[$item->id] = (float) $opnameDay1->actual_stock;
            } elseif (isset($prevOpnames[$item->id])) {
                $this->initial_stocks[$item->id] = (float) $prevOpnames[$item->id]->first()->actual_stock;
            } else {
                $this->initial_stocks[$item->id] = '';
            }
        }

        // 4. Populate grid per hari
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = $startOfMonth->copy()->day($day)->format('Y-m-d');

            foreach (['purchase', 'usage', 'sale'] as $type) {
                foreach ($items as $item) {
                    $entry = $entries->first(fn($e) => $e->item_id == $item->id && $e->date->format('Y-m-d') == $currentDate && $e->type === $type);
                    $this->grid[$type][$day][$item->id] = $entry && (float) $entry->quantity > 0 ? (float) $entry->quantity : '';
                }
            }

            foreach ($items as $item) {
                $op = $opnames->first(fn($o) => $o->item_id == $item->id && $o->date->format('Y-m-d') == $currentDate);
                $this->grid['opname'][$day][$item->id] = $op !== null ? (float) $op->actual_stock : '';
            }
        }
    }

    public function saveCell($type, $day, $itemId, $value)
    {
        abort_unless(auth()->user()->can('petis_productions:create'), 403);

        $startOfMonth = Carbon::parse($this->month . '-01')->startOfMonth();
        $date = $startOfMonth->copy()->day((int) $day)->format('Y-m-d');
        $qty = (float) ($value ?: 0);

        if ($qty > 0) {
            PetisRawMaterialEntry::updateOrCreate(
                [
                    'warehouse_id' => $this->warehouse_id,
                    'item_id' => $itemId,
                    'date' => $date,
                    'type' => $type,
                ],
                [
                    'quantity' => $qty,
                    'user_id' => auth()->id(),
                ]
            );
        } else {
            PetisRawMaterialEntry::where([
                'warehouse_id' => $this->warehouse_id,
                'item_id' => $itemId,
                'date' => $date,
                'type' => $type,
            ])->delete();
        }
    }

    public function saveOpnameCell($day, $itemId, $value)
    {
        abort_unless(auth()->user()->can('petis_productions:create'), 403);

        $startOfMonth = Carbon::parse($this->month . '-01')->startOfMonth();
        $date = $startOfMonth->copy()->day((int) $day)->format('Y-m-d');

        if ($value !== '' && $value !== null) {
            PetisOpname::updateOrCreate(
                [
                    'warehouse_id' => $this->warehouse_id,
                    'item_id' => $itemId,
                    'date' => $date,
                ],
                [
                    'actual_stock' => (float) $value,
                    'user_id' => auth()->id(),
                ]
            );
        } else {
            PetisOpname::where([
                'warehouse_id' => $this->warehouse_id,
                'item_id' => $itemId,
                'date' => $date,
            ])->delete();
        }
    }

    public function saveInitialStockCell($itemId, $value)
    {
        abort_unless(auth()->user()->can('petis_productions:create'), 403);

        $startOfMonth = Carbon::parse($this->month . '-01')->startOfMonth();
        $date = $startOfMonth->format('Y-m-d');

        if ($value !== '' && $value !== null) {
            PetisOpname::updateOrCreate(
                [
                    'warehouse_id' => $this->warehouse_id,
                    'item_id' => $itemId,
                    'date' => $date,
                ],
                [
                    'actual_stock' => (float) $value,
                    'notes' => 'Stok Awal Bulan',
                    'user_id' => auth()->id(),
                ]
            );
        } else {
            PetisOpname::where([
                'warehouse_id' => $this->warehouse_id,
                'item_id' => $itemId,
                'date' => $date,
            ])->delete();
        }
    }

    #[Computed]
    public function warehouses()
    {
        $query = Warehouse::query()->orderBy('name');
        if (
            auth()->user()->hasRole('checker') &&
            !auth()->user()->hasRole('kepala_checker') &&
            !auth()->user()->hasRole('superadmin')
        ) {
            $query->where('id', auth()->user()->warehouse_id);
        }
        return $query->get();
    }

    #[Computed]
    public function rawMaterialCategory()
    {
        return ItemCategory::where('name', 'Bahan Baku Petis')->first();
    }

    #[Computed]
    public function rawItems()
    {
        $cat = $this->rawMaterialCategory;
        if (!$cat) {
            return collect();
        }
        return Item::where('item_category_id', $cat->id)
            ->orderBy('code')
            ->get();
    }

    /**
     * Menghitung seluruh data sirkulasi bulanan per tanggal (1 .. daysInMonth)
     */
    #[Computed]
    public function monthlyData()
    {
        if (!$this->warehouse_id || !$this->rawMaterialCategory) {
            return [];
        }

        $items = $this->rawItems;
        if ($items->isEmpty()) {
            return [];
        }

        $startOfMonth = Carbon::parse($this->month . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        // 1. Ambil semua entries bulan ini
        $entries = PetisRawMaterialEntry::where('warehouse_id', $this->warehouse_id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        // 2. Ambil semua opname bulan ini
        $opnames = PetisOpname::where('warehouse_id', $this->warehouse_id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        // 3. Tentukan Stok Awal per item untuk tanggal 1
        $initialStocks = [];
        $lastMonthEnd = $startOfMonth->copy()->subDay()->format('Y-m-d');
        $prevOpnames = PetisOpname::where('warehouse_id', $this->warehouse_id)
            ->where('date', '<=', $lastMonthEnd)
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('item_id');

        foreach ($items as $item) {
            $opnameDay1 = $opnames->where('item_id', $item->id)->where('date', $startOfMonth->format('Y-m-d'))->first();
            if ($opnameDay1 !== null) {
                $initialStocks[$item->id] = (float) $opnameDay1->actual_stock;
            } elseif (isset($prevOpnames[$item->id])) {
                $initialStocks[$item->id] = (float) $prevOpnames[$item->id]->first()->actual_stock;
            } else {
                $initialStocks[$item->id] = 0;
            }
        }

        // 4. Struktur data per hari
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
                'formatted_date' => $startOfMonth->copy()->day($day)->translatedFormat('d M'),
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

                // Purchase
                $p = (float) ($entries->first(fn($e) => $e->item_id == $itemId && $e->date->format('Y-m-d') == $currentDate && $e->type === 'purchase')?->quantity ?? 0);
                $dayData['purchase'][$itemId] = $p;
                $totals['purchase'][$itemId] += $p;
                if ($p > 0) $totals['count_purchase'][$itemId]++;

                // Usage
                $u = (float) ($entries->first(fn($e) => $e->item_id == $itemId && $e->date->format('Y-m-d') == $currentDate && $e->type === 'usage')?->quantity ?? 0);
                $dayData['usage'][$itemId] = $u;
                $totals['usage'][$itemId] += $u;
                if ($u > 0) $totals['count_usage'][$itemId]++;

                // Sale
                $s = (float) ($entries->first(fn($e) => $e->item_id == $itemId && $e->date->format('Y-m-d') == $currentDate && $e->type === 'sale')?->quantity ?? 0);
                $dayData['sale'][$itemId] = $s;
                $totals['sale'][$itemId] += $s;
                if ($s > 0) $totals['count_sale'][$itemId]++;

                // Stok Buku = Stok Kemarin + Beli - (Pakai + Jual)
                $prev = $runningStock[$itemId];
                $bookStock = $prev + $p - ($u + $s);
                $runningStock[$itemId] = $bookStock;
                $dayData['book_stock'][$itemId] = $bookStock;

                // Stok Opname
                $op = $opnames->first(fn($o) => $o->item_id == $itemId && $o->date->format('Y-m-d') == $currentDate);
                $dayData['opname'][$itemId] = $op ? (float) $op->actual_stock : null;

                // Selisih = Stok Nyata - Stok Buku
                if ($op !== null) {
                    $dayData['diff'][$itemId] = (float) $op->actual_stock - $bookStock;
                } else {
                    $dayData['diff'][$itemId] = null;
                }
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
        return view('livewire.petis.raw-materials');
    }
}
