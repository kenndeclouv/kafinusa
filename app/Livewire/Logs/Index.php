<?php

namespace App\Livewire\Logs;

use App\Services\LogService;
use Livewire\Component;

class Index extends Component
{
    public string $sortBy = 'modified';
    public string $sortDirection = 'desc';

    public function mount()
    {
        $this->authorize('logs.view');
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

    public function delete(string $filename, LogService $service)
    {
        $this->authorize('logs.delete');

        if ($service->deleteLogFile($filename)) {
            $this->dispatch('alert', 'success', 'Log file deleted successfully!');
        } else {
            $this->dispatch('alert', 'error', 'Failed to delete log file.');
        }
    }

    public function download(string $filename)
    {
        $this->authorize('logs.export');

        $filePath = storage_path("logs/{$filename}");

        if (file_exists($filePath)) {
            return response()->download($filePath);
        }

        $this->dispatch('alert', 'error', 'Log file not found.');
    }

    public function render(LogService $service)
    {
        $logs = collect($service->getLogFiles());

        if ($this->sortBy) {
            $logs = $this->sortDirection === 'asc' 
                ? $logs->sortBy($this->sortBy)
                : $logs->sortByDesc($this->sortBy);
        }

        return view('livewire.logs.index', [
            'logs' => $logs->all(),
            'stats' => $service->getStats(),
        ])
            ->title('System Logs');
    }
}
