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
                <flux:text class="mt-1 text-xs">{{ $this->metrics['cpu']['model'] }}</flux:text>
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
                <flux:text class="mt-1 text-xs truncate" title="{{ $this->metrics['memory']['model'] }}">{{ $this->metrics['memory']['model'] }} &middot; {{ $this->metrics['memory']['formatted_used'] }} / {{ $this->metrics['memory']['formatted_total'] }}</flux:text>
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
                <flux:text class="mt-1 text-xs truncate" title="{{ $this->metrics['disk']['model'] }}">{{ $this->metrics['disk']['model'] }} &middot; {{ $this->metrics['disk']['formatted_used'] }} / {{ $this->metrics['disk']['formatted_total'] }}</flux:text>
            </div>
            <div class="mt-2 h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 ease-out {{ $this->metrics['disk']['status'] === 'danger' ? 'bg-red-500' : ($this->metrics['disk']['status'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $this->metrics['disk']['percentage'] }}%"></div>
            </div>
        </flux:card>
    </div>

    <div class="mt-6" wire:poll.10s="$refresh">
        <flux:card>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 pb-6 border-b border-zinc-200 dark:border-white/10 gap-4">
                <div>
                    <flux:heading size="lg">System Configuration & Status</flux:heading>
                    <flux:subheading>Detailed information about the application and database environment.</flux:subheading>
                </div>
                <flux:button wire:click="clearCache" wire:loading.attr="disabled" size="sm" variant="outline" icon="trash">
                    Bersihkan Cache
                </flux:button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-12 gap-y-8">
                <!-- App Status List -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 rounded-lg bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400">
                            <flux:icon.command-line class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Application</h3>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Environment</span>
                            <flux:badge color="{{ $this->metrics['app_status']['debug_mode'] ? 'amber' : 'green' }}" size="sm">{{ $this->metrics['app_status']['environment'] }}</flux:badge>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">PHP Version</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->metrics['app_status']['php_version'] }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Laravel Version</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->metrics['app_status']['laravel_version'] }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Server Uptime</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->metrics['app_status']['uptime'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Database List -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400">
                            <flux:icon.circle-stack class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Database</h3>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Status</span>
                            <flux:badge color="{{ $this->metrics['database']['status'] === 'success' ? 'green' : 'red' }}" size="sm">{{ $this->metrics['database']['status'] === 'success' ? 'Connected' : 'Error' }}</flux:badge>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Driver</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100 uppercase">{{ $this->metrics['database']['driver'] }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Database Name</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->metrics['database']['database'] }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Total Size</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->metrics['database']['size'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Cache & Session List -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <flux:icon.bolt class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Cache & Session</h3>
                    </div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Cache Driver</span>
                            <flux:badge color="zinc" size="sm">{{ strtoupper($this->metrics['cache_session']['cache_driver']) }}</flux:badge>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Session Driver</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100 uppercase">{{ $this->metrics['cache_session']['session_driver'] }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">Active Sessions</span>
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $this->metrics['cache_session']['active_sessions'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </flux:card>
    </div>
</div>
