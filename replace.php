<?php
$file = 'app/Livewire/Reports/PrintStockCirculation.php';
$content = file_get_contents($file);

// Replace from "// 1. Get current stock" up to "return ["
$startString = '// 1. Get current stock';
$endString = "return [\n            'initial' => \$initialStocks,";

$startPos = strpos($content, $startString);
$endPos = strpos($content, $endString);

if ($startPos === false || $endPos === false) {
    die("Markers not found");
}

$newLogic = <<<'PHP'
// 1. Get current stock
        $currentStocks = WarehouseStock::where('warehouse_id', $this->warehouse_id)
            ->whereIn('item_id', $itemIds)
            ->get()
            ->keyBy('item_id');

        // 2. Get mutations from start of month to now
        $allMutations = StockMutation::where('warehouse_id', $this->warehouse_id)
            ->whereIn('item_id', $itemIds)
            ->whereDate('created_at', '>=', $startOfMonth->format('Y-m-d'))
            ->get();

        $initialStocks = [];
        $runningBalances = [];
        
        $opnames = \App\Models\StockOpname::where('warehouse_id', $this->warehouse_id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->whereIn('item_id', $itemIds)
            ->get()
            ->keyBy('item_id');

        foreach ($items as $item) {
            $current = isset($currentStocks[$item->id]) ? $currentStocks[$item->id]->current_stock : 0;
            $multiplier = $this->isProductionCategory ? (($itemsMapped[$item->id]->weight ?? 0) / 1000) : 1;
            
            // Revert mutations to find stock at 1st of month (00:00)
            $itemMuts = $allMutations->where('item_id', $item->id);
            $in = $itemMuts->where('type', 'in')->sum(fn($m) => $m->quantity * $multiplier);
            $out = $itemMuts->where('type', 'out')->sum(fn($m) => $m->quantity * $multiplier);
            $net = $in - $out;
            
            if ($opnames->has($item->id)) {
                $pieces = (float) $opnames[$item->id]->actual_stock / ($itemsMapped[$item->id]->weight ?? 1);
                $initialStocks[$item->id] = $pieces * $multiplier;
            } else {
                $initialStocks[$item->id] = ($current * $multiplier) - $net;
            }
            $runningBalances[$item->id] = $initialStocks[$item->id];
        }

        // Filter out Opname mutations from daily processing so they don't double count
        $mutations = $allMutations->filter(function($m) {
            return $m->reference_type !== \App\Models\StockOpname::class;
        });

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
                    return $m->item_id === $item->id && $m->created_at->format('Y-m-d') === $currentDate;
                });
                
                $multiplier = $this->isProductionCategory ? (($itemsMapped[$item->id]->weight ?? 0) / 1000) : 1;
                
                if ($this->isProductionCategory) {
                    // Split logic for production
                    $inPembelian = $dayMuts->where('type', 'in')->where('transaction_category', '!=', 'Barang Jadi')->sum(fn($m) => $m->quantity * $multiplier);
                    $inHasilProduksi = $dayMuts->where('type', 'in')->where('transaction_category', 'Barang Jadi')->sum(fn($m) => $m->quantity * $multiplier);
                    $outPenjualan = $dayMuts->where('type', 'out')->where('transaction_category', '!=', 'Bahan Baku')->sum(fn($m) => $m->quantity * $multiplier);
                    $outPemakaian = $dayMuts->where('type', 'out')->where('transaction_category', 'Bahan Baku')->sum(fn($m) => $m->quantity * $multiplier);
                    
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
                    $in = $dayMuts->where('type', 'in')->sum(fn($m) => $m->quantity * $multiplier);
                    $out = $dayMuts->where('type', 'out')->sum(fn($m) => $m->quantity * $multiplier);
                    
                    $dailyIn[$item->id] = $in;
                    $dailyOut[$item->id] = $out;
                }
                
                // Update running balance
                $runningBalances[$item->id] += ($in - $out);
                
                if (!$this->isProductionCategory) {
                    $dailyIn[$item->id] = $in;
                    $dailyOut[$item->id] = $out;
                }
                
                $dailyBal[$item->id] = $runningBalances[$item->id];
            }

            $dailyData[$d] = [
                'in' => $dailyIn,
                'out' => $dailyOut,
                'bal' => $dailyBal,
            ];
        }

        PHP;

$newContent = substr($content, 0, $startPos) . $newLogic . substr($content, $endPos);
file_put_contents($file, $newContent);
echo "Done";
