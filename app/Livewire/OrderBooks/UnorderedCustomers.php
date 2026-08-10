<?php

namespace App\Livewire\OrderBooks;

use App\Models\OrderBook;
use App\Models\Order;
use App\Models\Customer;
use Livewire\Attributes\Computed;
use Livewire\Component;

class UnorderedCustomers extends Component
{
    public OrderBook $orderBook;

    public function mount(OrderBook $orderBook)
    {
        abort_unless(auth()->user() && auth()->user()->hasAnyPermission(['order_books:read', 'order_books:read-self']), 403, 'Unauthorized.');
        
        if (auth()->user()->hasPermissionTo('order_books:read-self') && !auth()->user()->hasPermissionTo('order_books:read')) {
            abort_if($orderBook->employee_id !== (auth()->user()->employee->id ?? 0), 403, 'Anda tidak memiliki akses ke buku order ini.');
        }

        $this->orderBook = $orderBook;
    }

    #[Computed]
    public function customersNotBuying()
    {
        $buyingCustomerIds = Order::where('order_book_id', $this->orderBook->id)
            ->pluck('customer_id');

        return Customer::where('market_id', $this->orderBook->market_id)
            ->where('status', true)
            ->whereNotIn('id', $buyingCustomerIds)
            ->with('category')
            ->get();
    }

    public function render()
    {
        return view('livewire.order-books.unordered-customers')
            ->title("Pelanggan Tidak Beli - {$this->orderBook->market->name}");
    }
}
