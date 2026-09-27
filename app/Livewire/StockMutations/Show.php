<?php

namespace App\Livewire\StockMutations;

use App\Models\Item;
use App\Models\StockMutation;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;

class Show extends Component
{
    use WithPagination;

    public Warehouse $warehouse;

    public $search = '';

    // Form fields
    public $item_id;
    public $step = 1;
    public $type = 'in';
    public $quantity = 1;
    public $physical_quantity = null;
    public $notes = '';
    public $mutation_date;
    public $sender_name;
    public $transaction_category;

    public function mount(Warehouse $warehouse)
    {
        abort_unless(
            auth()->user() && auth()->user()->can('stock_mutations:read'),
            403
        );

        // Checker biasa hanya boleh akses gudangnya sendiri (kepala_checker bebas)
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
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        Gate::authorize('stock_mutations:create');

        $this->reset(['item_id', 'quantity', 'physical_quantity', 'notes', 'type', 'sender_name', 'transaction_category']);
        $this->mutation_date = date('Y-m-d');
        $this->step = 1;
        $this->type = 'in';
        $this->quantity = 1;
        $this->resetValidation();
        $this->modal('create-mutation-modal')->show();
    }

    public function save()
    {
        Gate::authorize('stock_mutations:create');
        $rules = [
            'mutation_date' => 'required|date',
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:in,out,adjustment',
            'notes' => 'nullable|string',
        ];

        if ($this->type === 'in') {
            $rules['quantity'] = 'required|integer|min:1';
            $rules['sender_name'] = 'nullable|string|max:255';
        } else if ($this->type === 'out') {
            $rules['quantity'] = 'required|integer|min:1';
            $rules['transaction_category'] = 'nullable|string|max:255';
        } else if ($this->type === 'adjustment') {
            $rules['physical_quantity'] = 'required|integer|min:0';
        }

        $this->validate($rules);

        try {
            DB::transaction(function () {
                // Lock stock record for update
                $stock = WarehouseStock::firstOrCreate(
                    ['warehouse_id' => $this->warehouse->id, 'item_id' => $this->item_id],
                    ['current_stock' => 0, 'physical_stock' => 0]
                );

                // Re-fetch with lockForUpdate to ensure atomic transaction
                $stock = WarehouseStock::where('id', $stock->id)->lockForUpdate()->first();

                $oldQuantity = $stock->current_stock;
                $newQuantity = $oldQuantity;
                
                $mutationQuantity = 0;

                if ($this->type === 'in') {
                    $newQuantity += $this->quantity;
                    $mutationQuantity = $this->quantity;
                } else if ($this->type === 'out') {
                    if ($oldQuantity < $this->quantity) {
                        throw new \Exception('Stok tidak mencukupi untuk dikeluarkan.');
                    }
                    $newQuantity -= $this->quantity;
                    $mutationQuantity = $this->quantity;
                } else if ($this->type === 'adjustment') {
                    if ($this->physical_quantity !== null && $this->physical_quantity !== '') {
                        $stock->physical_stock = $this->physical_quantity;
                    }
                }

                // Update stock cache
                $stock->update(['current_stock' => $newQuantity]);
                if ($this->type === 'adjustment' && $this->physical_quantity !== null && $this->physical_quantity !== '') {
                    $stock->update(['physical_stock' => $this->physical_quantity]);
                }

                // Record mutation ledger
                StockMutation::create([
                    'warehouse_id' => $this->warehouse->id,
                    'item_id' => $this->item_id,
                    'type' => $this->type,
                    'quantity' => $this->type === 'adjustment' ? abs($mutationQuantity) : $mutationQuantity,
                    'mutation_date' => $this->mutation_date,
                    'sender_name' => $this->type === 'in' ? $this->sender_name : null,
                    'transaction_category' => $this->type === 'out' ? $this->transaction_category : null,
                    'notes' => $this->notes,
                    'user_id' => auth()->id(),
                ]);
            });

            $this->modal('create-mutation-modal')->close();
            Flux::toast(heading: 'Berhasil', text: 'Mutasi stok berhasil disimpan.', variant: 'success');
            
        } catch (\Exception $e) {
            Flux::toast(heading: 'Gagal', text: $e->getMessage(), variant: 'danger');
        }
    }

    #[Computed]
    public function stocks()
    {
        $query = WarehouseStock::query()
            ->where('warehouse_id', $this->warehouse->id)
            ->with(['item.category']);

        if ($this->search) {
            $query->whereHas('item', function($q) {
                $q->search($this->search);
            });
        }

        return $query->paginate(15);
    }

    #[Computed]
    public function items()
    {
        return Item::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.stock-mutations.show');
    }
}
