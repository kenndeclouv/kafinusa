<?php

namespace App\Livewire\OrderBooks;

use App\Models\OrderBook;
use App\Models\Order;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanItem;
use App\Models\ShipmentBatch;
use App\Models\WarehouseStock;
use App\Models\StockMutation;
use App\Models\Warehouse;
use App\Models\Item;
use App\Models\PetisSale;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Flux;

class PrintShipments extends Component
{
    public OrderBook $orderBook;
    public ?ShipmentPlan $plan;

    public array $returns = [];
    public array $checkG = [];
    public array $checkS = [];
    public string $returnDate = '';

    public function mount(OrderBook $orderBook)
    {
        abort_unless(
            auth()->user() && auth()->user()->can('order_books:manage-shipments'),
            403
        );

        $this->orderBook = $orderBook;
        $this->returnDate = now()->format('Y-m-d');

        $this->plan = $orderBook->shipmentPlan()->with([
            'items.orderItem.item.category',
            'items.orderItem.order.customer',
            'batches.warehouse',
        ])->first();

        if (!$this->plan) {
            $this->redirect(route('order-books.shipments', $orderBook), navigate: true);
            return;
        }

        // Initialize state
        foreach ($this->plan->items as $planItem) {
            if (!$planItem->orderItem) continue;
            $itemId = $planItem->orderItem->item_id;
            $batch = $planItem->batch_number;

            if (!isset($this->returns[$itemId][$batch])) $this->returns[$itemId][$batch] = 0;
            if (!isset($this->checkG[$itemId][$batch])) $this->checkG[$itemId][$batch] = false;
            if (!isset($this->checkS[$itemId][$batch])) $this->checkS[$itemId][$batch] = false;

            $this->returns[$itemId][$batch] += $planItem->return_quantity;
            $this->checkG[$itemId][$batch] = $this->checkG[$itemId][$batch] || $planItem->is_checked_g;
            $this->checkS[$itemId][$batch] = $this->checkS[$itemId][$batch] || $planItem->is_checked_s;
        }

        // Change 0 to empty string for cleaner UI
        foreach ($this->returns as $itemId => $batches) {
            foreach ($batches as $batch => $val) {
                if ($val == 0) $this->returns[$itemId][$batch] = '';
            }
        }
    }

    public function toggleCheck(int $itemId, int $batch, string $type)
    {
        $field = $type === 'G' ? 'checkG' : 'checkS';
        $currentValue = $this->$field[$itemId][$batch] ?? false;
        
        $temp = $this->$field;
        $temp[$itemId][$batch] = !$currentValue;
        $this->$field = $temp;

        // Update DB in transaction
        DB::transaction(function () use ($itemId, $batch, $type, $currentValue) {
            $planItems = $this->plan->items()->whereHas('orderItem', function($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })->where('batch_number', $batch)->get();

            foreach ($planItems as $pi) {
                if ($type === 'G') $pi->update(['is_checked_g' => !$currentValue]);
                else $pi->update(['is_checked_s' => !$currentValue]);
            }
        });
    }

    public function updatedReturns($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $itemId = (int) $parts[0];
            $batch = (int) $parts[1];
            $retValue = (int) ($value ?: 0);

            try {
                $delta = $this->applyReturnChange($itemId, $batch, $retValue);

                if ($delta !== 0) {
                    $batchObj = $this->plan->batches->where('batch_number', $batch)->first();
                    $isShipped = $batchObj && $batchObj->status === 'shipped';

                    Flux::toast(
                        heading: 'Retur Disimpan',
                        text: $isShipped
                            ? "Retur dicatat: stok gudang " . ($delta > 0 ? "+{$delta}" : "{$delta}") . "."
                            : "Jumlah retur diperbarui.",
                        variant: 'success'
                    );
                }
            } catch (\Throwable $e) {
                \Log::error('Gagal memperbarui retur: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                Flux::toast(
                    heading: 'Gagal Menyimpan Retur',
                    text: 'Terjadi kesalahan: ' . $e->getMessage(),
                    variant: 'danger'
                );
            }
        }
    }

    public function saveAllReturns(): void
    {
        try {
            $totalDeltas = DB::transaction(function () {
                $deltas = 0;
                foreach ($this->returns as $itemId => $batches) {
                    foreach ($batches as $batchNumber => $val) {
                        $retVal = (int) ($val ?: 0);
                        $delta = $this->applyReturnChange((int)$itemId, (int)$batchNumber, $retVal);
                        $deltas += abs($delta);
                    }
                }
                return $deltas;
            });

            Flux::toast(
                heading: 'Retur Berhasil Disimpan',
                text: $totalDeltas > 0
                    ? 'Stok gudang otomatis disesuaikan dan mutasi retur telah dicatat.'
                    : 'Data retur telah tersimpan rapi.',
                variant: 'success'
            );
        } catch (\Throwable $e) {
            \Log::error('Gagal menyimpan retur: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            Flux::toast(
                heading: 'Gagal Menyimpan Retur',
                text: 'Terjadi kesalahan sistem: ' . $e->getMessage(),
                variant: 'danger'
            );
        }
    }

    protected function applyReturnChange(int $itemId, int $batchNumber, int $newRetValue): int
    {
        return DB::transaction(function () use ($itemId, $batchNumber, $newRetValue) {
            $planItems = $this->plan->items()->whereHas('orderItem', function($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })->where('batch_number', $batchNumber)->get();

            $maxMuatan = (int) $planItems->sum('quantity');
            $newRetValue = min(max(0, $newRetValue), $maxMuatan);

            $oldRetValue = (int) $planItems->sum('return_quantity');
            $delta = $newRetValue - $oldRetValue;

            if ($delta === 0) {
                return 0;
            }

            // Distribute new return quantity across ShipmentPlanItems
            $remaining = $newRetValue;
            foreach ($planItems as $pi) {
                if ($remaining >= $pi->quantity) {
                    $pi->update(['return_quantity' => $pi->quantity]);
                    $remaining -= $pi->quantity;
                } else {
                    $pi->update(['return_quantity' => $remaining]);
                    $remaining = 0;
                }
            }

            // Sync local returns property
            $this->returns[$itemId][$batchNumber] = $newRetValue ?: '';

            // If batch is already shipped, automatically adjust warehouse stock and record mutation
            $batchObj = $this->plan->batches->where('batch_number', $batchNumber)->first();
            if ($batchObj && $batchObj->status === 'shipped') {
                $this->syncStockAndMutationForReturn($itemId, $batchObj, $delta);
            }

            return $delta;
        });
    }

    protected function syncStockAndMutationForReturn(int $itemId, ShipmentBatch $batchObj, int $delta): void
    {
        if ($delta === 0) return;

        $item = Item::with('category')->find($itemId);
        if (!$item) return;

        $isPetis = ($item->item_category_id == 5 || strtolower($item->category?->name ?? '') === 'petis');
        $petisWarehouse = Warehouse::where('name', 'Gudang Petis')->first();
        $petisWarehouseId = $petisWarehouse?->id;

        $effectiveWarehouseId = ($isPetis && $petisWarehouseId)
            ? $petisWarehouseId
            : ($batchObj->warehouse_id ?? Warehouse::first()?->id);

        if (!$effectiveWarehouseId) return;

        $targetDate = $this->returnDate ?: now()->format('Y-m-d');
        $batchDate = $batchObj->shipment_date ? $batchObj->shipment_date->format('Y-m-d') : $this->orderBook->book_date->format('Y-m-d');
        $salesmanName = $this->orderBook->employee?->name ?? 'Sales';

        DB::transaction(function () use ($itemId, $effectiveWarehouseId, $batchObj, $delta, $targetDate, $batchDate, $salesmanName, $isPetis, $petisWarehouseId) {
            $stock = WarehouseStock::firstOrCreate(
                ['warehouse_id' => $effectiveWarehouseId, 'item_id' => $itemId],
                ['current_stock' => 0, 'physical_stock' => 0]
            );
            $stock = WarehouseStock::where('id', $stock->id)->lockForUpdate()->first();

            if ($delta > 0) {
                // Barang kembali ke gudang: Stok bertambah (+)
                $stock->update([
                    'current_stock' => $stock->current_stock + $delta,
                    'physical_stock' => $stock->physical_stock + $delta,
                ]);

                $mutation = new StockMutation([
                    'item_id' => $itemId,
                    'warehouse_id' => $effectiveWarehouseId,
                    'user_id' => auth()->id() ?? 1,
                    'type' => 'in',
                    'quantity' => $delta,
                    'mutation_date' => $targetDate,
                    'transaction_category' => 'Retur',
                    'reference_type' => ShipmentBatch::class,
                    'reference_id' => $batchObj->id,
                    'sender_name' => "Sales: {$salesmanName}",
                    'notes' => "Retur Pengiriman Muatan {$batchObj->batch_number} (Pasar {$this->orderBook->market->name})",
                ]);
                $mutation->created_at = $targetDate . ' ' . now()->format('H:i:s');
                $mutation->save();
            } else {
                // Koreksi retur: stok gudang dikurangi kembali (-)
                $absDelta = abs($delta);
                $stock->update([
                    'current_stock' => $stock->current_stock - $absDelta,
                    'physical_stock' => $stock->physical_stock - $absDelta,
                ]);

                $mutation = new StockMutation([
                    'item_id' => $itemId,
                    'warehouse_id' => $effectiveWarehouseId,
                    'user_id' => auth()->id() ?? 1,
                    'type' => 'out',
                    'quantity' => $absDelta,
                    'mutation_date' => $targetDate,
                    'transaction_category' => 'Koreksi Retur',
                    'reference_type' => ShipmentBatch::class,
                    'reference_id' => $batchObj->id,
                    'sender_name' => "Sales: {$salesmanName}",
                    'notes' => "Koreksi Pengurangan Retur Muatan {$batchObj->batch_number} (Pasar {$this->orderBook->market->name})",
                ]);
                $mutation->created_at = $targetDate . ' ' . now()->format('H:i:s');
                $mutation->save();
            }

            // Sinkronkan PetisSale jika item adalah petis
            if ($isPetis && $petisWarehouseId) {
                $petisKalengItem = Item::where('item_category_id', 5)->where('name', 'KI')->first()
                    ?? Item::where('code', 'like', '%032%')->first();
                $petisRefillItem = Item::where('item_category_id', 5)->where('name', 'Rf')->first()
                    ?? Item::where('code', 'like', '%033%')->first();

                $petisSale = PetisSale::firstOrNew([
                    'warehouse_id' => $petisWarehouseId,
                    'date' => $batchDate,
                    'salesman_name' => $salesmanName,
                ]);

                if ($petisKalengItem && $itemId == $petisKalengItem->id) {
                    $petisSale->retur_kaleng = max(0, ($petisSale->retur_kaleng ?? 0) + $delta);
                } elseif ($petisRefillItem && $itemId == $petisRefillItem->id) {
                    $petisSale->retur_refill = max(0, ($petisSale->retur_refill ?? 0) + $delta);
                }
                $petisSale->user_id = auth()->id() ?? 1;
                $petisSale->save();
            }
        });
    }

    #[Computed]
    public function totalBatches(): int
    {
        return $this->plan?->total_batches ?? 1;
    }

    #[Computed]
    public function itemRows()
    {
        if (!$this->plan) return collect();

        $items = [];

        foreach ($this->plan->items as $planItem) {
            if ($planItem->quantity <= 0) continue;
            if (!$planItem->orderItem || !$planItem->orderItem->item) continue;

            $item = $planItem->orderItem->item;
            $itemId = $item->id;

            if (!isset($items[$itemId])) {
                $items[$itemId] = [
                    'item_id' => $itemId,
                    'name' => $item->name,
                    'category_name' => $item->category->name ?? 'Lain-lain',
                    'batches' => [],
                ];
            }

            $batch = $planItem->batch_number;
            if (!isset($items[$itemId]['batches'][$batch])) {
                $items[$itemId]['batches'][$batch] = 0;
            }

            $items[$itemId]['batches'][$batch] += $planItem->quantity;
        }

        $categoryOrder = [
            'GARAM', 'TNE', 'TNE POLOS', 'LOS', 'PETIS', 'SOHUN', 'AREN', 'TRASI'
        ];

        $itemOrder = [
            'GARAM' => ['B-32', 'K-20', 'G-20', 'KPL 1/4', 'KPL 1/2'],
            'TNE' => ['2 Kg', '3 Kg', 'KK 10', 'KK 20', '4K/10', '4K/20', '1/4 4,5', '1/2 4,5', '1/4 5', '1/2 5', '8 Kg', '9K/10', '9K/20', '10K/10', '10K/20'],
            'TNE POLOS' => ['PLS 1/2', 'PLS 1'],
            'LOS' => ['AGR 50', 'AGR 25', 'DS 50', 'DS 25', 'JGKR', 'JAWA', 'JMR', 'KRKTU', 'DLL'],
            'PETIS' => ['KI', 'Rf'],
            'SOHUN' => ['125', '75', '150', '300'],
            'AREN' => ['1/4 KCL', '1/2 KCL', '1/4 BSR', '1/2 BSR', 'LOS'],
            'TRASI' => ['A J', 'A W', 'LYR']
        ];

        uasort($items, function ($a, $b) use ($categoryOrder, $itemOrder) {
            $catIndexA = array_search(strtoupper($a['category_name']), $categoryOrder);
            $catIndexB = array_search(strtoupper($b['category_name']), $categoryOrder);

            $catIndexA = $catIndexA === false ? 999 : $catIndexA;
            $catIndexB = $catIndexB === false ? 999 : $catIndexB;

            if ($catIndexA === $catIndexB) {
                $catName = strtoupper($a['category_name']);
                if (isset($itemOrder[$catName])) {
                    $orderMap = array_map('strtoupper', $itemOrder[$catName]);
                    $itemIndexA = array_search(strtoupper($a['name']), $orderMap);
                    $itemIndexB = array_search(strtoupper($b['name']), $orderMap);

                    $itemIndexA = $itemIndexA === false ? 999 : $itemIndexA;
                    $itemIndexB = $itemIndexB === false ? 999 : $itemIndexB;

                    if ($itemIndexA === $itemIndexB) {
                        return strcmp($a['name'], $b['name']);
                    }
                    return $itemIndexA <=> $itemIndexB;
                }
                return strcmp($a['name'], $b['name']);
            }

            return $catIndexA <=> $catIndexB;
        });

        return collect($items);
    }

    #[Computed]
    public function totalTonase(): int
    {
        if (!$this->plan) return 0;

        $total = 0;
        foreach ($this->plan->items as $planItem) {
            if ($planItem->quantity > 0 && $planItem->orderItem && $planItem->orderItem->item) {
                $total += $planItem->quantity * $planItem->orderItem->item->weight;
            }
        }
        return $total;
    }

    public function render()
    {
        return view('livewire.order-books.print-shipments')
            ->title("Daftar Pengambilan — {$this->orderBook->market->name}")
            ->layout('layouts.print');
    }
}
