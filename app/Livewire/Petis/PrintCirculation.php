<?php

namespace App\Livewire\Petis;

use App\Models\Item;
use App\Models\PetisOpname;
use App\Models\PetisProduction;
use App\Models\PetisSale;
use App\Models\Warehouse;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class PrintCirculation extends Component
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
            $this->warehouse = Warehouse::where('name', 'Gudang Petis')->first()?->id ?? Warehouse::first()?->id;
        }
    }

    #[Computed]
    public function currentWarehouse()
    {
        return Warehouse::find($this->warehouse);
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

    #[Computed]
    public function circulationData()
    {
        if (!$this->warehouse) return [];

        $startOfMonth = Carbon::parse($this->month . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($this->month . '-01')->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        $kalengItem = $this->itemKaleng;
        $refillItem = $this->itemRefill;

        $productions = PetisProduction::where('warehouse_id', $this->warehouse)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->keyBy(fn($p) => $p->date->format('Y-m-d'));

        $sales = PetisSale::where('warehouse_id', $this->warehouse)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        $opnames = PetisOpname::where('warehouse_id', $this->warehouse)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        $initialKaleng = 0;
        $initialRefill = 0;

        $lastMonthEnd = $startOfMonth->copy()->subDay()->format('Y-m-d');
        if ($kalengItem) {
            $prevOpK = PetisOpname::where('warehouse_id', $this->warehouse)
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
            $prevOpR = PetisOpname::where('warehouse_id', $this->warehouse)
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

            $prod = $productions->get($currentDate);
            $inKaleng = $prod ? (int) $prod->result_kaleng : 0;
            $inRefill = $prod ? (int) $prod->result_refill : 0;
            $totalProdKaleng += $inKaleng;
            $totalProdRefill += $inRefill;

            $daySales = $sales->where('date', $currentDate);
            $outKaleng = 0;
            $outRefill = 0;
            foreach ($daySales as $sale) {
                $outKaleng += $sale->sold_kaleng;
                $outRefill += $sale->sold_refill;
            }
            $totalSoldKaleng += $outKaleng;
            $totalSoldRefill += $outRefill;

            $bookKaleng = $runningKaleng + $inKaleng - $outKaleng;
            $bookRefill = $runningRefill + $inRefill - $outRefill;
            $runningKaleng = $bookKaleng;
            $runningRefill = $bookRefill;

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

            $diffKaleng = $opKaleng !== null ? $opKaleng - $bookKaleng : null;
            $diffRefill = $opRefill !== null ? $opRefill - $bookRefill : null;
            $diffKalengKg = $diffKaleng !== null ? $diffKaleng * PetisProduction::WEIGHT_KALENG_KG : null;
            $diffRefillKg = $diffRefill !== null ? $diffRefill * PetisProduction::WEIGHT_REFILL_KG : null;
            $totalDiffKg = ($diffKalengKg !== null || $diffRefillKg !== null) ? (($diffKalengKg ?? 0) + ($diffRefillKg ?? 0)) : null;

            $days[$day] = [
                'day' => $day,
                'date' => $currentDate,
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

    public function render()
    {
        return view('livewire.petis.print-circulation')
            ->title('Cetak Rekap Sirkulasi Petis')
            ->layout('layouts.print');
    }
}
