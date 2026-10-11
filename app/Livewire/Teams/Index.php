<?php

namespace App\Livewire\Teams;

use Livewire\Component;
use App\Models\Team;
use App\Models\Warehouse;
use App\Models\PetisCooker;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;

class Index extends Component
{
    use WithPagination;

    public $activeTab = 'teams'; // 'teams' | 'cookers'

    public $search = '';
    public $filter_warehouse_id = null;

    public $sortBy = 'name';
    public $sortDirection = 'asc';

    // Team Form fields
    public $team_id;
    public $name;
    public $warehouse_id;

    // Cooker Form fields
    public $cooker_id;
    public $cooker_name;
    public $cooker_warehouse_id;
    public $cooker_sort_order = 1;
    public $cooker_is_active = true;

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedActiveTab()
    {
        $this->resetPage();
        $this->search = '';
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterWarehouseId()
    {
        $this->resetPage();
    }

    // --- TIM PRODUKSI METHODS ---

    public function openCreateModal()
    {
        $this->reset(['team_id', 'name', 'warehouse_id']);
        $this->resetValidation();
        $this->modal('team-modal')->show();
    }

    public function editTeam($id)
    {
        $team = Team::findOrFail($id);
        $this->team_id = $team->id;
        $this->name = $team->name;
        $this->warehouse_id = $team->warehouse_id;
        $this->resetValidation();
        $this->modal('team-modal')->show();
    }

    public function saveTeam()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        Team::updateOrCreate(
            ['id' => $this->team_id],
            [
                'name' => $this->name,
                'warehouse_id' => $this->warehouse_id,
            ]
        );

        $this->modal('team-modal')->close();
        
        Flux::toast(
            heading: 'Berhasil',
            text: 'Data tim berhasil disimpan.',
            variant: 'success'
        );
    }

    public function deleteTeam($id)
    {
        try {
            Team::findOrFail($id)->delete();
            Flux::toast(heading: 'Berhasil', text: 'Tim berhasil dihapus.', variant: 'success');
        } catch (\Exception $e) {
            Flux::toast(heading: 'Gagal', text: 'Gagal menghapus tim.', variant: 'danger');
        }
    }

    // --- KOKI PETIS METHODS ---

    public function openCreateCookerModal()
    {
        Gate::authorize('petis_cookers:create');
        $this->reset(['cooker_id', 'cooker_name']);
        $this->cooker_warehouse_id = $this->filter_warehouse_id ?: Warehouse::first()?->id;
        $maxOrder = PetisCooker::where('warehouse_id', $this->cooker_warehouse_id)->max('sort_order') ?? 0;
        $this->cooker_sort_order = $maxOrder + 1;
        $this->cooker_is_active = true;
        $this->resetValidation();
        $this->modal('cooker-modal')->show();
    }

    public function editCooker($id)
    {
        Gate::authorize('petis_cookers:update');
        $cooker = PetisCooker::findOrFail($id);
        $this->cooker_id = $cooker->id;
        $this->cooker_name = $cooker->name;
        $this->cooker_warehouse_id = $cooker->warehouse_id;
        $this->cooker_sort_order = $cooker->sort_order;
        $this->cooker_is_active = (bool) $cooker->is_active;
        $this->resetValidation();
        $this->modal('cooker-modal')->show();
    }

    public function saveCooker()
    {
        if ($this->cooker_id) {
            Gate::authorize('petis_cookers:update');
        } else {
            Gate::authorize('petis_cookers:create');
        }

        $this->validate([
            'cooker_name' => 'required|string|max:100',
            'cooker_warehouse_id' => 'required|exists:warehouses,id',
            'cooker_sort_order' => 'required|integer|min:1',
            'cooker_is_active' => 'boolean',
        ]);

        PetisCooker::updateOrCreate(
            ['id' => $this->cooker_id],
            [
                'warehouse_id' => $this->cooker_warehouse_id,
                'name' => $this->cooker_name,
                'sort_order' => $this->cooker_sort_order,
                'is_active' => (bool) $this->cooker_is_active,
            ]
        );

        $this->modal('cooker-modal')->close();

        Flux::toast(
            heading: 'Berhasil',
            text: $this->cooker_id ? 'Data koki petis berhasil diperbarui.' : 'Koki petis baru berhasil ditambahkan.',
            variant: 'success'
        );
    }

    public function toggleCookerActive($id)
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

    public function deleteCooker($id)
    {
        Gate::authorize('petis_cookers:delete');
        $cooker = PetisCooker::findOrFail($id);
        $cooker->delete(); // Soft delete keeps past production records safe

        Flux::toast(
            heading: 'Koki Dihapus',
            text: "Koki {$cooker->name} telah dihapus dari daftar aktif. Riwayat masa lalu tetap aman.",
            variant: 'success'
        );
    }

    #[Computed]
    public function warehouses()
    {
        return Warehouse::orderBy('name')->pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function teams()
    {
        $query = Team::with('warehouse');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->filter_warehouse_id) {
            $query->where('warehouse_id', $this->filter_warehouse_id);
        }

        if ($this->sortBy) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        } else {
            $query->orderBy('name');
        }

        return $query->paginate(10, pageName: 'teams_page');
    }

    #[Computed]
    public function cookers()
    {
        $query = PetisCooker::with('warehouse');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->filter_warehouse_id) {
            $query->where('warehouse_id', $this->filter_warehouse_id);
        }

        return $query->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10, pageName: 'cookers_page');
    }

    public function render()
    {
        return view('livewire.teams.index');
    }
}
