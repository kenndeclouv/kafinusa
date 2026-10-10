<?php
$file = 'app/Livewire/Inventory/StockOpname.php';
$content = file_get_contents($file);

$start = 'foreach ($this->items as $item) {';
$end = 'Flux::toast(';

$startPos = strpos($content, $start);
$endPos = strpos($content, $end);

if ($startPos === false || $endPos === false) {
    die("Markers not found");
}

$newLogic = <<<'PHP'
foreach ($this->items as $item) {
            if (!isset($this->actualStocks[$item->id]) || $this->actualStocks[$item->id] === '') {
                continue;
            }

            $actual = (float) $this->actualStocks[$item->id] * ($item->weight ?? 1);
            
            $existingOpname = StockOpnameModel::where([
                'warehouse_id' => $this->warehouse_id,
                'item_id' => $item->id,
                'month' => $this->month,
                'year' => $this->year,
            ])->first();

            $oldActual = $existingOpname ? (float) $existingOpname->actual_stock : 0;
            $delta = $actual - $oldActual;

            // If no existing opname, we use current system stock to find the initial diff
            $sys = $existingOpname ? (float) $existingOpname->system_stock : ($this->getSystemStock($item->id) * ($item->weight ?? 1));

            $opname = StockOpnameModel::updateOrCreate(
                [
                    'warehouse_id' => $this->warehouse_id,
                    'item_id' => $item->id,
                    'month' => $this->month,
                    'year' => $this->year,
                ],
                [
                    'system_stock' => $sys,
                    'actual_stock' => $actual,
                    'difference' => $actual - $sys,
                    'notes' => $this->notes[$item->id] ?? null,
                    'recorded_by_id' => auth()->id(),
                ]
            );

            // Only adjust realtime stock if opname is for CURRENT month and there is a change
            if ($isCurrentMonth && ($delta != 0 || !$existingOpname)) {
                
                // Adjust WarehouseStock by the DELTA (change in actual stock)
                if ($existingOpname) {
                    $stock = WarehouseStock::where('warehouse_id', $this->warehouse_id)
                        ->where('item_id', $item->id)->first();
                    if ($stock) {
                        $stock->current_stock += ($delta / ($item->weight ?? 1));
                        $stock->physical_stock += ($delta / ($item->weight ?? 1));
                        $stock->save();
                    }
                } else {
                    // First time creating opname for this month, adjust by actual - sys
                    $diffPieces = ($actual - $sys) / ($item->weight ?? 1);
                    WarehouseStock::updateOrCreate(
                        [
                            'warehouse_id' => $this->warehouse_id,
                            'item_id' => $item->id,
                        ],
                        [
                            'current_stock' => $this->actualStocks[$item->id],
                            'physical_stock' => $this->actualStocks[$item->id]
                        ]
                    );
                }

                // Update or Create the ONE adjustment mutation for this opname
                $mutation = StockMutation::firstOrNew([
                    'reference_type' => \App\Models\StockOpname::class,
                    'reference_id' => $opname->id,
                    'item_id' => $item->id,
                    'warehouse_id' => $this->warehouse_id,
                ]);

                $mutation->type = $opname->difference > 0 ? 'in' : 'out';
                $mutation->quantity = abs($opname->difference) / ($item->weight ?? 1);
                $mutation->mutation_date = Carbon::create($this->year, $this->month, 1)->format('Y-m-d');
                $mutation->transaction_category = 'Penyesuaian Opname';
                $mutation->notes = 'Stok Awal (Opname): ' . ($this->notes[$item->id] ?? '-');
                
                if (!$mutation->exists) {
                    $mutation->user_id = auth()->id();
                    $mutation->created_at = Carbon::create($this->year, $this->month, 1)->startOfDay();
                }
                $mutation->save();
            }
        }

        Flux::toast(
PHP;

$content = substr($content, 0, $startPos) . $newLogic . substr($content, $endPos + 12);
file_put_contents($file, $content);
echo "Opname logic updated";
