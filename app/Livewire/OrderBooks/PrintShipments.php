<?php

namespace App\Livewire\OrderBooks;

use App\Models\OrderBook;
use App\Models\Order;
use App\Models\ShipmentPlan;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PrintShipments extends Component
{
    public OrderBook $orderBook;
    public ?ShipmentPlan $plan;

    public array $returns = [];
    public array $checkG = [];
    public array $checkS = [];

    public function mount(OrderBook $orderBook)
    {
        abort_unless(
            auth()->user() && auth()->user()->can('order_books:manage-shipments'),
            403
        );

        $this->orderBook = $orderBook;
        $this->plan = $orderBook->shipmentPlan()->with([
            'items.orderItem.item.category',
            'items.orderItem.order.customer',
        ])->first();

        if (!$this->plan) {
            $this->redirect(route('order-books.shipments', $orderBook), navigate: true);
            return;
        }

        // Initialize state
        foreach ($this->plan->items as $planItem) {
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

        // Update DB
        $planItems = $this->plan->items()->whereHas('orderItem', function($q) use ($itemId) {
            $q->where('item_id', $itemId);
        })->where('batch_number', $batch)->get();

        foreach ($planItems as $pi) {
            if ($type === 'G') $pi->update(['is_checked_g' => !$currentValue]);
            else $pi->update(['is_checked_s' => !$currentValue]);
        }
    }

    public function updatedReturns($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) === 2) {
            $itemId = (int) $parts[0];
            $batch = (int) $parts[1];
            $retValue = (int) ($value ?: 0);

            // Need to distribute the return quantity across ShipmentPlanItems for this itemId and batch
            $planItems = $this->plan->items()->whereHas('orderItem', function($q) use ($itemId) {
                $q->where('item_id', $itemId);
            })->where('batch_number', $batch)->get();

            $remainingRetur = $retValue;
            foreach ($planItems as $pi) {
                if ($remainingRetur >= $pi->quantity) {
                    $pi->update(['return_quantity' => $pi->quantity]);
                    $remainingRetur -= $pi->quantity;
                } else {
                    $pi->update(['return_quantity' => $remainingRetur]);
                    $remainingRetur = 0;
                }
            }
        }
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
