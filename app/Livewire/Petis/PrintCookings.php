<?php

namespace App\Livewire\Petis;

use App\Models\Item;
use App\Models\PetisCooker;
use App\Models\PetisOpname;
use App\Models\PetisProduction;
use App\Models\PetisProductionCooker;
use App\Models\Warehouse;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class PrintCookings extends Component
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
    public function cookers()
    {
        if (!$this->warehouse) return collect();

        $startOfMonth = Carbon::parse($this->month . '-01')->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::parse($this->month . '-01')->endOfMonth()->format('Y-m-d');

        $activeCookers = PetisCooker::where('warehouse_id', $this->warehouse)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $historicalCookerNames = PetisProductionCooker::whereHas('production', function ($q) use ($startOfMonth, $endOfMonth) {
            $q->where('warehouse_id', $this->warehouse)
              ->whereBetween('date', [$startOfMonth, $endOfMonth]);
        })->distinct()->pluck('cooker_name')->toArray();

        $activeNames = $activeCookers->pluck('name')->toArray();
        $extraCookers = collect();

        foreach ($historicalCookerNames as $hName) {
            if (!in_array($hName, $activeNames)) {
                $cookerObj = PetisCooker::withTrashed()->where('warehouse_id', $this->warehouse)->where('name', $hName)->first();
                $extraCookers->push($cookerObj ?? (object) ['id' => null, 'name' => $hName, 'is_active' => false]);
            }
        }

        return $activeCookers->concat($extraCookers);
    }

    #[Computed]
    public function monthlyData()
    {
        if (!$this->warehouse) return [];

        $startOfMonth = Carbon::parse($this->month . '-01')->startOfMonth();
        $endOfMonth = Carbon::parse($this->month . '-01')->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        $cookers = $this->cookers;
        $kalengItem = $this->itemKaleng;
        $refillItem = $this->itemRefill;

        $productions = PetisProduction::with('cookers')
            ->where('warehouse_id', $this->warehouse)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->keyBy(fn($p) => $p->date->format('Y-m-d'));

        $opnames = PetisOpname::where('warehouse_id', $this->warehouse)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        $days = [];
        $totalCookerSessions = array_fill_keys($cookers->pluck('name')->toArray(), 0);
        $totalCookerDays = array_fill_keys($cookers->pluck('name')->toArray(), 0);

        $grandTotalSessions = 0;
        $grandTotalKaleng = 0;
        $grandTotalRefill = 0;
        $grandTotalSisaKg = 0;
        $grandTotalKg = 0;
        $totalDaysCooking = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = $startOfMonth->copy()->day($day)->format('Y-m-d');
            $prod = $productions->get($currentDate);

            $sessions = [];
            $totalDaySessions = 0;

            foreach ($cookers as $c) {
                $cName = $c->name;
                $s = 0;
                if ($prod) {
                    $entry = $prod->cookers->firstWhere('cooker_name', $cName);
                    $s = $entry ? (float) $entry->sessions : 0;
                }
                $sessions[$cName] = $s;
                $totalDaySessions += $s;
                $totalCookerSessions[$cName] += $s;
                if ($s > 0) $totalCookerDays[$cName]++;
            }

            $kaleng = $prod ? (int) $prod->result_kaleng : 0;
            $refill = $prod ? (int) $prod->result_refill : 0;
            $sisaKg = $prod ? (float) $prod->result_sisa_kg : 0;

            $kgKaleng = $kaleng * PetisProduction::WEIGHT_KALENG_KG;
            $kgRefill = $refill * PetisProduction::WEIGHT_REFILL_KG;
            $totalKg = $kgKaleng + $kgRefill + $sisaKg;
            $avgKgPerSession = $totalDaySessions > 0 ? round($totalKg / $totalDaySessions, 2) : 0;

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

            if ($totalDaySessions > 0 || $kaleng > 0 || $refill > 0) {
                $totalDaysCooking++;
            }

            $grandTotalSessions += $totalDaySessions;
            $grandTotalKaleng += $kaleng;
            $grandTotalRefill += $refill;
            $grandTotalSisaKg += $sisaKg;
            $grandTotalKg += $totalKg;

            $days[$day] = [
                'day' => $day,
                'date' => $currentDate,
                'sessions' => $sessions,
                'total_sessions' => $totalDaySessions,
                'kaleng' => $kaleng,
                'refill' => $refill,
                'sisa_kg' => $sisaKg,
                'kg_kaleng' => $kgKaleng,
                'kg_refill' => $kgRefill,
                'total_kg' => $totalKg,
                'avg_kg_per_session' => $avgKgPerSession,
                'opname_kaleng' => $opKaleng,
                'opname_refill' => $opRefill,
            ];
        }

        $overallAvg = $grandTotalSessions > 0 ? round($grandTotalKg / $grandTotalSessions, 2) : 0;

        return [
            'days' => $days,
            'totalCookerSessions' => $totalCookerSessions,
            'totalCookerDays' => $totalCookerDays,
            'grandTotalSessions' => $grandTotalSessions,
            'grandTotalKaleng' => $grandTotalKaleng,
            'grandTotalRefill' => $grandTotalRefill,
            'grandTotalSisaKg' => $grandTotalSisaKg,
            'grandTotalKg' => $grandTotalKg,
            'overallAvg' => $overallAvg,
            'totalDaysCooking' => $totalDaysCooking,
        ];
    }

    public function render()
    {
        return view('livewire.petis.print-cookings')
            ->title('Cetak Proses Masakan Petis')
            ->layout('layouts.print');
    }
}
