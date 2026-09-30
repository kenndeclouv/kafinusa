<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Warehouse;
use App\Models\ItemCategory;
use App\Models\Item;
use App\Models\WarehouseStock;
use App\Models\StockMutation;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Traits\SortsReportCategories;

class PrintStockCirculation extends Component
{
    use SortsReportCategories;

    #[Url]
    public $month;
    
    #[Url]
    public $year;
    
    #[Url]
    public $warehouse_id;
    
    #[Url]
    public $item_category_id;

    public function mount()
    {
        abort_unless(
            auth()->user() && auth()->user()->can('reports:stock-circulation'),
            403
        );

        $this->month = $this->month ?: date('m');
        $this->year = $this->year ?: date('Y');
        
        $user = auth()->user();
        if ($user->hasRole('checker') && !$user->hasRole('kepala_checker') && !$user->hasRole('superadmin')) {
            abort_if(
                $this->warehouse_id && $this->warehouse_id != $user->warehouse_id,
                403,
                'Anda hanya dapat melihat sirkulasi dari gudang Anda sendiri.'
            );
            $this->warehouse_id = $user->warehouse_id;
        } else {
            if (!$this->warehouse_id) {
                $warehouse = Warehouse::first();
                $this->warehouse_id = $warehouse ? $warehouse->id : null;
            }
        }

        // Set default category if available
        if (!$this->item_category_id) {
            $category = ItemCategory::orderBy('name')->first();
            $this->item_category_id = $category ? $category->id : null;
        }
    }

    #[Computed]
    public function categories()
    {
        return ItemCategory::orderBy('name')->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function isProductionCategory()
    {
        if (!$this->item_category_id) return false;
        $category = ItemCategory::find($this->item_category_id);
        if (!$category) return false;
        
        $name = strtolower($category->name);
        return str_contains($name, 'tne') || str_contains($name, 'aren');
    }

    #[Computed]
    public function rawItems()
    {
        if (!$this->item_category_id) return collect();
        $category = ItemCategory::find($this->item_category_id);
        $catName = strtolower($category->name ?? '');

        $query = Item::with('category')->orderBy('name');
        if (str_contains($catName, 'tne')) {
            $query->whereHas('category', fn($q) => $q->where('name', 'LOS'));
        } elseif (str_contains($catName, 'aren')) {
            $query->where('item_category_id', $category->id);
        } else {
            $query->whereHas('category', fn($q) => $q->where('name', 'LOS'));
        }
        return $this->sortItems($query->get(), 'LOS');
    }

    #[Computed]
    public function resultItems()
    {
        if (!$this->item_category_id) return collect();
        $category = ItemCategory::find($this->item_category_id);
        $items = Item::where('item_category_id', $this->item_category_id)->get();
        if ($category) {
            return $this->sortItems($items, $category->name);
        }
        return $items;
    }

    #[Computed]
    public function items()
    {
        return $this->resultItems();
    }

    #[Computed]
    public function circulationData()
    {
        if (!$this->warehouse_id || !$this->item_category_id || !$this->month || !$this->year) {
            return null;
        }

        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        if ($this->isProductionCategory) {
            $items = $this->rawItems()->merge($this->resultItems());
        } else {
            $items = $this->items();
        }
        
        $itemIds = $items->pluck('id')->toArray();
        $itemsMapped = $items->keyBy('id');

        // 1. Get current stock
        $currentStocks = WarehouseStock::where('warehouse_id', $this->warehouse_id)
            ->whereIn('item_id', $itemIds)
            ->get()
            ->keyBy('item_id');

        // 2. Get mutations from start of month to now
        $mutations = StockMutation::where('warehouse_id', $this->warehouse_id)
            ->whereIn('item_id', $itemIds)
            ->where('mutation_date', '>=', $startOfMonth->format('Y-m-d'))
            ->get();

        $initialStocks = [];
        $runningBalances = [];
        
        // Get StockOpnames for this month
        $opnames = \App\Models\StockOpname::where('warehouse_id', $this->warehouse_id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->whereIn('item_id', $itemIds)
            ->get()
            ->keyBy('item_id');

        foreach ($items as $item) {
            $current = isset($currentStocks[$item->id]) ? $currentStocks[$item->id]->current_stock : 0;
            
            // Revert mutations to find stock at 1st of month (00:00)
            $itemMuts = $mutations->where('item_id', $item->id);
            $in = $itemMuts->where('type', 'in')->sum(fn($m) => $m->quantity * ($itemsMapped[$item->id]->weight ?? 1));
            $out = $itemMuts->where('type', 'out')->sum(fn($m) => $m->quantity * ($itemsMapped[$item->id]->weight ?? 1));
            $net = $in - $out;
            
            // If Opname exists for this month, use it directly! Otherwise, rollback.
            if ($opnames->has($item->id)) {
                $initialStocks[$item->id] = (float) $opnames[$item->id]->actual_stock;
            } else {
                $initialStocks[$item->id] = ($current * ($itemsMapped[$item->id]->weight ?? 1)) - $net;
            }
            $runningBalances[$item->id] = $initialStocks[$item->id];
        }

        // 3. Process daily data
        $dailyData = [];
        $daysInMonth = $startOfMonth->daysInMonth;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $currentDate = $startOfMonth->copy()->addDays($d - 1)->format('Y-m-d');
            
            $dailyIn = [];
            $dailyOut = [];
            $dailyBal = [];

            foreach ($items as $item) {
                // Filter mutations exactly for this day
                $dayMuts = $mutations->filter(function($m) use ($item, $currentDate) {
                    return $m->item_id === $item->id && $m->mutation_date === $currentDate;
                });
                
                $weight = $itemsMapped[$item->id]->weight ?? 1;
                
                if ($this->isProductionCategory) {
                    $inPembelian = $dayMuts->where('type', 'in')->where('transaction_category', '!=', 'Barang Jadi')->sum(fn($m) => $m->quantity * $weight);
                    $inHasilProduksi = $dayMuts->where('type', 'in')->where('transaction_category', 'Barang Jadi')->sum(fn($m) => $m->quantity * $weight);
                    $outPenjualan = $dayMuts->where('type', 'out')->where('transaction_category', '!=', 'Bahan Baku')->sum(fn($m) => $m->quantity * $weight);
                    $outPemakaian = $dayMuts->where('type', 'out')->where('transaction_category', 'Bahan Baku')->sum(fn($m) => $m->quantity * $weight);
                    
                    $in = $inPembelian + $inHasilProduksi;
                    $out = $outPenjualan + $outPemakaian;
                    
                    $dailyIn[$item->id] = [
                        'pembelian' => $inPembelian,
                        'hasil_produksi' => $inHasilProduksi
                    ];
                    $dailyOut[$item->id] = [
                        'penjualan' => $outPenjualan,
                        'pemakaian' => $outPemakaian
                    ];
                } else {
                    $in = $dayMuts->where('type', 'in')->sum(fn($m) => $m->quantity * $weight);
                    $out = $dayMuts->where('type', 'out')->sum(fn($m) => $m->quantity * $weight);
                    
                    $dailyIn[$item->id] = $in;
                    $dailyOut[$item->id] = $out;
                }
                
                // Update running balance
                $runningBalances[$item->id] += ($in - $out);
                
                $dailyIn[$item->id] = $in;
                $dailyOut[$item->id] = $out;
                $dailyBal[$item->id] = $runningBalances[$item->id];
            }

            $dailyData[$d] = [
                'in' => $dailyIn,
                'out' => $dailyOut,
                'bal' => $dailyBal,
            ];
        }

        return [
            'initial' => $initialStocks,
            'daily' => $dailyData,
        ];
    }

    public function render()
    {
        return view('livewire.reports.print-stock-circulation')
            ->title('Cetak Sirkulasi Stok Bulanan')
            ->layout('layouts.print');
    }
}
