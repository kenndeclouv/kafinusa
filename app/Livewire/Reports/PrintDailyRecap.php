<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\OrderBook;
use App\Models\ItemCategory;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use App\Traits\SortsReportCategories;

class PrintDailyRecap extends Component
{
    use SortsReportCategories;

    #[Url]
    public $date;

    public function mount()
    {
        $this->date = $this->date ?: date('Y-m-d');
    }

    #[Computed]
    public function reportData()
    {
        $orderBooks = OrderBook::with(['employee', 'orders.orderItems'])
            ->whereDate('book_date', $this->date)
            ->get();

        $data = [];

        foreach ($orderBooks as $book) {
            $empId = $book->employee_id;
            
            // Initialize employee data if not exists
            if (!isset($data[$empId])) {
                $data[$empId] = [
                    'employee' => $book->employee,
                    'items' => []
                ];
            }

            foreach ($book->orders as $order) {
                foreach ($order->orderItems as $item) {
                    $itemId = $item->item_id;
                    
                    if (!isset($data[$empId]['items'][$itemId])) {
                        $data[$empId]['items'][$itemId] = 0;
                    }
                    
                    $data[$empId]['items'][$itemId] += $item->quantity;
                }
            }
        }

        return collect($data)->values();
    }

    #[Computed]
    public function categories()
    {
        // First get all items sold on this date to know which items to show
        $soldItemIds = OrderBook::whereDate('book_date', $this->date)
            ->join('orders', 'order_books.id', '=', 'orders.order_book_id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->pluck('order_items.item_id')
            ->unique();

        if ($soldItemIds->isEmpty()) {
            return collect();
        }

        // Get categories that have those items
        $categories = ItemCategory::with(['items' => function ($q) use ($soldItemIds) {
                $q->whereIn('id', $soldItemIds)->orderBy('code');
            }])
            ->whereHas('items', function ($q) use ($soldItemIds) {
                $q->whereIn('id', $soldItemIds);
            })
            ->get();
            
        return $this->sortCategoriesAndItems($categories);
    }

    public function render()
    {
        return view('livewire.reports.print-daily-recap')
            ->title('Cetak Rekap Penjualan')
            ->layout('layouts.print');
    }
}
