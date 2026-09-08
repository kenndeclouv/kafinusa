<?php

namespace App\Livewire\StockMutations;

use App\Models\Warehouse;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    #[Computed]
    public function warehouses()
    {
        $user = auth()->user();
        
        $query = Warehouse::query()->withCount('stocks');

        if ($user->hasRole('checker') && !$user->hasRole('kepala_checker') && !$user->hasRole('superadmin')) {
            $query->where('id', $user->warehouse_id);
        }

        return $query->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.stock-mutations.index');
    }
}
