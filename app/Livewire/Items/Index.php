<?php

namespace App\Livewire\Items;

use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $sortBy = 'code';
    public $sortDirection = 'asc';

    public $itemId;
    public $item_category_id = '';
    public $code = '';
    public $name = '';
    public $weight = '';
    public $photo;
    public $existingPhoto;
    
    public $prices = [];

    public $showItem = null;

    public function rules()
    {
        return [
            'item_category_id' => 'required|exists:item_categories,id',
            'code' => 'required|string|max:50|unique:items,code,' . $this->itemId,
            'name' => 'required|string|max:255',
            'weight' => 'required|integer|min:0',
            'photo' => 'nullable|image|max:2048',
            'prices' => 'array',
            'prices.*.name' => 'required|string|max:50',
            'prices.*.value' => 'required|integer|min:0',
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
        $this->reset(['itemId', 'item_category_id', 'code', 'name', 'weight', 'photo', 'existingPhoto']);
        $this->prices = [
            ['name' => 'normal', 'value' => ''],
        ];
        $this->resetValidation();
        $this->modal('create-item-modal')->show();
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $item = Item::findOrFail($id);
        $this->itemId = $item->id;
        $this->item_category_id = $item->item_category_id;
        $this->code = $item->code;
        $this->name = $item->name;
        $this->weight = $item->weight;
        $this->reset('photo');
        $this->existingPhoto = $item->photo;
        $this->prices = [];
        if (is_array($item->prices)) {
            foreach ($item->prices as $name => $value) {
                $this->prices[] = ['name' => $name, 'value' => $value];
            }
        }
        $this->modal('create-item-modal')->show();
    }

    public function save()
    {
        $this->validate();

        $pricesJson = [];
        foreach ($this->prices as $price) {
            if (!empty($price['name'])) {
                // Konversi nama menjadi lowercase slug-like untuk key
                $key = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($price['name'])));
                $pricesJson[$key] = (int) $price['value'];
            }
        }

        $data = [
            'item_category_id' => $this->item_category_id,
            'code' => $this->code,
            'name' => $this->name,
            'weight' => $this->weight,
            'prices' => $pricesJson,
        ];

        if ($this->photo) {
            $data['photo'] = $this->photo->store('items', 'public');
        }

        Item::updateOrCreate(
            ['id' => $this->itemId],
            $data
        );

        $this->modal('create-item-modal')->close();

        Flux::toast(heading: 'Success', text: 'Barang berhasil disimpan.', variant: 'success');
    }

    public function delete($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        Flux::toast(heading: 'Success', text: 'Barang berhasil dihapus.', variant: 'success');
    }

    public function addPrice()
    {
        $this->prices[] = ['name' => '', 'value' => ''];
    }

    public function removePrice($index)
    {
        unset($this->prices[$index]);
        $this->prices = array_values($this->prices);
    }

    public function openShowModal($id)
    {
        $this->showItem = Item::with('category')->findOrFail($id);
        $this->modal('detail-item-modal')->show();
    }

    #[Computed]
    public function items()
    {
        return Item::query()
            ->with('category')
            ->when($this->search, fn($q) => $q->search($this->search))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    #[Computed]
    public function categories()
    {
        return ItemCategory::pluck('name', 'id')->toArray();
    }

    public function render()
    {
        return view('livewire.items.index');
    }
}
