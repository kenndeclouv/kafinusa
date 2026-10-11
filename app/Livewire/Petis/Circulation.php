<?php

namespace App\Livewire\Petis;

use App\Models\Item;
use App\Models\PetisOpname;
use App\Models\PetisProduction;
use App\Models\PetisSale;
use App\Models\Warehouse;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Circulation extends Component
{
    public $warehouse_id;
    public $month; // 'Y-m', misal '2026-10'
    public $activeTab = 'rekap'; // rekap | sales

    // Modal state for input sales (muatan & retur)
    public $sales_date;
    public $sales_data = []; // [salesman_name => ['muatan_kaleng' => x, 'muatan_refill' => y, 'retur_kaleng' => z, 'retur_refill' => w]]
    public $new_salesman_name = '';

    // Modal state for initial stock
    public $initial_kaleng = 0;
    public $initial_refill = 0;

    // Modal state for opname fisik
    public $opname_date;
    public $actual_kaleng = '';
    public $actual_refill = '';
    public $opname_notes = '';

    public function mount()
    {
        abort_unless(auth()->user()->can('petis_productions:read'), 403);

        $defaultWarehouse = Warehouse::orderBy('name')->first();
        if (
            auth()->user()->hasRole('checker') &&
            !auth()->user()->hasRole('kepala_checker') &&
            !auth()->user()->hasRole('superadmin')
        ) {
            $this->warehouse_id = auth()->user()->warehouse_id ?? $defaultWarehouse?->id;
        } else {
            $this->warehouse_id = $defaultWarehouse?->id;
        }

        $this->month = Carbon::today()->format('Y-m');
        $this->sales_date = Carbon::today()->format('Y-m-d');
        $this->opname_date = Carbon::today()->format('Y-m-d');
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
    public function itemKaleng()
    {
        return Item::where('item_category_id', 5)->where('name', 'KI')->first()
            ?? Item::where('item_category_id', 5)->where('code', 'like', '%032%')->first();
    }

    #[Computed]
    public function itemRefill()
    {
        return Item::where('item_category_id', 5)->where('name', 'Rf')->first()
            ?? Item::where('item_category_id', 5)->where('code', 'like', '%033%')->first();
    }

    /**
     * Daftar nama salesman / driver petis
     */
    #[Computed]
    public function salesmen()
    {
        $defaultNames = ['Ikhsan', 'Sutekno', 'Kardi', 'Basuki', 'Budi', 'Bayu', 'Juari', 'Nurul', 'Kuladi', 'Agus'];

        $existingNames = PetisSale::where('warehouse_id', $this->warehouse_id)
            ->distinct()
            ->pluck('salesman_name')
            ->toArray();

        $allNames = array_unique(array_merge($defaultNames, $existingNames));
        return array_values($allNames);
    }

    /**
     * Menghitung seluruh sirkulasi bulanan petis jadi (Kaleng & Refill)
     */
    #[Computed]
    public function circulationData()
    {
        if (!$this->warehouse_id) {
            return [];
        }

        $startOfMonth = Carbon::parse($this->month . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($this->month . '-01')->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        $kalengItem = $this->itemKaleng;
        $refillItem = $this->itemRefill;

        // 1. Ambil produksi bulan ini
        $productions = PetisProduction::where('warehouse_id', $this->warehouse_id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->keyBy(fn($p) => $p->date->format('Y-m-d'));

        // 2. Ambil penjualan bulan ini
        $sales = PetisSale::where('warehouse_id', $this->warehouse_id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        // 3. Ambil opname bulan ini
        $opnames = PetisOpname::where('warehouse_id', $this->warehouse_id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        // 4. Hitung Stok Awal bulan ini
        $initialKaleng = 0;
        $initialRefill = 0;

        $lastMonthEnd = $startOfMonth->copy()->subDay()->format('Y-m-d');
        if ($kalengItem) {
            $prevOpK = PetisOpname::where('warehouse_id', $this->warehouse_id)
                ->where('item_id', $kalengItem->id)
                ->where('date', '<=', $lastMonthEnd)
                ->orderBy('date', 'desc')
                ->first();
            if ($prevOpK) {
                $initialKaleng = (float) $prevOpK->actual_stock;
            } else {
                $day1OpK = $opnames->where('item_id', $kalengItem->id)->where('date', $startOfMonth->format('Y-m-d'))->first();
                $initialKaleng = $day1OpK ? (float) $day1OpK->actual_stock : 0;
            }
        }
        if ($refillItem) {
            $prevOpR = PetisOpname::where('warehouse_id', $this->warehouse_id)
                ->where('item_id', $refillItem->id)
                ->where('date', '<=', $lastMonthEnd)
                ->orderBy('date', 'desc')
                ->first();
            if ($prevOpR) {
                $initialRefill = (float) $prevOpR->actual_stock;
            } else {
                $day1OpR = $opnames->where('item_id', $refillItem->id)->where('date', $startOfMonth->format('Y-m-d'))->first();
                $initialRefill = $day1OpR ? (float) $day1OpR->actual_stock : 0;
            }
        }

        $runningKaleng = $initialKaleng;
        $runningRefill = $initialRefill;

        $days = [];
        $totalProdKaleng = 0;
        $totalProdRefill = 0;
        $totalSoldKaleng = 0;
        $totalSoldRefill = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = $startOfMonth->copy()->day($day)->format('Y-m-d');

            // Masuk (Produksi)
            $prod = $productions->get($currentDate);
            $inKaleng = $prod ? (int) $prod->result_kaleng : 0;
            $inRefill = $prod ? (int) $prod->result_refill : 0;
            $totalProdKaleng += $inKaleng;
            $totalProdRefill += $inRefill;

            // Keluar (Penjualan Bersih)
            $daySales = $sales->where('date', $currentDate);
            $outKaleng = 0;
            $outRefill = 0;
            foreach ($daySales as $sale) {
                $outKaleng += $sale->sold_kaleng;
                $outRefill += $sale->sold_refill;
            }
            $totalSoldKaleng += $outKaleng;
            $totalSoldRefill += $outRefill;

            // Stok Buku = Stok Kemarin + Masuk - Keluar
            $bookKaleng = $runningKaleng + $inKaleng - $outKaleng;
            $bookRefill = $runningRefill + $inRefill - $outRefill;
            $runningKaleng = $bookKaleng;
            $runningRefill = $bookRefill;

            // Stok Opname Fisik
            $opKaleng = null;
            $opRefill = null;
            if ($kalengItem) {
                $opK = $opnames->first(fn($o) => $o->item_id == $kalengItem->id && $o->date->format('Y-m-d') == $currentDate);
                if ($opK) $opKaleng = (float) $opK->actual_stock;
            }
            if ($refillItem) {
                $opR = $opnames->first(fn($o) => $o->item_id == $refillItem->id && $o->date->format('Y-m-d') == $currentDate);
                if ($opR) $opRefill = (float) $opR->actual_stock;
            }

            // Selisih = Opname - Stok Buku
            $diffKaleng = null;
            $diffRefill = null;
            $diffKalengKg = null;
            $diffRefillKg = null;
            $totalDiffKg = null;

            if ($opKaleng !== null) {
                $diffKaleng = $opKaleng - $bookKaleng;
                $diffKalengKg = $diffKaleng * PetisProduction::WEIGHT_KALENG_KG;
            }
            if ($opRefill !== null) {
                $diffRefill = $opRefill - $bookRefill;
                $diffRefillKg = $diffRefill * PetisProduction::WEIGHT_REFILL_KG;
            }
            if ($diffKalengKg !== null || $diffRefillKg !== null) {
                $totalDiffKg = ($diffKalengKg ?? 0) + ($diffRefillKg ?? 0);
            }

            $days[$day] = [
                'day' => $day,
                'date' => $currentDate,
                'is_sunday' => $startOfMonth->copy()->day($day)->isSunday(),
                'in_kaleng' => $inKaleng,
                'in_refill' => $inRefill,
                'out_kaleng' => $outKaleng,
                'out_refill' => $outRefill,
                'book_kaleng' => $bookKaleng,
                'book_refill' => $bookRefill,
                'opname_kaleng' => $opKaleng,
                'opname_refill' => $opRefill,
                'diff_kaleng' => $diffKaleng,
                'diff_refill' => $diffRefill,
                'diff_kaleng_kg' => $diffKalengKg,
                'diff_refill_kg' => $diffRefillKg,
                'total_diff_kg' => $totalDiffKg,
            ];
        }

        return [
            'days' => $days,
            'initial_kaleng' => $initialKaleng,
            'initial_refill' => $initialRefill,
            'final_kaleng' => $runningKaleng,
            'final_refill' => $runningRefill,
            'total_prod_kaleng' => $totalProdKaleng,
            'total_prod_refill' => $totalProdRefill,
            'total_sold_kaleng' => $totalSoldKaleng,
            'total_sold_refill' => $totalSoldRefill,
        ];
    }

    public function openSalesModal($date = null)
    {
        Gate::authorize('petis_productions:create');
        $this->sales_date = $date ?? Carbon::today()->format('Y-m-d');
        $this->loadSalesDataIntoModal();
        $this->modal('sales-modal')->show();
    }

    public function updatedSalesDate()
    {
        $this->loadSalesDataIntoModal();
    }

    protected function loadSalesDataIntoModal()
    {
        $existingSales = PetisSale::where('warehouse_id', $this->warehouse_id)
            ->where('date', $this->sales_date)
            ->get()
            ->keyBy('salesman_name');

        $this->sales_data = [];
        foreach ($this->salesmen as $name) {
            if ($existingSales->has($name)) {
                $s = $existingSales[$name];
                $this->sales_data[$name] = [
                    'muatan_kaleng' => $s->muatan_kaleng ?: '',
                    'muatan_refill' => $s->muatan_refill ?: '',
                    'retur_kaleng'  => $s->retur_kaleng ?: '',
                    'retur_refill'  => $s->retur_refill ?: '',
                ];
            } else {
                $this->sales_data[$name] = [
                    'muatan_kaleng' => '',
                    'muatan_refill' => '',
                    'retur_kaleng'  => '',
                    'retur_refill'  => '',
                ];
            }
        }
    }

    public function addSalesman()
    {
        $name = trim($this->new_salesman_name);
        if ($name && !isset($this->sales_data[$name])) {
            $this->sales_data[$name] = [
                'muatan_kaleng' => '',
                'muatan_refill' => '',
                'retur_kaleng'  => '',
                'retur_refill'  => '',
            ];
            $this->new_salesman_name = '';
        }
    }

    public function saveSales()
    {
        Gate::authorize('petis_productions:create');

        $this->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'sales_date' => 'required|date',
        ]);

        foreach ($this->sales_data as $sName => $row) {
            $mk = (int) ($row['muatan_kaleng'] ?: 0);
            $mr = (int) ($row['muatan_refill'] ?: 0);
            $rk = (int) ($row['retur_kaleng'] ?: 0);
            $rr = (int) ($row['retur_refill'] ?: 0);

            if ($mk > 0 || $mr > 0 || $rk > 0 || $rr > 0) {
                PetisSale::updateOrCreate(
                    [
                        'warehouse_id' => $this->warehouse_id,
                        'date' => $this->sales_date,
                        'salesman_name' => $sName,
                    ],
                    [
                        'muatan_kaleng' => $mk,
                        'muatan_refill' => $mr,
                        'retur_kaleng'  => $rk,
                        'retur_refill'  => $rr,
                        'user_id' => auth()->id(),
                    ]
                );
            } else {
                PetisSale::where([
                    'warehouse_id' => $this->warehouse_id,
                    'date' => $this->sales_date,
                    'salesman_name' => $sName,
                ])->delete();
            }
        }

        $this->modal('sales-modal')->close();

        Flux::toast(
            heading: 'Penjualan Disimpan',
            text: "Data muatan dan retur sales tanggal {$this->sales_date} berhasil disimpan.",
            variant: 'success'
        );
    }

    public function openOpnameModal($date = null)
    {
        Gate::authorize('petis_productions:create');
        $this->opname_date = $date ?? Carbon::today()->format('Y-m-d');
        $this->actual_kaleng = '';
        $this->actual_refill = '';
        $this->opname_notes = '';

        if ($this->itemKaleng) {
            $opK = PetisOpname::where('warehouse_id', $this->warehouse_id)
                ->where('item_id', $this->itemKaleng->id)
                ->where('date', $this->opname_date)
                ->first();
            $this->actual_kaleng = $opK ? (float) $opK->actual_stock : '';
        }
        if ($this->itemRefill) {
            $opR = PetisOpname::where('warehouse_id', $this->warehouse_id)
                ->where('item_id', $this->itemRefill->id)
                ->where('date', $this->opname_date)
                ->first();
            $this->actual_refill = $opR ? (float) $opR->actual_stock : '';
        }

        $this->modal('opname-modal')->show();
    }

    public function saveOpname()
    {
        Gate::authorize('petis_productions:create');

        $this->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'opname_date' => 'required|date',
        ]);

        if ($this->itemKaleng) {
            if ($this->actual_kaleng !== '' && $this->actual_kaleng !== null) {
                PetisOpname::updateOrCreate(
                    [
                        'warehouse_id' => $this->warehouse_id,
                        'item_id' => $this->itemKaleng->id,
                        'date' => $this->opname_date,
                    ],
                    [
                        'actual_stock' => (float) $this->actual_kaleng,
                        'notes' => $this->opname_notes,
                        'user_id' => auth()->id(),
                    ]
                );
            } else {
                PetisOpname::where([
                    'warehouse_id' => $this->warehouse_id,
                    'item_id' => $this->itemKaleng->id,
                    'date' => $this->opname_date,
                ])->delete();
            }
        }

        if ($this->itemRefill) {
            if ($this->actual_refill !== '' && $this->actual_refill !== null) {
                PetisOpname::updateOrCreate(
                    [
                        'warehouse_id' => $this->warehouse_id,
                        'item_id' => $this->itemRefill->id,
                        'date' => $this->opname_date,
                    ],
                    [
                        'actual_stock' => (float) $this->actual_refill,
                        'notes' => $this->opname_notes,
                        'user_id' => auth()->id(),
                    ]
                );
            } else {
                PetisOpname::where([
                    'warehouse_id' => $this->warehouse_id,
                    'item_id' => $this->itemRefill->id,
                    'date' => $this->opname_date,
                ])->delete();
            }
        }

        $this->modal('opname-modal')->close();

        Flux::toast(
            heading: 'Stok Opname Disimpan',
            text: "Stok fisik petis kaleng & refill tanggal {$this->opname_date} berhasil disimpan.",
            variant: 'success'
        );
    }

    public function openInitialStockModal()
    {
        Gate::authorize('petis_productions:create');
        $firstDate = Carbon::parse($this->month . '-01')->format('Y-m-d');
        $this->initial_kaleng = '';
        $this->initial_refill = '';

        if ($this->itemKaleng) {
            $opK = PetisOpname::where('warehouse_id', $this->warehouse_id)
                ->where('item_id', $this->itemKaleng->id)
                ->where('date', $firstDate)
                ->first();
            $this->initial_kaleng = $opK ? (float) $opK->actual_stock : '';
        }
        if ($this->itemRefill) {
            $opR = PetisOpname::where('warehouse_id', $this->warehouse_id)
                ->where('item_id', $this->itemRefill->id)
                ->where('date', $firstDate)
                ->first();
            $this->initial_refill = $opR ? (float) $opR->actual_stock : '';
        }

        $this->modal('initial-stock-modal')->show();
    }

    public function saveInitialStock()
    {
        Gate::authorize('petis_productions:create');
        $firstDate = Carbon::parse($this->month . '-01')->format('Y-m-d');

        if ($this->itemKaleng && $this->initial_kaleng !== '' && $this->initial_kaleng !== null) {
            PetisOpname::updateOrCreate(
                [
                    'warehouse_id' => $this->warehouse_id,
                    'item_id' => $this->itemKaleng->id,
                    'date' => $firstDate,
                ],
                [
                    'actual_stock' => (float) $this->initial_kaleng,
                    'notes' => 'Stok Awal Bulan Kaleng',
                    'user_id' => auth()->id(),
                ]
            );
        }

        if ($this->itemRefill && $this->initial_refill !== '' && $this->initial_refill !== null) {
            PetisOpname::updateOrCreate(
                [
                    'warehouse_id' => $this->warehouse_id,
                    'item_id' => $this->itemRefill->id,
                    'date' => $firstDate,
                ],
                [
                    'actual_stock' => (float) $this->initial_refill,
                    'notes' => 'Stok Awal Bulan Refill',
                    'user_id' => auth()->id(),
                ]
            );
        }

        $this->modal('initial-stock-modal')->close();

        Flux::toast(
            heading: 'Stok Awal Disimpan',
            text: "Stok awal petis berhasil diperbarui.",
            variant: 'success'
        );
    }

    public function render()
    {
        return view('livewire.petis.circulation');
    }
}
