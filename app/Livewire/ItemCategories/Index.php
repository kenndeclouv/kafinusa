<?php

namespace App\Livewire\ItemCategories;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\ItemCategory;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $categoryId;
    public $name = '';

    public $sortBy = 'name';
    public $sortDirection = 'asc';

    public function rules()
    {
        return [
            'name' => 'required|string|unique:item_categories,name,' . $this->categoryId,
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
        $this->reset(['categoryId', 'name']);
        $this->resetValidation();
        $this->modal('create-item-category-modal')->show();
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $category = ItemCategory::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->modal('create-item-category-modal')->show();
    }

    public function save()
    {
        $this->validate();

        ItemCategory::updateOrCreate(
            ['id' => $this->categoryId],
            ['name' => $this->name]
        );

        $this->modal('create-item-category-modal')->close();

        Flux::toast(heading: 'Success', text: 'Category saved successfully.', variant: 'success');
    }

    public function delete($id)
    {
        ItemCategory::findOrFail($id)->delete();
        Flux::toast(heading: 'Success', text: 'Kategori berhasil dihapus.', variant: 'success');
    }

    #[Computed]
    public function categories()
    {
        return ItemCategory::query()
            ->tap(fn($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.item-categories.index');
    }
}
