<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">System Monitor</flux:heading>
            <flux:subheading>Real-time resource utilization</flux:subheading>
        </div>
        <flux:button wire:click="$refresh" icon="arrow-path" size="sm">Refresh</flux:button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6" wire:poll.3s="$refresh">
        {{-- CPU --}}
        <flux:card class="flex flex-col gap-4">
            <div class="flex justify-between items-start">
                <div class="bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 p-3 rounded-xl">
                    <flux:icon.cpu-chip class="w-6 h-6" />
                </div>
                <flux:badge color="{{ $this->metrics['cpu']['status'] }}" size="sm">
                    {{ $this->metrics['cpu']['percentage'] }}%
                </flux:badge>
            </div>
            <div>
                <flux:heading size="lg">CPU Usage</flux:heading>
                <flux:text class="mt-1">Current processing load.</flux:text>
            </div>
            <div class="mt-2 h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 ease-out {{ $this->metrics['cpu']['status'] === 'danger' ? 'bg-red-500' : ($this->metrics['cpu']['status'] === 'warning' ? 'bg-amber-500' : 'bg-blue-500') }}" style="width: {{ $this->metrics['cpu']['percentage'] }}%"></div>
            </div>
        </flux:card>

        {{-- Memory --}}
        <flux:card class="flex flex-col gap-4">
            <div class="flex justify-between items-start">
                <div class="bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 p-3 rounded-xl">
                    <flux:icon.server class="w-6 h-6" />
                </div>
                <flux:badge color="{{ $this->metrics['memory']['status'] }}" size="sm">
                    {{ $this->metrics['memory']['percentage'] }}%
                </flux:badge>
            </div>
            <div>
                <flux:heading size="lg">Memory (RAM)</flux:heading>
                <flux:text class="mt-1">{{ $this->metrics['memory']['formatted_used'] }} / {{ $this->metrics['memory']['formatted_total'] }}</flux:text>
            </div>
            <div class="mt-2 h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 ease-out {{ $this->metrics['memory']['status'] === 'danger' ? 'bg-red-500' : ($this->metrics['memory']['status'] === 'warning' ? 'bg-amber-500' : 'bg-indigo-500') }}" style="width: {{ $this->metrics['memory']['percentage'] }}%"></div>
            </div>
        </flux:card>

        {{-- Disk --}}
        <flux:card class="flex flex-col gap-4">
            <div class="flex justify-between items-start">
                <div class="bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 p-3 rounded-xl">
                    <flux:icon.hard-drive class="w-6 h-6" />
                </div>
                <flux:badge color="{{ $this->metrics['disk']['status'] }}" size="sm">
                    {{ $this->metrics['disk']['percentage'] }}%
                </flux:badge>
            </div>
            <div>
                <flux:heading size="lg">Disk Space</flux:heading>
                <flux:text class="mt-1">{{ $this->metrics['disk']['formatted_used'] }} / {{ $this->metrics['disk']['formatted_total'] }}</flux:text>
            </div>
            <div class="mt-2 h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 ease-out {{ $this->metrics['disk']['status'] === 'danger' ? 'bg-red-500' : ($this->metrics['disk']['status'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $this->metrics['disk']['percentage'] }}%"></div>
            </div>
        </flux:card>
    </div>
</div>
