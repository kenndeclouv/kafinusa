<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
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

    public $editingCustomerId = null;

    // Form fields
    public $user_id = '';
    public $market_id = '';
    public $customer_category_id = '';
    public $code = '';
    public $name = '';
    public $phone = '';
    public $status = true;

    protected function rules()
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'market_id' => 'required|exists:markets,id',
            'customer_category_id' => 'required|exists:customer_categories,id',
            'code' => 'required|string|max:255|unique:customers,code,' . $this->editingCustomerId,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'status' => 'boolean',
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

    public function addCustomer()
    {
        $this->reset(['editingCustomerId', 'user_id', 'market_id', 'customer_category_id', 'code', 'name', 'phone']);
        $this->status = true;
        $this->resetValidation();
        $this->modal('create-customer-modal')->show();
    }

    public function editCustomer($id)
    {
        $this->resetValidation();
        $customer = Customer::findOrFail($id);
        
        $this->editingCustomerId = $customer->id;
        $this->user_id = $customer->user_id;
        $this->market_id = $customer->market_id;
        $this->customer_category_id = $customer->customer_category_id;
        $this->code = $customer->code;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->status = $customer->status;
        
        $this->modal('create-customer-modal')->show();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => $this->user_id ?: null,
            'market_id' => $this->market_id,
            'customer_category_id' => $this->customer_category_id,
            'code' => $this->code,
            'name' => $this->name,
            'phone' => $this->phone,
            'status' => $this->status,
        ];

        if ($this->editingCustomerId) {
            Customer::findOrFail($this->editingCustomerId)->update($data);
            Flux::toast(heading: 'Success', text: 'Pelanggan berhasil diperbarui.', variant: 'success');
        } else {
            Customer::create($data);
            Flux::toast(heading: 'Success', text: 'Pelanggan berhasil ditambahkan.', variant: 'success');
        }

        $this->modal('create-customer-modal')->close();
    }

    public function delete($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        Flux::toast(heading: 'Success', text: 'Pelanggan berhasil dihapus.', variant: 'success');
    }

    #[Computed]
    public function customers()
    {
        return Customer::query()
            ->with(['market', 'category'])
            ->when($this->search, fn($q) => $q->search($this->search))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    #[Computed]
    public function users()
    {
        return \App\Models\User::pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function markets()
    {
        return \App\Models\Market::pluck('name', 'id')->toArray();
    }

    #[Computed]
    public function categories()
    {
        return \App\Models\CustomerCategory::pluck('name', 'id')->toArray();
    }

    public function render()
    {
        return view('livewire.customers.index');
    }
}
