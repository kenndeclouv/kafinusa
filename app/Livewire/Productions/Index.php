<?php

namespace App\Livewire\Productions;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Production;
use App\Models\Warehouse;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'date';
    public $sortDirection = 'desc';

    // Form fields for Create
    public $date;
    public $warehouse_id;
    public $raw_item_id;
    public $raw_quantity;
    public $notes;
    public $results = [];

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

    public function openCreateModal()
    {
        Gate::authorize('productions:create');
        
        $this->reset(['warehouse_id', 'raw_item_id', 'raw_quantity', 'notes']);
        $this->date = date('Y-m-d');
        $this->results = [];
        $this->addResult();
        $this->resetValidation();
        $this->modal('create-production-modal')->show();
    }

    public function addResult()
    {
        $this->results[] = ['item_id' => null, 'quantity' => null];
    }

    public function removeResult($index)
    {
        unset($this->results[$index]);
        $this->results = array_values($this->results); // Re-index array
    }

    public function save()
    {
        Gate::authorize('productions:create');

        $this->validate([
            'date' => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
            'raw_item_id' => 'required|exists:items,id',
            'raw_quantity' => 'required|numeric|min:1',
            'results' => 'required|array|min:1',
            'results.*.item_id' => 'required|exists:items,id|different:raw_item_id',
            'results.*.quantity' => 'required|numeric|min:1',
        ], [
            'results.*.item_id.different' => 'Barang jadi tidak boleh sama dengan bahan baku.',
        ]);

        try {
            DB::transaction(function () {
                // Create production
                $production = Production::create([
                    'user_id' => auth()->id(),
                    'warehouse_id' => $this->warehouse_id,
                    'raw_item_id' => $this->raw_item_id,
                    'date' => $this->date,
                    'raw_quantity' => $this->raw_quantity,
                    'notes' => $this->notes,
                ]);

                // Create results
                foreach ($this->results as $resultData) {
                    $production->results()->create([
                        'item_id' => $resultData['item_id'],
                        'quantity' => $resultData['quantity'],
                    ]);
                }

                // Trigger mutations
                $production->processMutations();
            });

            $this->modal('create-production-modal')->close();
            
            Flux::toast(
                heading: 'Berhasil Disimpan',
                text: 'Catatan produksi harian berhasil ditambahkan.',
                variant: 'success'
            );
        } catch (\Exception $e) {
            Flux::toast(heading: 'Gagal', text: $e->getMessage(), variant: 'danger');
        }
    }

    #[Computed]
    public function warehouses()
    {
        return Warehouse::orderBy('name')->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function items()
    {
        return Item::with('category')->orderBy('name')->get();
    }

    public function render()
    {
        $productions = Production::query()
            ->with(['warehouse', 'rawItem', 'results.item', 'user'])
            ->when($this->search, function ($query) {
                $query->whereHas('warehouse', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                      ->orWhereHas('rawItem', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(15);

        return view('livewire.productions.index', [
            'productions' => $productions,
        ]);
    }
}
