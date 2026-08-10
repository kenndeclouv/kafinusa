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
        $orders = Order::where('order_book_id', $this->orderBook->id)
            ->with(['customer', 'orderItems.item.category'])
            ->whereHas('orderItems', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->get();

        if ($orders->isEmpty()) {
            return [
                'customers' => collect(),
                'categories' => collect(),
                'quantities' => [],
            ];
        }

        $customers = [];
        $categories = []; // [categoryId => ['name' => ..., 'items' => [itemId => itemData]]]
        $quantities = []; // [customerId][itemId] = qty
        $customerWeights = []; // [customerId] => total weight in grams
        $itemTotals = []; // [itemId] => total qty

        foreach ($orders as $order) {
            $hasValidItems = false;
            $orderWeight = 0;
            foreach ($order->orderItems as $orderItem) {
                if ($orderItem->quantity > 0) {
                    $hasValidItems = true;
                    $item = $orderItem->item;
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

                    $quantities[$order->customer_id][$item->id] = $orderItem->quantity;
                    $orderWeight += $orderItem->quantity * ($item->weight ?? 0);
                    
                    if (!isset($itemTotals[$item->id])) {
                        $itemTotals[$item->id] = 0;
                    }
                    $itemTotals[$item->id] += $orderItem->quantity;
                }
            }

            if ($hasValidItems) {
                $customers[$order->customer_id] = $order->customer;
                $customerWeights[$order->customer_id] = $orderWeight;
            }
        }

        // Sort categories by name
        usort($categories, fn($a, $b) => strcmp($a['name'], $b['name']));

        // Sort items within categories by name
        foreach ($categories as &$category) {
            usort($category['items'], fn($a, $b) => strcmp($a->name, $b->name));
        }

        // Sort customers by name
        usort($customers, fn($a, $b) => strcmp($a->name, $b->name));

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
