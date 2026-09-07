<?php

namespace App\Livewire\Backups;

use App\Models\OrderBook;
use App\Models\MonthlyBackup;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public $month;
    public $search = '';

    public $sortBy = 'book_date';
    public $sortDirection = 'desc';

    public function mount($month)
    {
        abort_unless(auth()->user() && auth()->user()->hasAnyPermission(['backups:read']), 403, 'Unauthorized.');
        
        $backup = MonthlyBackup::where('month', $month)->firstOrFail();
        
        $this->month = $month;
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
    public function orderBooks()
    {
        $parts = explode('-', $this->month);
        
        $query = OrderBook::with(['market', 'employee'])
            ->withCount('orders')
            ->whereYear('book_date', $parts[0])
            ->whereMonth('book_date', $parts[1])
            ->orderBy($this->sortBy, $this->sortDirection);

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('market', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                })->orWhereHas('employee', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('status', 'like', '%' . $this->search . '%');
            });
        }

        return $query->paginate(10);
    }

    public function render()
    {
        return view('livewire.backups.show');
    }
}
