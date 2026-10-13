<?php

namespace App\Livewire\OrderBooks;

use App\Models\OrderBook;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanItem;
use App\Models\ShipmentBatch;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\StockMutation;
use App\Models\PetisSale;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Flux\Flux;

class ManageShipments extends Component
{
    public OrderBook $orderBook;

    public int $totalBatches = 1;

    // assignments[order_item_id][batch_number] = quantity
    public array $assignments = [];

    // Batch metadata
    public array $batchStatuses = [];
    public array $batchIds = [];
    public array $batchDates = [];

    // Modal state for shipping
    public ?int $selectedBatchForShipping = null;
    public ?string $shippingDate = null;
    public ?int $shippingWarehouseId = null;

    // Modal state for changing date
    public ?int $selectedBatchForDateChange = null;
    public ?string $newBatchDate = null;

    // Modal state for bulk shipping
    public ?string $bulkShippingDate = null;
    public ?int $bulkShippingWarehouseId = null;

    public function mount(OrderBook $orderBook)
    {
        abort_unless(
            auth()->user() && auth()->user()->can('order_books:manage-shipments'),
            403
        );

        if (auth()->user()->can('order_books:read-self') && !auth()->user()->can('order_books:read')) {
            abort_if(
                $orderBook->employee_id !== (auth()->user()->employee->id ?? 0),
                403,
                'Anda tidak memiliki akses ke buku order ini.'
            );
        }

        $this->orderBook = $orderBook;
        $this->loadExistingPlan();
    }

    protected function loadExistingPlan(): void
    {
        $plan = $this->orderBook->shipmentPlan()->with(['items', 'batches'])->first();

        if ($plan) {
            $this->totalBatches = $plan->total_batches;
            $plannedItemIds = $plan->items->pluck('order_item_id')->toArray();

            // Pre-initialize all items to 0 for all batches to ensure Alpine JS reactivity
            $orders = Order::where('order_book_id', $this->orderBook->id)->with('orderItems')->get();
            foreach ($orders as $order) {
                foreach ($order->orderItems as $orderItem) {
                    if (!in_array($orderItem->id, $plannedItemIds)) {
                        // New item added after plan was created: default to Batch 1
                        $this->assignments[$orderItem->id][1] = $orderItem->quantity;
                        for ($b = 2; $b <= max($this->totalBatches, 5); $b++) {
                            $this->assignments[$orderItem->id][$b] = 0;
                        }
                    } else {
                        // Item exists in plan: initialize all to 0 first
                        for ($b = 1; $b <= max($this->totalBatches, 5); $b++) {
                            $this->assignments[$orderItem->id][$b] = 0;
                        }
                    }
                }
            }

            foreach ($plan->items as $planItem) {
                $this->assignments[$planItem->order_item_id][$planItem->batch_number] = $planItem->quantity;
            }

            foreach ($plan->batches as $batch) {
                $this->batchStatuses[$batch->batch_number] = $batch->status;
                $this->batchIds[$batch->batch_number] = $batch->id;
                $this->batchDates[$batch->batch_number] = $batch->shipment_date ? $batch->shipment_date->format('Y-m-d') : $this->orderBook->book_date->format('Y-m-d');
            }

            $this->fillMissingBatchMetadata();
        } else {
            // Default: semua item masuk muatan 1 dengan full quantity
            $this->initializeDefaults();
        }
    }

    protected function fillMissingBatchMetadata(): void
    {
        for ($b = 1; $b <= $this->totalBatches; $b++) {
            if (!isset($this->batchStatuses[$b])) {
                $this->batchStatuses[$b] = 'pending';
            }
            if (!isset($this->batchDates[$b])) {
                $this->batchDates[$b] = $this->orderBook->book_date->format('Y-m-d');
            }
        }
    }

    protected function initializeDefaults(): void
    {
        $orders = Order::where('order_book_id', $this->orderBook->id)
            ->with('orderItems')
            ->get();

        foreach ($orders as $order) {
            foreach ($order->orderItems as $orderItem) {
                // Initialize default quantities (Batch 1 = Full qty, Batch 2+ = 0)
                $this->assignments[$orderItem->id][1] = $orderItem->quantity;
                for ($b = 2; $b <= 5; $b++) { // Pre-initialize a few possible batches to ensure JS reactivity
                    $this->assignments[$orderItem->id][$b] = 0;
                }
            }
        }

        $this->totalBatches = 1;
        $this->fillMissingBatchMetadata();
    }

    #[Computed]
    public function warehouses()
    {
        return Warehouse::where('name', '!=', 'Gudang Petis')->pluck('name', 'id');
    }

    #[Computed]
    public function ordersWithItems()
    {
        return Order::where('order_book_id', $this->orderBook->id)
            ->with(['customer', 'orderItems.item'])
            ->get();
    }

    #[Computed]
    public function batchTotals()
    {
        $totals = [];
        for ($b = 1; $b <= $this->totalBatches; $b++) {
            $totals[$b] = 0;
        }

        $orders = Order::where('order_book_id', $this->orderBook->id)
            ->with('orderItems.item')
            ->get();

        foreach ($orders as $order) {
            foreach ($order->orderItems as $orderItem) {
                $weight = $orderItem->item?->weight ?? 0;
                for ($b = 1; $b <= $this->totalBatches; $b++) {
                    $qty = $this->assignments[$orderItem->id][$b] ?? 0;
                    $totals[$b] += $qty * $weight;
                }
            }
        }

        return $totals;
    }

    public function addBatch(): void
    {
        $this->totalBatches++;
    }

    public function removeBatch(int $batchNumber): void
    {
        if ($this->totalBatches <= 1) return;

        // Pindah semua qty dari batch yang dihapus ke batch 1
        foreach ($this->assignments as $orderItemId => $batches) {
            $qty = $batches[$batchNumber] ?? 0;
            if ($qty > 0) {
                $this->assignments[$orderItemId][1] = ($this->assignments[$orderItemId][1] ?? 0) + $qty;
            }
            unset($this->assignments[$orderItemId][$batchNumber]);
        }

        // Re-number batches yang lebih tinggi dari yang dihapus
        if ($batchNumber < $this->totalBatches) {
            foreach ($this->assignments as $orderItemId => $batches) {
                for ($b = $batchNumber + 1; $b <= $this->totalBatches; $b++) {
                    $qty = $batches[$b] ?? 0;
                    $this->assignments[$orderItemId][$b - 1] = $qty;
                    unset($this->assignments[$orderItemId][$b]);
                }
            }
        }

        $this->totalBatches--;
        $this->fillMissingBatchMetadata();
    }

    public function updatedAssignments($value, $key = null): void
    {
        if (!$key) return;

        $parts = explode('.', (string) $key);
        if (count($parts) === 2) {
            $orderItemId = (int) $parts[0];
            $changedBatch = (int) $parts[1];

            $orderItem = OrderItem::find($orderItemId);
            if (!$orderItem) return;

            $maxQty = $orderItem->quantity;
            $newValue = (int) ($value ?: 0);

            // Force update the current value in case it was empty string
            $this->assignments[$orderItemId][$changedBatch] = $newValue;

            // Determine which batch to auto-adjust
            $targetBatchToAdjust = $changedBatch === 1 ? ($this->totalBatches > 1 ? 2 : null) : 1;
            
            if ($targetBatchToAdjust) {
                // Sum all batches EXCEPT the target batch we want to auto-adjust
                $sumOtherThanTarget = 0;
                for ($b = 1; $b <= $this->totalBatches; $b++) {
                    if ($b !== $targetBatchToAdjust) {
                        $sumOtherThanTarget += (int) ($this->assignments[$orderItemId][$b] ?? 0);
                    }
                }

                $newTargetValue = $maxQty - $sumOtherThanTarget;
                
                if ($newTargetValue < 0) {
                    $newTargetValue = 0;
                    // Clamp the changed batch so the total doesn't exceed maxQty
                    $otherBatchesSumWithoutChanged = $sumOtherThanTarget - $newValue;
                    $this->assignments[$orderItemId][$changedBatch] = max(0, $maxQty - $otherBatchesSumWithoutChanged);
                }
                
                $this->assignments[$orderItemId][$targetBatchToAdjust] = $newTargetValue;
            }
        }
    }

    public function moveToBatch(int $orderId, int $batchNumber): void
    {
        $order = Order::with('orderItems')->find($orderId);
        if (!$order) return;

        foreach ($order->orderItems as $orderItem) {
            $maxQty = $orderItem->quantity;
            
            // Set all batches to 0 for this item
            for ($b = 1; $b <= $this->totalBatches; $b++) {
                $this->assignments[$orderItem->id][$b] = 0;
            }
            
            // Set the selected batch to the max quantity
            $this->assignments[$orderItem->id][$batchNumber] = $maxQty;
        }
    }

    public function save($type = 'summary'): void
    {
        // Validasi: total qty per order item harus sama dengan quantity aslinya
        $orders = Order::where('order_book_id', $this->orderBook->id)
            ->with('orderItems')
            ->get();

        foreach ($orders as $order) {
            foreach ($order->orderItems as $orderItem) {
                $totalAssigned = collect($this->assignments[$orderItem->id] ?? [])->sum();
                if ($totalAssigned != $orderItem->quantity) {
                    $itemName = $orderItem->item?->name ?? 'Produk Terhapus';
                    $customerName = $order->customer?->name ?? 'Customer Terhapus';
                    Flux::toast(
                        heading: 'Validasi Gagal',
                        text: "Total muatan untuk item {$itemName} (customer: {$customerName}) tidak sama dengan jumlah pesanan ({$orderItem->quantity}). Total saat ini: {$totalAssigned}.",
                        variant: 'danger'
                    );
                    return;
                }
            }
        }

        DB::transaction(function () {
            $plan = $this->orderBook->shipmentPlan;
            
            if (!$plan) {
                // Buat plan baru
                $plan = ShipmentPlan::create([
                    'order_book_id' => $this->orderBook->id,
                    'created_by'    => auth()->id(),
                    'total_batches' => $this->totalBatches,
                ]);
            } else {
                $plan->update(['total_batches' => $this->totalBatches]);
            }

            // Simpan semua assignments
            $validItemIds = [];
            foreach ($this->assignments as $orderItemId => $batches) {
                foreach ($batches as $batchNumber => $qty) {
                    if ($qty > 0) {
                        $planItem = ShipmentPlanItem::updateOrCreate([
                            'shipment_plan_id' => $plan->id,
                            'order_item_id'    => $orderItemId,
                            'batch_number'     => $batchNumber,
                        ], [
                            'quantity'         => $qty,
                        ]);
                        $validItemIds[] = $planItem->id;
                    }
                }
            }
            
            // Hapus assignment yang qty nya di set jadi 0
            if ($plan) {
                $plan->items()->whereNotIn('id', $validItemIds)->delete();
            }

            // Simpan metadata batch (hanya yang pending, yang shipped jangan di-overwrite datenya)
            $validBatchNumbers = [];
            for ($b = 1; $b <= $this->totalBatches; $b++) {
                $validBatchNumbers[] = $b;
                $existingBatch = $plan->batches()->where('batch_number', $b)->first();
                $targetDate = $this->batchDates[$b] ?? $this->orderBook->book_date->format('Y-m-d');
                if (!$existingBatch) {
                    ShipmentBatch::create([
                        'shipment_plan_id' => $plan->id,
                        'batch_number'     => $b,
                        'shipment_date'    => $targetDate,
                        'status'           => $this->batchStatuses[$b] ?? 'pending',
                    ]);
                } else {
                    if ($existingBatch->status !== 'shipped') {
                        $existingBatch->update(['shipment_date' => $targetDate]);
                    }
                }
            }
            
            // Hapus batch yang sudah tidak ada
            if ($plan) {
                $plan->batches()->whereNotIn('batch_number', $validBatchNumbers)->delete();
            }
        });

        if ($type === 'nota') {
            $this->redirect(route('order-books.shipments.notas', $this->orderBook), navigate: true);
        } elseif ($type === 'delivery') {
            $this->redirect(route('order-books.shipments.deliveries', $this->orderBook), navigate: true);
        } else {
            $this->redirect(route('order-books.shipments.print', $this->orderBook), navigate: true);
        }
    }

    public function openShippingModal(int $batchNumber): void
    {
        $this->selectedBatchForShipping = $batchNumber;
        $this->shippingDate = $this->orderBook->book_date->format('Y-m-d');
        
        // Coba auto-select gudang pertama jika ada
        $warehouses = $this->warehouses();
        if ($warehouses->isNotEmpty()) {
            $this->shippingWarehouseId = $warehouses->keys()->first();
        } else {
            $this->shippingWarehouseId = null;
        }

        $this->modal('shipping-modal')->show();
    }

    public function confirmShipping(): void
    {
        $batchNumber = $this->selectedBatchForShipping;
        $warehouseId = $this->shippingWarehouseId;
        $date = $this->shippingDate;

        if (!$batchNumber) return;

        if (!$warehouseId || !$date) {
            Flux::toast(
                heading: 'Data Tidak Lengkap',
                text: 'Silakan pilih tanggal dan gudang asal terlebih dahulu.',
                variant: 'danger'
            );
            return;
        }

        // 1. Validasi dulu apakah assignmennya udah di save
        // Untuk aman, kita save dulu
        $this->save('silent');

        // Reload the plan
        $plan = $this->orderBook->shipmentPlan()->with(['items', 'batches'])->first();
        $batch = $plan->batches->where('batch_number', $batchNumber)->first();

        if (!$batch || $batch->status === 'shipped') {
            return; // already shipped
        }

        // Lakukan pemotongan stok
        DB::transaction(function () use ($plan, $batch, $warehouseId, $date) {
            $this->processShipmentBatch($plan, $batch, $warehouseId, $date);
        });
        
        Flux::modal('shipping-modal')->close();

        Flux::toast(
            heading: 'Muatan Terkirim',
            text: "Stok telah dipotong dari Gudang. Muatan {$batchNumber} terkunci.",
            variant: 'success'
        );
    }

    protected function processShipmentBatch($plan, $batch, $warehouseId, $date)
    {
        $petisWarehouse = Warehouse::where('name', 'Gudang Petis')->first();
        $petisWarehouseId = $petisWarehouse?->id;

        $petisKalengItem = Item::where('item_category_id', 5)->where('name', 'KI')->first()
            ?? Item::where('code', 'like', '%032%')->first();
        $petisRefillItem = Item::where('item_category_id', 5)->where('name', 'Rf')->first()
            ?? Item::where('code', 'like', '%033%')->first();

        $itemsInBatch = $plan->items->where('batch_number', $batch->batch_number);
        $groupedItems = [];

        $kalengMuatan = 0;
        $kalengRetur = 0;
        $refillMuatan = 0;
        $refillRetur = 0;

        foreach ($itemsInBatch as $pi) {
            $orderItem = OrderItem::find($pi->order_item_id);
            if ($orderItem) {
                $itemId = $orderItem->item_id;
                if (!isset($groupedItems[$itemId])) $groupedItems[$itemId] = 0;
                $qty = max(0, $pi->quantity - $pi->return_quantity);
                $groupedItems[$itemId] += $qty;

                // Hitung muatan dan retur untuk pencatatan PetisSale
                if ($petisKalengItem && $itemId == $petisKalengItem->id) {
                    $kalengMuatan += (int) $pi->quantity;
                    $kalengRetur += (int) $pi->return_quantity;
                } elseif ($petisRefillItem && $itemId == $petisRefillItem->id) {
                    $refillMuatan += (int) $pi->quantity;
                    $refillRetur += (int) $pi->return_quantity;
                }
            }
        }

        foreach ($groupedItems as $itemId => $qty) {
            if ($qty <= 0) continue;

            $item = Item::with('category')->find($itemId);
            $isPetis = ($item && ($item->item_category_id == 5 || strtolower($item->category?->name ?? '') === 'petis'));

            // Jika petis, otomatis potong dari Gudang Petis secara independen
            $effectiveWarehouseId = ($isPetis && $petisWarehouseId) ? $petisWarehouseId : $warehouseId;

            $stock = \App\Models\WarehouseStock::firstOrCreate(
                ['warehouse_id' => $effectiveWarehouseId, 'item_id' => $itemId],
                ['current_stock' => 0, 'physical_stock' => 0]
            );
            $stock = \App\Models\WarehouseStock::where('id', $stock->id)->lockForUpdate()->first();
            
            $stock->update([
                'current_stock' => $stock->current_stock - $qty,
                'physical_stock' => $stock->physical_stock - $qty,
            ]);

            // Create stock mutation OUT
            $mutation = new StockMutation([
                'item_id' => $itemId,
                'warehouse_id' => $effectiveWarehouseId,
                'user_id' => auth()->id() ?? 1,
                'type' => 'out',
                'quantity' => $qty,
                'mutation_date' => $date,
                'sender_name' => $isPetis ? 'Gudang Petis' : null,
                'transaction_category' => 'Pengiriman',
                'notes' => "Pengiriman Muatan {$batch->batch_number} (Pasar {$this->orderBook->market->name})"
            ]);
            $mutation->created_at = $date . ' ' . now()->format('H:i:s');
            $mutation->save();
        }

        // Catat penjualan petis harian per salesman jika ada petis di muatan ini
        if ($petisWarehouseId && ($kalengMuatan > 0 || $refillMuatan > 0 || $kalengRetur > 0 || $refillRetur > 0)) {
            $salesmanName = $this->orderBook->employee?->name ?? 'Sales';
            $petisSale = PetisSale::firstOrNew([
                'warehouse_id' => $petisWarehouseId,
                'date' => $date,
                'salesman_name' => $salesmanName,
            ]);
            $petisSale->muatan_kaleng = ($petisSale->muatan_kaleng ?? 0) + $kalengMuatan;
            $petisSale->retur_kaleng = ($petisSale->retur_kaleng ?? 0) + $kalengRetur;
            $petisSale->muatan_refill = ($petisSale->muatan_refill ?? 0) + $refillMuatan;
            $petisSale->retur_refill = ($petisSale->retur_refill ?? 0) + $refillRetur;
            $petisSale->notes = $petisSale->notes 
                ? ($petisSale->notes . ', Muatan ' . $batch->batch_number)
                : "Muatan {$batch->batch_number} (Pasar {$this->orderBook->market->name})";
            $petisSale->user_id = auth()->id() ?? 1;
            $petisSale->save();
        }

        $batch->update([
            'status' => 'shipped',
            'shipment_date' => $date,
            'warehouse_id' => $warehouseId,
        ]);
        
        $this->batchStatuses[$batch->batch_number] = 'shipped';
    }

    public function openBulkShippingModal(): void
    {
        $this->bulkShippingDate = $this->orderBook->book_date->format('Y-m-d');
        $warehouses = $this->warehouses();
        $this->bulkShippingWarehouseId = $warehouses->isNotEmpty() ? $warehouses->keys()->first() : null;
        $this->modal('bulk-shipping-modal')->show();
    }

    public function confirmBulkShipping(): void
    {
        $warehouseId = $this->bulkShippingWarehouseId;
        $date = $this->bulkShippingDate;

        if (!$warehouseId || !$date) {
            Flux::toast(
                heading: 'Data Tidak Lengkap',
                text: 'Silakan pilih tanggal dan gudang asal terlebih dahulu.',
                variant: 'danger'
            );
            return;
        }

        $this->save('silent');
        $plan = $this->orderBook->shipmentPlan()->with(['items', 'batches'])->first();
        if (!$plan) return;

        $shippedCount = 0;
        
        DB::transaction(function () use ($plan, $warehouseId, $date, &$shippedCount) {
            foreach ($plan->batches as $batch) {
                $batchDate = $batch->shipment_date ? $batch->shipment_date->format('Y-m-d') : $this->orderBook->book_date->format('Y-m-d');
                if ($batch->status !== 'shipped' && $batchDate === $this->orderBook->book_date->format('Y-m-d')) {
                    $this->processShipmentBatch($plan, $batch, $warehouseId, $date);
                    $shippedCount++;
                }
            }
        });

        Flux::modal('bulk-shipping-modal')->close();
        
        if ($shippedCount > 0) {
            Flux::toast(
                heading: 'Kirim Selesai',
                text: "{$shippedCount} muatan telah terkirim dan stok dipotong.",
                variant: 'success'
            );
        } else {
            Flux::toast(
                heading: 'Info',
                text: 'Tidak ada muatan hari ini yang siap dikirim.',
                variant: 'warning'
            );
        }
    }

    public function openChangeDateModal(int $batchNumber): void
    {
        $this->selectedBatchForDateChange = $batchNumber;
        $this->newBatchDate = $this->batchDates[$batchNumber] ?? $this->orderBook->book_date->format('Y-m-d');
        $this->modal('change-date-modal')->show();
    }

    public function confirmChangeDate(): void
    {
        if (!$this->selectedBatchForDateChange || !$this->newBatchDate) return;
        
        $this->batchDates[$this->selectedBatchForDateChange] = $this->newBatchDate;
        
        $this->save('silent');
        
        Flux::modal('change-date-modal')->close();
        Flux::toast(
            heading: 'Berhasil',
            text: "Tanggal pengiriman muatan {$this->selectedBatchForDateChange} diubah.",
            variant: 'success'
        );
    }

    #[Computed]
    public function incomingBatches()
    {
        return ShipmentBatch::whereDate('shipment_date', $this->orderBook->book_date->format('Y-m-d'))
            ->whereHas('shipmentPlan.orderBook', function ($q) {
                $q->where('market_id', $this->orderBook->market_id)
                  ->where('id', '!=', $this->orderBook->id);
            })
            ->with(['shipmentPlan.orderBook.employee', 'shipmentPlan.items.orderItem.item'])
            ->get();
    }

    public function render()
    {
        return view('livewire.order-books.manage-shipments')
            ->title("Atur Muatan — {$this->orderBook->market->name}");
    }
}
