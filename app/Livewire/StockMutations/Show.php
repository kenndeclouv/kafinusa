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

class Show extends Component
{
    use WithPagination;

    public Warehouse $warehouse;

    public $search = '';
    public $typeFilter = '';

    // Form fields
    public $item_id;
    public $type = 'in';
    public $quantity = 1;
    public $notes = '';

    public function mount(Warehouse $warehouse)
    {
        // RBAC validation
        $user = auth()->user();
        if ($user->hasRole('checker') && !$user->hasRole('kepala_checker') && !$user->hasRole('superadmin')) {
            if ($user->warehouse_id !== $warehouse->id) {
                abort(403, 'Anda tidak memiliki akses ke gudang ini.');
            }
        }
        
        $this->warehouse = $warehouse;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['item_id', 'quantity', 'notes', 'type']);
        $this->type = 'in';
        $this->quantity = 1;
        $this->resetValidation();
        $this->modal('create-mutation-modal')->show();
    }

    public function save()
    {
        $this->validate([
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () {
                // Lock stock record for update
                $stock = WarehouseStock::firstOrCreate(
                    ['warehouse_id' => $this->warehouse->id, 'item_id' => $this->item_id],
                    ['quantity' => 0]
                );

                // Re-fetch with lockForUpdate to ensure atomic transaction
                $stock = WarehouseStock::where('id', $stock->id)->lockForUpdate()->first();

                $oldQuantity = $stock->quantity;
                $newQuantity = $oldQuantity;

                if ($this->type === 'in') {
                    $newQuantity += $this->quantity;
                } else if ($this->type === 'out') {
                    if ($oldQuantity < $this->quantity) {
                        throw new \Exception('Stok tidak mencukupi untuk dikeluarkan.');
                    }
                    $newQuantity -= $this->quantity;
                } else if ($this->type === 'adjustment') {
                    throw new \Exception('Adjustment khusus belum didukung lewat form ini.');
                }

                // Update stock cache
                $stock->update(['quantity' => $newQuantity]);

                // Record mutation ledger
                StockMutation::create([
                    'warehouse_id' => $this->warehouse->id,
                    'item_id' => $this->item_id,
                    'type' => $this->type,
                    'quantity' => $this->quantity,
                    'balance_after' => $newQuantity,
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
    public function mutations()
    {
        $query = StockMutation::query()
            ->where('warehouse_id', $this->warehouse->id)
            ->with(['item', 'user'])
            ->latest();

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->search) {
            $query->whereHas('item', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        return $query->paginate(10);
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
