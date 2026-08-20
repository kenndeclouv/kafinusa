<?php

namespace App\Livewire\Logs;

use App\Services\LogService;
use Livewire\Component;

class Show extends Component
{
    public $log; // The log file name (passed as route parameter)

    public $filename;

    public $entries = [];

    public function mount($log, LogService $service)
    {
        $this->authorize('logs.view');

        $this->filename = $log;
        $this->entries = $service->getLogContent($this->filename);

        if (empty($this->entries) && ! file_exists(storage_path("logs/{$this->filename}"))) {
            abort(404, 'Log file not found');
        }
    }

    public function render()
    {
        return view('livewire.logs.show')
            ->title('View Logs');
    }
}
