<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $sortBy = 'name';
    public $sortDirection = 'asc';

    public $editingRoleId = null;
    public $name = '';
    public $selectedPermissions = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|unique:roles,name' . ($this->editingRoleId ? ',' . $this->editingRoleId : ''),
            'selectedPermissions' => 'array',
        ];
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

    public function toggleAll($module)
    {
        $permissionsInModule = \Spatie\Permission\Models\Permission::where('name', 'like', strtolower($module) . ':%')->pluck('name')->toArray();
        $hasAll = empty(array_diff($permissionsInModule, $this->selectedPermissions));
        
        if ($hasAll) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $permissionsInModule));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $permissionsInModule)));
        }
    }

    public function addRole()
    {
        $this->reset(['editingRoleId', 'name', 'selectedPermissions']);
        $this->resetValidation();
        $this->modal('create-role-modal')->show();
    }

    public function editRole($id)
    {
        $this->resetValidation();
        $role = Role::findOrFail($id);
        
        $this->editingRoleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        
        $this->modal('create-role-modal')->show();
    }

    public function save()
    {
        $this->validate();

        if ($this->editingRoleId) {
            $role = Role::findOrFail($this->editingRoleId);
            $role->update(['name' => $this->name]);
            $role->syncPermissions($this->selectedPermissions);
            Flux::toast(heading: 'Success', text: 'Peran berhasil diperbarui.', variant: 'success');
        } else {
            $role = Role::create(['name' => $this->name]);
            $role->syncPermissions($this->selectedPermissions);
            Flux::toast(heading: 'Success', text: 'Peran berhasil ditambahkan.', variant: 'success');
        }

        $this->modal('create-role-modal')->close();
    }

    public function deleteRole($id)
    {
        Role::findOrFail($id)->delete();
        Flux::toast(heading: 'Success', text: 'Peran berhasil dihapus.', variant: 'success');
    }

    #[Computed]
    public function roles()
    {
        return Role::query()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    #[Computed]
    public function modules()
    {
        $permissions = \Spatie\Permission\Models\Permission::all();
        $modules = [];
        
        foreach ($permissions as $perm) {
            $parts = explode(':', $perm->name);
            $module = strtoupper($parts[0]);
            if (!isset($modules[$module])) {
                $modules[$module] = [];
            }
            $modules[$module][] = $perm;
        }

        return $modules;
    }

    public function render()
    {
        return view('livewire.roles.index');
    }
}
