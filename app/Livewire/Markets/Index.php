<?php

namespace App\Livewire\Markets;

use App\Models\Market;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'code';
    public $sortDirection = 'asc';

    public $marketId;
    public $code = '';
    public $name = '';
    public $address = '';

    public function rules()
    {
        return [
            'code' => 'required|string|max:50|unique:markets,code,' . $this->marketId,
            'name' => 'required|string|max:255',
            'address' => 'required|string',
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
        $this->reset(['marketId', 'code', 'name', 'address']);
        $this->resetValidation();
        $this->modal('create-market-modal')->show();
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $market = Market::findOrFail($id);
        $this->marketId = $market->id;
        $this->code = $market->code;
        $this->name = $market->name;
        $this->address = $market->address;
        $this->modal('create-market-modal')->show();
    }

    public function save()
    {
        $this->validate();

        Market::updateOrCreate(
            ['id' => $this->marketId],
            [
                'code' => $this->code,
                'name' => $this->name,
                'address' => $this->address,
            ]
        );

        $this->modal('create-market-modal')->close();

        Flux::toast(heading: 'Success', text: 'Pasar berhasil disimpan.', variant: 'success');
    }

    public function delete($id)
    {
        $market = Market::findOrFail($id);
        $market->delete();

        Flux::toast(heading: 'Success', text: 'Pasar berhasil dihapus.', variant: 'success');
    }

    #[Computed]
    public function markets()
    {
        return Market::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.markets.index');
    }
}
