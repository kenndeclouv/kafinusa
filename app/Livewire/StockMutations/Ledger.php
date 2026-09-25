<?php

namespace App\Livewire\StockMutations;

use App\Models\StockMutation;
use App\Models\Warehouse;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Ledger extends Component
{
    use WithPagination;

    public Warehouse $warehouse;

    public $search = '';
    public $typeFilter = '';
    public $month = '';
    public $year = '';

    public function mount(Warehouse $warehouse)
    {
        abort_unless(
            auth()->user() && auth()->user()->can('stock_mutations:read'),
            403
        );

        // Checker biasa hanya boleh akses gudangnya sendiri
        if (
            auth()->user()->hasRole('checker') &&
            !auth()->user()->hasRole('kepala_checker') &&
            !auth()->user()->hasRole('superadmin')
        ) {
            abort_if(
                auth()->user()->warehouse_id !== $warehouse->id,
                403,
                'Anda hanya dapat mengakses gudang yang di-assign ke akun Anda.'
            );
        }
        
        $this->warehouse = $warehouse;
        
        $this->month = date('m');
        $this->year = date('Y');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }
    
    public function updatedMonth()
    {
        $this->resetPage();
    }

    public function updatedYear()
    {
        $this->resetPage();
    }

    #[Computed]
    public function mutations()
    {
        $query = StockMutation::query()
            ->where('warehouse_id', $this->warehouse->id)
            ->with(['item', 'user'])
            ->latest();

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->month && $this->year) {
            $query->whereMonth('created_at', $this->month)
                  ->whereYear('created_at', $this->year);
        }

        if ($this->search) {
            $query->whereHas('item', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.stock-mutations.ledger');
    }
}
