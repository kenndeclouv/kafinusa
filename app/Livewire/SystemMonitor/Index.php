<?php

namespace App\Livewire\SystemMonitor;

use App\Services\SystemMonitorService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public function mount()
    {
        // Require permissions if needed, or just admin access
        // $this->authorize('system_monitor:read');
    }

    #[Computed]
    public function metrics()
    {
        $service = new SystemMonitorService();
        return $service->getSystemMetrics();
    }

    public function clearCache()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            \Flux::toast(heading: 'Berhasil', text: 'Semua cache (application, route, config, view) telah dibersihkan.', variant: 'success');
        } catch (\Exception $e) {
            \Flux::toast(heading: 'Error', text: 'Gagal membersihkan cache: ' . $e->getMessage(), variant: 'danger');
        }
    }

    public function render()
    {
        return view('livewire.system-monitor.index')
            ->title('System Monitor');
    }
}
