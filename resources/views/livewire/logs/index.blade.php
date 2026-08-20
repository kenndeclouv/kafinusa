<div>
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">System Logs</flux:heading>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <flux:card class="flex flex-col items-center justify-center p-4">
            <span class="text-zinc-500 dark:text-zinc-400 text-sm font-medium">Local</span>
            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['local'] }}</span>
        </flux:card>
        <flux:card class="flex flex-col items-center justify-center p-4">
            <span class="text-zinc-500 dark:text-zinc-400 text-sm font-medium">Production</span>
            <span class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['production'] }}</span>
        </flux:card>
        <flux:card class="flex flex-col items-center justify-center p-4">
            <span class="text-zinc-500 dark:text-zinc-400 text-sm font-medium">Errors</span>
            <span class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['error'] }}</span>
        </flux:card>
        <flux:card class="flex flex-col items-center justify-center p-4">
            <span class="text-zinc-500 dark:text-zinc-400 text-sm font-medium">Warnings</span>
            <span class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['warning'] }}</span>
        </flux:card>
        <flux:card class="flex flex-col items-center justify-center p-4">
            <span class="text-zinc-500 dark:text-zinc-400 text-sm font-medium">Info</span>
            <span class="text-2xl font-bold text-cyan-600 dark:text-cyan-400">{{ $stats['info'] }}</span>
        </flux:card>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        {{-- Card Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-5 border-b border-zinc-200 dark:border-white/10">
            <div class="flex items-center gap-2">
                <flux:icon name="document-text" class="w-5 h-5 text-zinc-400" />
                <h3 class="font-semibold text-zinc-900 dark:text-white">Log Files</h3>
                @if (count($logs) > 0)
                    <flux:badge color="zinc" size="sm" class="ml-1">{{ count($logs) }}</flux:badge>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <flux:table class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">File Name</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'size'" :direction="$sortDirection" wire:click="sort('size')">Size (KB)</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'modified'" :direction="$sortDirection" wire:click="sort('modified')">Last Modified</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($logs as $log)
                    <flux:table.row :key="$log['name']">
                        <flux:table.cell>
                            <div class="flex items-center gap-2">
                                <flux:icon name="document-text" class="w-4 h-4 text-zinc-400" />
                                <span class="font-medium">{{ $log['name'] }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $log['size'] }} KB
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ date('d F Y H:i:s', $log['modified']) }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                    inset="top bottom"></flux:button>

                                <flux:menu>
                                    @can('logs.view')
                                        <flux:menu.item icon="eye" :href="route('logs.show', $log['name'])"
                                            wire:navigate.hover>
                                            View Log
                                        </flux:menu.item>
                                    @endcan

                                    @can('logs.export')
                                        <flux:menu.item icon="arrow-down-tray" wire:click="download('{{ $log['name'] }}')">
                                            Download
                                        </flux:menu.item>
                                    @endcan

                                    @can('logs.delete')
                                        <flux:menu.separator />
                                        <x-delete-modal 
                                            id="delete-log-{{ \Illuminate\Support\Str::slug($log['name']) }}" 
                                            action="delete('{{ $log['name'] }}')" 
                                            title="Hapus Log?" 
                                            description="Anda yakin ingin menghapus file log ini? Tindakan ini tidak dapat dibatalkan.">
                                            <flux:menu.item icon="trash" variant="danger">Delete Log</flux:menu.item>
                                        </x-delete-modal>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">
                            <div class="text-center py-12">
                                <flux:heading size="lg" class="mt-4">No log files found</flux:heading>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
            </flux:table>
        </div>

        {{-- Pagination --}}
        @if (is_object($logs) && method_exists($logs, 'hasPages') && $logs->hasPages())
            <div class="px-6 py-4 border-t border-zinc-200 dark:border-white/10">
                <flux:pagination :paginator="$logs" />
            </div>
        @elseif(count($logs) > 0)
            <div class="px-6 py-4 border-t border-zinc-200 dark:border-white/10">
                <p class="text-sm text-zinc-500">Showing all {{ count($logs) }} results</p>
            </div>
        @endif
    </div>
</div>
