<?php

namespace App\Livewire\OrderBooks;

use App\Models\OrderBook;
use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PrintDeliveries extends Component
{
    public OrderBook $orderBook;

    public function mount(OrderBook $orderBook)
    {
        abort_unless(
            auth()->user() && auth()->user()->hasAnyPermission(['order_books:read', 'order_books:read-self']),
            403
        );

        $this->orderBook = $orderBook;
    }

    #[Computed]
    public function reportData()
    {
        $plan = $this->orderBook->shipmentPlan()->with([
            'items.orderItem.item.category',
            'items.orderItem.order.customer.category'
        ])->first();

        $batches = [];

        if (!$plan) {
            // Fallback if no plan is created yet
            $orders = Order::where('order_book_id', $this->orderBook->id)
                ->with(['customer.category', 'orderItems.item.category'])
                ->whereHas('orderItems', function ($query) {
                    $query->where('quantity', '>', 0);
                })
                ->get();

            if ($orders->isEmpty()) {
                return collect();
            }

            $batches[1] = $this->processItemsToBatchData($orders->flatMap->orderItems);
            return collect($batches);
        }

        // We have a plan, group by batch
        for ($b = 1; $b <= $plan->total_batches; $b++) {
            $planItemsInBatch = $plan->items->where('batch_number', $b)->where('quantity', '>', 0);
            if ($planItemsInBatch->isNotEmpty()) {
                // Map ShipmentPlanItem back to a structure similar to OrderItem for processing
                $simulatedOrderItems = $planItemsInBatch->map(function ($planItem) {
                    $oi = $planItem->orderItem;
                    if (!$oi) return null;
                    // override the quantity to the batch's quantity
                    $oi->setAttribute('batch_quantity', $planItem->quantity); 
                    return $oi;
                })->filter();

                $batches[$b] = $this->processItemsToBatchData($simulatedOrderItems, true);
            }
        }

        return collect($batches);
    }

    protected function processItemsToBatchData($orderItems, $useBatchQuantity = false)
    {
        $customers = [];
        $categories = []; // [categoryId => ['name' => ..., 'items' => [itemId => itemData]]]
        $quantities = []; // [customerId][itemId] = qty
        $customerWeights = []; // [customerId] => total weight in grams
        $itemTotals = []; // [itemId] => total qty

        foreach ($orderItems as $orderItem) {
            $qty = $useBatchQuantity ? $orderItem->batch_quantity : $orderItem->quantity;
            if ($qty <= 0) continue;
            if (!$orderItem->item || !$orderItem->order) continue;

            $item = $orderItem->item;
            $order = $orderItem->order;
            $categoryId = $item->item_category_id ?? 0;
            $categoryName = $item->category->name ?? 'Lain-lain';

            if (!isset($categories[$categoryId])) {
                $categories[$categoryId] = [
                    'name' => $categoryName,
                    'items' => [],
                ];
            }

            if (!isset($categories[$categoryId]['items'][$item->id])) {
                $categories[$categoryId]['items'][$item->id] = $item;
            }

            if (!isset($quantities[$order->customer_id][$item->id])) {
                $quantities[$order->customer_id][$item->id] = [
                    'qty' => 0,
                    'price_type' => $orderItem->price_type ?? 'umum',
                ];
            }
            $quantities[$order->customer_id][$item->id]['qty'] += $qty;
            
            if (!isset($customerWeights[$order->customer_id])) {
                $customerWeights[$order->customer_id] = 0;
            }
            $customerWeights[$order->customer_id] += $qty * ($item->weight ?? 0);
            
            if (!isset($itemTotals[$item->id])) {
                $itemTotals[$item->id] = 0;
            }
            $itemTotals[$item->id] += $qty;

            $customers[$order->customer_id] = $order->customer;
        }

        // Define the desired category order
        $categoryOrder = [
            'GARAM',
            'TNE',
            'TNE POLOS',
            'LOS',
            'PETIS',
            'SOHUN',
            'AREN',
            'TRASI'
        ];

        // Sort categories based on the custom order
        usort($categories, function ($a, $b) use ($categoryOrder) {
            $indexA = array_search(strtoupper($a['name']), $categoryOrder);
            $indexB = array_search(strtoupper($b['name']), $categoryOrder);

            // If not found in the custom order array, assign a very high index to put them at the end
            $indexA = $indexA === false ? 999 : $indexA;
            $indexB = $indexB === false ? 999 : $indexB;

            if ($indexA === $indexB) {
                // If both have the same index (e.g., both are 999), sort alphabetically
                return strcmp($a['name'], $b['name']);
            }

            return $indexA <=> $indexB;
        });

        // Define custom item order per category
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

        // Sort items within categories based on custom order
        foreach ($categories as &$category) {
            $catName = strtoupper($category['name']);
            if (isset($itemOrder[$catName])) {
                $orderMap = array_map('strtoupper', $itemOrder[$catName]);
                usort($category['items'], function ($a, $b) use ($orderMap) {
                    $indexA = array_search(strtoupper($a->name), $orderMap);
                    $indexB = array_search(strtoupper($b->name), $orderMap);

                    $indexA = $indexA === false ? 999 : $indexA;
                    $indexB = $indexB === false ? 999 : $indexB;

                    if ($indexA === $indexB) {
                        return strcmp($a->name, $b->name);
                    }

                    return $indexA <=> $indexB;
                });
            } else {
                usort($category['items'], fn($a, $b) => strcmp($a->name, $b->name));
            }
        }

        // Define customer category order
        $customerCategoryOrder = [
            'ECER', // Eceran
            'GROS', // Grosir
            'UKM',  // Usaha Kecil Menengah
        ];

        // Sort customers by category order, then by name
        usort($customers, function ($a, $b) use ($customerCategoryOrder) {
            $catCodeA = $a->category ? $a->category->code : '';
            $catCodeB = $b->category ? $b->category->code : '';

            $indexA = array_search($catCodeA, $customerCategoryOrder);
            $indexB = array_search($catCodeB, $customerCategoryOrder);

            $indexA = $indexA === false ? 999 : $indexA;
            $indexB = $indexB === false ? 999 : $indexB;

            if ($indexA === $indexB) {
                return strcmp($a->name, $b->name);
            }

            return $indexA <=> $indexB;
        });

        return [
            'customers' => collect($customers),
            'categories' => collect($categories),
            'quantities' => $quantities,
            'customerWeights' => $customerWeights,
            'itemTotals' => $itemTotals,
        ];
    }

    public function render()
    {
        return view('livewire.order-books.print-deliveries')
            ->title("Daftar Pengiriman — {$this->orderBook->market->name}")
            ->layout('layouts.print');
    }
}
