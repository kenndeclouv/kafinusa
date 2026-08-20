<?php

namespace App\Livewire\OrderBooks;

use App\Models\OrderBook;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShipmentPlan;
use App\Models\ShipmentPlanItem;
use App\Models\Item;
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

    public function mount(OrderBook $orderBook)
    {
        abort_unless(
            auth()->user() && auth()->user()->hasAnyPermission(['order_books:read', 'order_books:read-self']),
            403
        );

        if (auth()->user()->hasPermissionTo('order_books:read-self') && !auth()->user()->hasPermissionTo('order_books:read')) {
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
        $plan = $this->orderBook->shipmentPlan()->with('items')->first();

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
        } else {
            // Default: semua item masuk muatan 1 dengan full quantity
            $this->initializeDefaults();
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
            // Hapus plan lama jika ada
            $plan = $this->orderBook->shipmentPlan;
            if ($plan) {
                $plan->items()->delete();
                $plan->delete();
            }

            // Buat plan baru
            $plan = ShipmentPlan::create([
                'order_book_id' => $this->orderBook->id,
                'created_by'    => auth()->id(),
                'total_batches' => $this->totalBatches,
            ]);

            // Simpan semua assignments
            foreach ($this->assignments as $orderItemId => $batches) {
                foreach ($batches as $batchNumber => $qty) {
                    if ($qty > 0) {
                        ShipmentPlanItem::create([
                            'shipment_plan_id' => $plan->id,
                            'order_item_id'    => $orderItemId,
                            'batch_number'     => $batchNumber,
                            'quantity'         => $qty,
                        ]);
                    }
                }
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

    public function render()
    {
        return view('livewire.order-books.manage-shipments')
            ->title("Atur Muatan — {$this->orderBook->market->name}");
    }
}
