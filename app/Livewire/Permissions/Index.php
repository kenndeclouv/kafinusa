<?php

namespace App\Livewire\Permissions;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Permission;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $name = '';
    public $showPermissionModal = false;

    public $sortBy = 'name';
    public $sortDirection = 'asc';

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function rules()
    {
        return [
            'name' => 'required|string|unique:permissions,name',
        ];
    }

    public function openCreatePermissionModal()
    {
        $this->reset(['name']);
        $this->resetValidation();
        $this->showPermissionModal = true;
    }

    public function savePermission()
    {
        $this->validate();
        Permission::create(['name' => $this->name]);
        $this->showPermissionModal = false;

        Flux::toast(heading: 'Success', text: 'Permission saved successfully.', variant: 'success');
    }

    public function deletePermission($id)
    {
        Permission::findOrFail($id)->delete();
        Flux::toast(heading: 'Success', text: 'Izin berhasil dihapus.', variant: 'success');
    }

    #[Computed]
    public function permissions()
    {
        return Permission::query()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.permissions.index');
    }
}
