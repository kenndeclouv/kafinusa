<?php

namespace App\Livewire\CustomerCategories;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\CustomerCategory;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $categoryId;
    public $code = '';
    public $name = '';

    public $sortBy = 'code';
    public $sortDirection = 'asc';

    public function rules()
    {
        return [
            'code' => 'required|string|unique:customer_categories,code,' . $this->categoryId,
            'name' => 'required|string',
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

    public function openCreateModal()
    {
        $this->reset(['categoryId', 'code', 'name']);
        $this->resetValidation();
        $this->modal('create-customer-category-modal')->show();
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $category = CustomerCategory::findOrFail($id);
        $this->categoryId = $category->id;
        $this->code = $category->code;
        $this->name = $category->name;
        $this->modal('create-customer-category-modal')->show();
    }

    public function save()
    {
        $this->validate();

        CustomerCategory::updateOrCreate(
            ['id' => $this->categoryId],
            ['code' => $this->code, 'name' => $this->name]
        );

        $this->modal('create-customer-category-modal')->close();

        Flux::toast(heading: 'Success', text: 'Kategori berhasil disimpan.', variant: 'success');
    }

    public function delete($id)
    {
        CustomerCategory::findOrFail($id)->delete();
        Flux::toast(heading: 'Success', text: 'Kategori berhasil dihapus.', variant: 'success');
    }

    #[Computed]
    public function categories()
    {
        return CustomerCategory::query()
            ->tap(fn($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.customer-categories.index');
    }
}
