<?php

namespace App\Livewire\OrderBooks;

use App\Models\OrderBook;
use App\Models\Order;
use Livewire\Component;

class PrintNotas extends Component
{
    public OrderBook $orderBook;

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

        $this->orderBook = $orderBook->load(['market', 'employee']);
    }

    public function render()
    {
        $orders = Order::where('order_book_id', $this->orderBook->id)
            ->with(['customer.category', 'orderItems.item'])
            ->get();

        return view('livewire.order-books.print-notas', [
            'orders' => $orders
        ])->title("Nota Penjualan — {$this->orderBook->market->name}");
    }
}
