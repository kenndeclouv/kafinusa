<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    public $editingUserId = null;

    // Form fields
    public $name = '';
    public $email = '';
    public $password = '';
    public $roles = [];

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($this->editingUserId ?? 'NULL'),
            'password' => $this->editingUserId ? 'nullable|string|min:8' : 'required|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
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

    public function addUser()
    {
        $this->reset(['editingUserId', 'name', 'email', 'password', 'roles']);
        $this->resetValidation();
        $this->modal('create-user-modal')->show();
    }

    public function editUser($id)
    {
        $this->resetValidation();
        $user = User::findOrFail($id);
        
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->roles = $user->roles->pluck('name')->toArray();
        
        $this->modal('create-user-modal')->show();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if (!empty($this->password)) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($this->password);
        }

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->update($data);
            $user->syncRoles($this->roles);
            Flux::toast(heading: 'Success', text: 'Pengguna berhasil diperbarui.', variant: 'success');
        } else {
            $user = User::create($data);
            $user->syncRoles($this->roles);
            Flux::toast(heading: 'Success', text: 'Pengguna berhasil ditambahkan.', variant: 'success');
        }

        $this->modal('create-user-modal')->close();
    }

    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
        Flux::toast(heading: 'Success', text: 'Pengguna berhasil dihapus.', variant: 'success');
    }

    #[Computed]
    public function users()
    {
        return User::with('roles')
            ->when($this->search, fn($q) => $q->search($this->search))
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    #[Computed]
    public function roleOptions()
    {
        return Role::pluck('name', 'name')->map(fn($name) => \Illuminate\Support\Str::headline($name))->toArray();
    }

    public function render()
    {
        return view('livewire.users.index');
    }
}
