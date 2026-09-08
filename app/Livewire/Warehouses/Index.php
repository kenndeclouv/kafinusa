<?php

namespace App\Livewire\Warehouses;

use App\Models\Warehouse;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    public $warehouseId;
    public $name = '';
    public $description = '';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
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

    public function openCreateModal()
    {
        $this->reset(['warehouseId', 'name', 'description']);
        $this->resetValidation();
        $this->modal('create-warehouse-modal')->show();
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $warehouse = Warehouse::findOrFail($id);
        $this->warehouseId = $warehouse->id;
        $this->name = $warehouse->name;
        $this->description = $warehouse->description;
        $this->modal('create-warehouse-modal')->show();
    }

    public function save()
    {
        $this->validate();

        Warehouse::updateOrCreate(
            ['id' => $this->warehouseId],
            [
                'name' => $this->name,
                'description' => $this->description,
            ]
        );

        $this->modal('create-warehouse-modal')->close();
        Flux::toast(heading: 'Berhasil', text: 'Gudang berhasil disimpan.', variant: 'success');
    }

    public function delete($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();
        Flux::toast(heading: 'Berhasil', text: 'Gudang berhasil dihapus.', variant: 'success');
    }

    #[Computed]
    public function warehouses()
    {
        return Warehouse::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.warehouses.index');
    }
}
