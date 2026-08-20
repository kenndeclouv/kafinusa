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

    public function mount(OrderBook $orderBook)
    {
        abort_unless(
            auth()->user() && auth()->user()->hasAnyPermission(['order_books:read', 'order_books:read-self']),
            403
        );

        $this->orderBook = $orderBook;
        $this->plan = $orderBook->shipmentPlan()->with([
            'items.orderItem.item',
            'items.orderItem.order.customer',
        ])->first();

        if (!$this->plan) {
            $this->redirect(route('order-books.shipments', $orderBook), navigate: true);
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
                    'name' => $item->name,
                    'batches' => [],
                ];
            }

            $batch = $planItem->batch_number;
            if (!isset($items[$itemId]['batches'][$batch])) {
                $items[$itemId]['batches'][$batch] = 0;
            }

            $items[$itemId]['batches'][$batch] += $planItem->quantity;
        }

        // Sort items alphabetically by name
        usort($items, fn($a, $b) => strcmp($a['name'], $b['name']));

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
