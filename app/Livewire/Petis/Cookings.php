<?php

namespace App\Livewire\Petis;

use App\Models\Item;
use App\Models\PetisCooker;
use App\Models\PetisOpname;
use App\Models\PetisProduction;
use App\Models\PetisProductionCooker;
use App\Models\Warehouse;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Cookings extends Component
{
    public $warehouse_id;
    public $month; // 'Y-m', misal '2026-10'

    // Grid data untuk inline editing: [day => ['sessions' => [cName => val], 'result_kaleng' => x, ...]]
    public $grid = [];

    public function mount()
    {
        abort_unless(auth()->user()->can('petis_productions:read'), 403);

        // Dapur masak petis bersifat sentral (1 dapur utama)
        $defaultWarehouse = Warehouse::where('name', 'like', '%Utama%')->first() ?? Warehouse::orderBy('id')->first();
        $this->warehouse_id = $defaultWarehouse?->id;

        $this->month = Carbon::today()->format('Y-m');
        $this->loadGrid();
    }

    public function updated($property)
    {
        if ($property === 'month') {
            $this->loadGrid();
            return;
        }

        if (str_starts_with($property, 'grid.')) {
            $parts = explode('.', $property);
            if (count($parts) >= 2) {
                $day = (int) $parts[1];
                $this->saveDay($day);
            }
        }
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
        $startOfMonth = Carbon::parse($this->month . '-01')->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::parse($this->month . '-01')->endOfMonth()->format('Y-m-d');

        // Seluruh koki dapur masak petis aktif (dapur sentral)
        $activeCookers = PetisCooker::where('is_active', true)
            ->where(function ($q) {
                if ($this->warehouse_id) {
                    $q->where('warehouse_id', $this->warehouse_id)->orWhereNull('warehouse_id');
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->unique('name')
            ->values();

        $historicalCookerNames = PetisProductionCooker::whereHas('production', function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('date', [$startOfMonth, $endOfMonth]);
            if ($this->warehouse_id) {
                $q->where('warehouse_id', $this->warehouse_id);
            }
        })->distinct()->pluck('cooker_name')->toArray();

        $activeNames = $activeCookers->pluck('name')->toArray();
        $extraCookers = collect();

        foreach ($historicalCookerNames as $hName) {
            if (!in_array($hName, $activeNames)) {
                $cookerObj = PetisCooker::withTrashed()->where('name', $hName)->first();
                $extraCookers->push($cookerObj ?? (object) ['id' => null, 'name' => $hName, 'is_active' => false]);
            }
        }

        return $activeCookers->concat($extraCookers)->unique('name')->values();
    }

    public function loadGrid()
    {
        $this->grid = [];
        if (!$this->warehouse_id) return;

        $startOfMonth = Carbon::parse($this->month . '-01')->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $daysInMonth = $startOfMonth->daysInMonth;

        $cookers = $this->cookers;
        $kalengItem = $this->itemKaleng;
        $refillItem = $this->itemRefill;

        $productions = PetisProduction::with('cookers')
            ->where('warehouse_id', $this->warehouse_id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get()
            ->keyBy(fn($p) => $p->date->format('Y-m-d'));

        $opnames = PetisOpname::where('warehouse_id', $this->warehouse_id)
            ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = $startOfMonth->copy()->day($day)->format('Y-m-d');
            $prod = $productions->get($currentDate);

            $sessions = [];
            foreach ($cookers as $c) {
                $cName = $c->name;
                $val = '';
                if ($prod) {
                    $entry = $prod->cookers->firstWhere('cooker_name', $cName);
                    if ($entry && (float) $entry->sessions > 0) {
                        $val = (float) $entry->sessions;
                    }
                }
                $sessions[$cName] = $val;
            }

            $opK = null;
            $opR = null;
            if ($kalengItem) {
                $itemOp = $opnames->first(fn($o) => $o->item_id == $kalengItem->id && $o->date->format('Y-m-d') == $currentDate);
                if ($itemOp) $opK = (float) $itemOp->actual_stock;
            }
            if ($refillItem) {
                $itemOp = $opnames->first(fn($o) => $o->item_id == $refillItem->id && $o->date->format('Y-m-d') == $currentDate);
                if ($itemOp) $opR = (float) $itemOp->actual_stock;
            }

            $this->grid[$day] = [
                'day' => $day,
                'date' => $currentDate,
                'is_sunday' => $startOfMonth->copy()->day($day)->isSunday(),
                'sessions' => $sessions,
                'result_kaleng' => $prod && $prod->result_kaleng > 0 ? $prod->result_kaleng : '',
                'result_refill' => $prod && $prod->result_refill > 0 ? $prod->result_refill : '',
                'result_sisa_kg' => $prod && (float) $prod->result_sisa_kg > 0 ? (float) $prod->result_sisa_kg : '',
                'opname_kaleng' => $opK !== null ? $opK : '',
                'opname_refill' => $opR !== null ? $opR : '',
            ];
        }
    }

    public function saveDay($day)
    {
        abort_unless(auth()->user()->can('petis_productions:create'), 403);
        if (!isset($this->grid[$day])) return;

        $row = $this->grid[$day];
        $date = $row['date'];
        $kaleng = (int) ($row['result_kaleng'] ?: 0);
        $refill = (int) ($row['result_refill'] ?: 0);
        $sisaKg = (float) ($row['result_sisa_kg'] ?: 0);

        $totalSessions = 0;
        foreach ($row['sessions'] as $sess) {
            $totalSessions += (float) ($sess ?: 0);
        }

        DB::transaction(function () use ($date, $kaleng, $refill, $sisaKg, $totalSessions, $row) {
            if ($kaleng <= 0 && $refill <= 0 && $sisaKg <= 0 && $totalSessions <= 0) {
                $prod = PetisProduction::where('warehouse_id', $this->warehouse_id)->where('date', $date)->first();
                if ($prod) {
                    $prod->cookers()->delete();
                    $prod->delete();
                }
            } else {
                $prod = PetisProduction::updateOrCreate(
                    [
                        'warehouse_id' => $this->warehouse_id,
                        'date' => $date,
                    ],
                    [
                        'result_kaleng' => $kaleng,
                        'result_refill' => $refill,
                        'result_sisa_kg' => $sisaKg,
                        'user_id' => auth()->id(),
                    ]
                );

                foreach ($row['sessions'] as $cName => $sessVal) {
                    $sess = (float) ($sessVal ?: 0);
                    $cookerObj = PetisCooker::withTrashed()->where('warehouse_id', $this->warehouse_id)->where('name', $cName)->first();

                    if ($sess > 0) {
                        PetisProductionCooker::updateOrCreate(
                            [
                                'petis_production_id' => $prod->id,
                                'cooker_name' => $cName,
                            ],
                            [
                                'cooker_id' => $cookerObj?->id,
                                'sessions' => $sess,
                            ]
                        );
                    } else {
                        PetisProductionCooker::where('petis_production_id', $prod->id)
                            ->where('cooker_name', $cName)
                            ->delete();
                    }
                }
            }

            // Opname fisik Kaleng
            if ($this->itemKaleng) {
                $valK = $row['opname_kaleng'];
                if ($valK !== '' && $valK !== null) {
                    PetisOpname::updateOrCreate(
                        [
                            'warehouse_id' => $this->warehouse_id,
                            'item_id' => $this->itemKaleng->id,
                            'date' => $date,
                        ],
                        [
                            'actual_stock' => (float) $valK,
                            'user_id' => auth()->id(),
                        ]
                    );
                } else {
                    PetisOpname::where([
                        'warehouse_id' => $this->warehouse_id,
                        'item_id' => $this->itemKaleng->id,
                        'date' => $date,
                    ])->delete();
                }
            }

            // Opname fisik Refill
            if ($this->itemRefill) {
                $valR = $row['opname_refill'];
                if ($valR !== '' && $valR !== null) {
                    PetisOpname::updateOrCreate(
                        [
                            'warehouse_id' => $this->warehouse_id,
                            'item_id' => $this->itemRefill->id,
                            'date' => $date,
                        ],
                        [
                            'actual_stock' => (float) $valR,
                            'user_id' => auth()->id(),
                        ]
                    );
                } else {
                    PetisOpname::where([
                        'warehouse_id' => $this->warehouse_id,
                        'item_id' => $this->itemRefill->id,
                        'date' => $date,
                    ])->delete();
                }
            }
        });
    }

    public function render()
    {
        return view('livewire.petis.cookings');
    }
}
