<?php

namespace App\Livewire\Employees;

use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    public $editingEmployeeId = null;

    // Employee details
    public $employee_id_number = '';
    public $name = '';
    public $phone_number = '';
    public $position = '';
    public $status = true;

    // User details
    public $has_user = false;
    public $create_user = false;
    public $email = '';
    public $password = '';
    public $role = '';

    protected function rules()
    {
        $rules = [
            'employee_id_number' => 'required|string|max:255|unique:employees,employee_id_number' . ($this->editingEmployeeId ? ',' . $this->editingEmployeeId : ''),
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'status' => 'boolean',
            'create_user' => 'boolean',
        ];

        if ($this->create_user) {
            if ($this->editingEmployeeId && $this->has_user) {
                $employee = Employee::find($this->editingEmployeeId);
                $rules['email'] = 'required|email|unique:users,email,' . $employee->user_id;
                $rules['password'] = 'nullable|min:8'; // optional if editing
            } else {
                $rules['email'] = 'required|email|unique:users,email';
                $rules['password'] = 'required|min:8';
            }
            $rules['role'] = 'required|exists:roles,name';
        }

        return $rules;
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

    public function addEmployee()
    {
        $this->reset([
            'editingEmployeeId', 'employee_id_number', 'name', 'phone_number', 
            'position', 'has_user', 'create_user', 'email', 'password', 'role'
        ]);
        $this->status = true;
        $this->resetValidation();
        $this->modal('create-employee-modal')->show();
    }

    public function editEmployee($id)
    {
        $this->resetValidation();
        $employee = Employee::findOrFail($id);
        
        $this->editingEmployeeId = $employee->id;
        $this->employee_id_number = $employee->employee_id_number;
        $this->name = $employee->name;
        $this->phone_number = $employee->phone_number;
        $this->position = $employee->position;
        $this->status = (bool) $employee->status;

        $this->has_user = false;
        $this->create_user = false;
        $this->email = '';
        $this->password = '';
        $this->role = '';

        if ($employee->user_id) {
            $this->has_user = true;
            $this->create_user = true;
            $this->email = $employee->user->email;
            
            $userRole = $employee->user->roles->first();
            if ($userRole) {
                $this->role = $userRole->name;
            }
        }
        
        $this->modal('create-employee-modal')->show();
    }

    public function save()
    {
        $this->validate();

        \Illuminate\Support\Facades\DB::transaction(function () {
            $employee = $this->editingEmployeeId ? Employee::findOrFail($this->editingEmployeeId) : new Employee();
            $userId = $employee->user_id;
            
            if ($this->create_user) {
                if ($this->has_user && $userId) {
                    $user = \App\Models\User::find($userId);
                    $userData = [
                        'name' => $this->name,
                        'email' => $this->email,
                    ];
                    if (!empty($this->password)) {
                        $userData['password'] = \Illuminate\Support\Facades\Hash::make($this->password);
                    }
                    $user->update($userData);
                    $user->syncRoles([$this->role]);
                } else {
                    $user = \App\Models\User::create([
                        'name' => $this->name,
                        'email' => $this->email,
                        'password' => \Illuminate\Support\Facades\Hash::make($this->password),
                    ]);
                    $user->assignRole($this->role);
                    $userId = $user->id;
                }
            }

            $employee->fill([
                'user_id' => $userId,
                'employee_id_number' => $this->employee_id_number,
                'name' => $this->name,
                'phone_number' => $this->phone_number,
                'position' => $this->position,
                'status' => $this->status,
            ])->save();
        });

        Flux::toast(heading: 'Success', text: $this->editingEmployeeId ? 'Pegawai berhasil diperbarui.' : 'Pegawai berhasil ditambahkan.', variant: 'success');
        $this->modal('create-employee-modal')->close();
    }

    public function delete($id)
    {
        Gate::authorize('employees:delete');
        $employee = Employee::findOrFail($id);
        
        try {
            $employee->delete();
            Flux::toast(heading: 'Success', text: 'Pegawai berhasil dihapus.', variant: 'success');
        } catch (\Exception $e) {
            Flux::toast(heading: 'Error', text: 'Tidak dapat menghapus pegawai ini.', variant: 'danger');
        }
    }

    #[Computed]
    public function employees()
    {
        return Employee::with('user')
            ->when($this->search, fn($q) => $q->search($this->search))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    #[Computed]
    public function roles()
    {
        return \Spatie\Permission\Models\Role::orderBy('name')->pluck('name', 'name')->toArray();
    }

    public function render()
    {
        return view('livewire.employees.index');
    }
}
