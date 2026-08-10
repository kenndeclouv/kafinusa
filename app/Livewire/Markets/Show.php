<?php

namespace App\Livewire\Markets;

use App\Models\Market;
use App\Models\Customer;
use App\Models\OrderBook;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public Market $market;
    
    public $search = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    public function mount(Market $market)
    {
        $this->market = $market;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function marketOrderBookIds()
    {
        return OrderBook::where('market_id', $this->market->id)
            ->where('book_date', '<=', now()->toDateString())
            ->orderBy('book_date', 'desc')
            ->pluck('id');
    }

    #[Computed]
    public function customers()
    {
        return $this->market->customers()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhereHas('category', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->with(['category', 'orders' => function ($query) {
                $query->select('id', 'customer_id', 'order_book_id');
            }])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.markets.show');
    }
}
