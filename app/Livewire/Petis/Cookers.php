<?php

namespace App\Livewire\Petis;

use App\Models\PetisCooker;
use App\Models\Warehouse;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Cookers extends Component
{
    public $warehouse_id;
    public $cooker_id;
    public $name = '';
    public $is_active = true;
    public $sort_order = 1;

    public function mount()
    {
        abort_unless(auth()->user()->can('petis_cookers:read'), 403);

        $defaultWarehouse = Warehouse::orderBy('name')->first();
        if (
            auth()->user()->hasRole('checker') &&
            !auth()->user()->hasRole('kepala_checker') &&
            !auth()->user()->hasRole('superadmin')
        ) {
            $this->warehouse_id = auth()->user()->warehouse_id ?? $defaultWarehouse?->id;
        } else {
            $this->warehouse_id = $defaultWarehouse?->id;
        }
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

    #[Computed]
    public function cookers()
    {
        return PetisCooker::where('warehouse_id', $this->warehouse_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function openCreateModal()
    {
        Gate::authorize('petis_cookers:create');
        $this->reset(['cooker_id', 'name']);
        $this->is_active = true;
        $maxOrder = PetisCooker::where('warehouse_id', $this->warehouse_id)->max('sort_order') ?? 0;
        $this->sort_order = $maxOrder + 1;
        $this->resetValidation();
        $this->modal('cooker-modal')->show();
    }

    public function openEditModal($id)
    {
        Gate::authorize('petis_cookers:update');
        $cooker = PetisCooker::findOrFail($id);
        $this->cooker_id = $cooker->id;
        $this->name = $cooker->name;
        $this->is_active = (bool) $cooker->is_active;
        $this->sort_order = $cooker->sort_order;
        $this->resetValidation();
        $this->modal('cooker-modal')->show();
    }

    public function save()
    {
        if ($this->cooker_id) {
            Gate::authorize('petis_cookers:update');
        } else {
            Gate::authorize('petis_cookers:create');
        }

        $this->validate([
            'name' => 'required|string|max:100',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sort_order' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        PetisCooker::updateOrCreate(
            ['id' => $this->cooker_id],
            [
                'warehouse_id' => $this->warehouse_id,
                'name' => $this->name,
                'is_active' => (bool) $this->is_active,
                'sort_order' => $this->sort_order,
            ]
        );

        $this->modal('cooker-modal')->close();
        Flux::toast(
            heading: 'Berhasil',
            text: $this->cooker_id ? 'Data koki berhasil diperbarui.' : 'Koki baru berhasil ditambahkan.',
            variant: 'success'
        );
    }

    public function toggleActive($id)
    {
        Gate::authorize('petis_cookers:update');
        $cooker = PetisCooker::findOrFail($id);
        $cooker->update(['is_active' => !$cooker->is_active]);
        Flux::toast(
            heading: 'Status Diperbarui',
            text: "Koki {$cooker->name} sekarang " . ($cooker->is_active ? 'Aktif' : 'Non-aktif') . '.',
            variant: 'success'
        );
    }

    public function delete($id)
    {
        Gate::authorize('petis_cookers:delete');
        $cooker = PetisCooker::findOrFail($id);
        $cooker->delete(); // Soft delete so past production relations remain safe

        Flux::toast(
            heading: 'Koki Dihapus',
            text: "Koki {$cooker->name} telah dihapus dari daftar aktif. Riwayat masa lalu tetap aman.",
            variant: 'success'
        );
    }

    public function render()
    {
        return view('livewire.petis.cookers');
    }
}
