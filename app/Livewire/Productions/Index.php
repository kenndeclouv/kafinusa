<?php

namespace App\Livewire\Productions;

use App\Models\Warehouse;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public function mount()
    {
        abort_unless(auth()->user()->can('productions:read'), 403);
    }

    #[Computed]
    public function warehouses()
    {
        $query = Warehouse::query()->orderBy('name');

        if (
            auth()->user()->hasRole('checker') &&
            !auth()->user()->hasRole('kepala_checker') &&
            !auth()->user()->hasRole('superadmin')
        ) {
            $query->where('id', auth()->user()->warehouse_id);
        }

        return $query->get();
    }

    public function render()
    {
        return view('livewire.productions.index');
    }
}
