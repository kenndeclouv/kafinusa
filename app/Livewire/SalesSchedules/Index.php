<?php

namespace App\Livewire\SalesSchedules;

use App\Models\Employee;
use App\Models\Market;
use App\Models\SalesSchedule;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Flux\Flux;

class Index extends Component
{
    public $salesId = null;
    public $dayOfWeek = '';
    public $marketId = '';

    public function mount()
    {
        abort_unless(auth()->user() && auth()->user()->hasPermissionTo('sales_schedules:read'), 403, 'Unauthorized.');
    }

    // Day of week mapping
    public $days = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        0 => 'Minggu',
    ];

    protected $rules = [
        'salesId' => 'required|exists:employees,id',
        'dayOfWeek' => 'required|integer|between:0,6',
        'marketId' => 'required|exists:markets,id',
    ];

    public function addSchedule()
    {
        $this->validate();

        $exists = SalesSchedule::where('employee_id', $this->salesId)
            ->where('day_of_week', $this->dayOfWeek)
            ->where('market_id', $this->marketId)
            ->exists();

        if ($exists) {
            Flux::toast(heading: 'Error', text: 'Jadwal ini sudah ada!', variant: 'danger');
            return;
        }

        SalesSchedule::create([
            'employee_id' => $this->salesId,
            'day_of_week' => $this->dayOfWeek,
            'market_id' => $this->marketId,
        ]);

        Flux::toast(heading: 'Success', text: 'Jadwal berhasil ditambahkan.', variant: 'success');
        $this->reset(['marketId']);
    }

    public function removeSchedule($id)
    {
        SalesSchedule::findOrFail($id)->delete();
        Flux::toast(heading: 'Success', text: 'Jadwal berhasil dihapus.', variant: 'success');
    }

    #[Computed]
    public function sales()
    {
        return Employee::orderBy('name')->get();
    }

    #[Computed]
    public function markets()
    {
        return Market::orderBy('name')->get();
    }

    #[Computed]
    public function schedules()
    {
        $data = SalesSchedule::with(['employee', 'market'])->get();
        // Kelompokkan per sales, lalu per hari
        $grouped = [];
        foreach ($data as $schedule) {
            $grouped[$schedule->employee_id][$schedule->day_of_week][] = $schedule;
        }
        return $grouped;
    }

    public function render()
    {
        return view('livewire.sales-schedules.index');
    }
}
