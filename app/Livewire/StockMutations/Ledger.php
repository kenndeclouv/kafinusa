<?php

namespace App\Livewire\StockMutations;

use App\Models\StockMutation;
use App\Models\Warehouse;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;

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

    public $editingMutationId = null;
    public $editQuantity = 0;
    public $editNotes = '';

    public function editMutation($id)
    {
        Gate::authorize('stock_mutations:update');
        $mutation = StockMutation::findOrFail($id);
        
        $this->editingMutationId = $mutation->id;
        $this->editQuantity = $mutation->quantity;
        $this->editNotes = $mutation->notes;
        
        $this->modal('edit-mutation-modal')->show();
    }

    public function updateMutation()
    {
        Gate::authorize('stock_mutations:update');
        $this->validate([
            'editQuantity' => 'required|numeric|min:0',
            'editNotes' => 'nullable|string',
        ]);

        $mutation = StockMutation::findOrFail($this->editingMutationId);
        
        try {
            \DB::transaction(function () use ($mutation) {
                // Revert old quantity
                $stock = \App\Models\WarehouseStock::where('warehouse_id', $mutation->warehouse_id)
                    ->where('item_id', $mutation->item_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock) {
                    if ($mutation->type === 'in') {
                        $stock->current_stock -= $mutation->quantity;
                    } elseif ($mutation->type === 'out') {
                        $stock->current_stock += $mutation->quantity;
                    }
                    // For adjustment, we can't easily revert because adjustment just overrides stock.
                    // But if it's an adjustment, this simplified CRUD will just let them change the number?
                    // Actually, if it's an adjustment, the quantity is the absolute value.
                    // Let's keep it simple: only adjust 'in' and 'out'.
                    if ($mutation->type !== 'adjustment') {
                        // Apply new quantity
                        if ($mutation->type === 'in') {
                            $stock->current_stock += $this->editQuantity;
                        } elseif ($mutation->type === 'out') {
                            $stock->current_stock -= $this->editQuantity;
                        }
                        $stock->save();
                    }
                }

                $mutation->update([
                    'quantity' => $this->editQuantity,
                    'notes' => $this->editNotes,
                ]);
            });

            $this->modal('edit-mutation-modal')->close();
            \Flux::toast(heading: 'Berhasil', text: 'Mutasi stok berhasil diubah.', variant: 'success');
            $this->reset(['editingMutationId', 'editQuantity', 'editNotes']);

        } catch (\Exception $e) {
            \Flux::toast(heading: 'Gagal', text: $e->getMessage(), variant: 'danger');
        }
    }

    public function deleteMutation($id)
    {
        Gate::authorize('stock_mutations:delete');
        $mutation = StockMutation::findOrFail($id);
        
        try {
            \DB::transaction(function () use ($mutation) {
                $stock = \App\Models\WarehouseStock::where('warehouse_id', $mutation->warehouse_id)
                    ->where('item_id', $mutation->item_id)
                    ->lockForUpdate()
                    ->first();

                if ($stock && $mutation->type !== 'adjustment') {
                    if ($mutation->type === 'in') {
                        $stock->current_stock -= $mutation->quantity;
                    } elseif ($mutation->type === 'out') {
                        $stock->current_stock += $mutation->quantity;
                    }
                    $stock->save();
                }

                $mutation->delete();
            });

            \Flux::toast(heading: 'Berhasil', text: 'Mutasi stok berhasil dihapus.', variant: 'success');
        } catch (\Exception $e) {
            \Flux::toast(heading: 'Gagal', text: $e->getMessage(), variant: 'danger');
        }
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
