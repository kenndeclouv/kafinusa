<?php

namespace App\Livewire\Teams;

use Livewire\Component;
use App\Models\Team;
use App\Models\Warehouse;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filter_warehouse_id = null;

    public $sortBy = 'name';
    public $sortDirection = 'asc';

    // Form fields
    public $team_id;
    public $name;
    public $warehouse_id;

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function mount()
    {
        // Auth check if needed
    }

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

    public function save()
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

        return $query->paginate(10);
    }

    public function render()
    {
        return view('livewire.teams.index');
    }
}
