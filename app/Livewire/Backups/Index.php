<?php

namespace App\Livewire\Backups;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\MonthlyBackup;
use Flux\Flux;

class Index extends Component
{
    use WithPagination;

    public $monthToLock;

    public function mount()
    {
        abort_unless(auth()->user() && auth()->user()->can('backups:read'), 403, 'Unauthorized.');
        $this->monthToLock = now()->format('Y-m');
    }

    public function lockMonth()
    {
        abort_unless(auth()->user()->can('backups:create'), 403);
        
        $this->validate([
            'monthToLock' => 'required|date_format:Y-m',
        ]);

        if (MonthlyBackup::where('month', $this->monthToLock)->exists()) {
            Flux::toast(heading: 'Error', text: 'Bulan ini sudah ditutup sebelumnya.', variant: 'danger');
            return;
        }

        MonthlyBackup::create([
            'month' => $this->monthToLock,
            'locked_at' => now(),
        ]);

        \Illuminate\Support\Facades\Cache::forget("is_locked_{$this->monthToLock}");
        \Illuminate\Support\Facades\Cache::forget('all_locked_months');

        $this->monthToLock = now()->format('Y-m');
        Flux::toast(heading: 'Success', text: 'Tutup buku berhasil dilakukan.', variant: 'success');
        $this->modal('create-backup-modal')->close();
    }

    public function deleteBackup($id)
    {
        abort_unless(auth()->user()->can('backups:delete'), 403);

        $backup = MonthlyBackup::findOrFail($id);
        
        \Illuminate\Support\Facades\Cache::forget("is_locked_{$backup->month}");
        \Illuminate\Support\Facades\Cache::forget('all_locked_months');
        
        $backup->delete();

        Flux::toast(heading: 'Success', text: 'Status tutup buku berhasil dibatalkan.', variant: 'success');
    }

    #[Computed]
    public function backups()
    {
        return MonthlyBackup::orderBy('month', 'desc')->paginate(10);
    }

    public function render()
    {
        return view('livewire.backups.index');
    }
}
