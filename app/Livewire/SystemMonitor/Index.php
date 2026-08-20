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

    public function render()
    {
        return view('livewire.system-monitor.index')
            ->title('System Monitor');
    }
}
