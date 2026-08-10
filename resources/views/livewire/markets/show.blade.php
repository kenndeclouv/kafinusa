<div>
    <!-- Market Info Card -->
    <flux:card class="mb-8 p-6 relative overflow-hidden">
        <!-- Background Decoration for premium feel -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 text-zinc-100 dark:text-zinc-800/50 pointer-events-none transition-all">
            <flux:icon.building-storefront class="w-64 h-64 opacity-50" />
        </div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-start justify-between gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-4 mb-6">
                    <flux:button href="{{ route('markets.index') }}" variant="ghost" icon="arrow-left" class="hidden sm:flex" />
                    <div class="flex items-center gap-4">
                        <div class="rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 p-3 shadow-md">
                            <flux:icon.building-storefront class="w-6 h-6" />
                        </div>
                        <div>
                            <flux:heading size="xl" class="!font-bold">{{ $market->name }}</flux:heading>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Informasi Data Pasar</flux:text>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-2">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 p-2 text-zinc-500 dark:text-zinc-400">
                            <flux:icon.qr-code class="w-4 h-4" />
                        </div>
                        <div>
                            <flux:text class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Kode Pasar</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $market->code }}</flux:text>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 p-2 text-zinc-500 dark:text-zinc-400">
                            <flux:icon.map-pin class="w-4 h-4" />
                        </div>
                        <div>
                            <flux:text class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Alamat</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $market->address ?: 'Belum ada alamat' }}</flux:text>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col gap-3 sm:min-w-60 mt-4 sm:mt-0">
                <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-white/10 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-300">Total Pelanggan</flux:text>
                        <div class="rounded-full bg-zinc-200/50 dark:bg-zinc-700 p-1.5">
                            <flux:icon.users class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <flux:heading size="2xl" class="!font-bold">{{ $market->customers()->count() }}</flux:heading>
                    </div>
                </div>
            </div>
        </div>
    </flux:card>

    <!-- Header List Pelanggan -->
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <flux:heading size="lg">Daftar Pelanggan</flux:heading>
            <flux:badge size="sm" color="zinc" class="rounded-full">{{ $this->customers->total() }}</flux:badge>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari pelanggan..."
                icon="magnifying-glass" class="w-full sm:w-72" />
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table
                class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                        wire:click="sort('name')">Nama Pelanggan</flux:table.column>
                    <flux:table.column>Aktivitas Order</flux:table.column>
                    <flux:table.column>Kategori</flux:table.column>
                    {{-- <flux:table.column>User</flux:table.column>
                    <flux:table.column>Status</flux:table.column> --}}
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->customers as $customer)
                        @php
                            $streak = $customer->getMissedStreak($this->marketOrderBookIds);
                            $rowClass = '';
                            if ($streak >= 4) {
                                $rowClass = 'bg-red-50/50 dark:bg-red-900/10 hover:bg-red-50 dark:hover:bg-red-900/20';
                            } elseif ($streak >= 2) {
                                $rowClass = 'bg-amber-50/50 dark:bg-amber-900/10 hover:bg-amber-50 dark:hover:bg-amber-900/20';
                            }
                        @endphp
                        <flux:table.row :key="$customer->id" class="{{ $rowClass }}">
                            <flux:table.cell class="font-medium text-zinc-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    {{ $customer->name }}
                                    @if($streak >= 4)
                                        <div class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></div>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($streak === 0)
                                    <flux:badge size="sm" color="success">Aktif (Order)</flux:badge>
                                @elseif($streak === 1)
                                    <flux:badge size="sm" color="zinc">Absen 1x</flux:badge>
                                @elseif($streak >= 4)
                                    <flux:badge size="sm" color="danger">Absen {{ $streak }}x</flux:badge>
                                @else
                                    <flux:badge size="sm" color="warning">Absen {{ $streak }}x</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($customer->category)
                                    <flux:badge size="sm" color="zinc">{{ $customer->category->name }}</flux:badge>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </flux:table.cell>
                            {{-- <flux:table.cell>
                                @if($customer->user)
                                    {{ $customer->user->name }}
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($customer->status === 'active')
                                    <flux:badge size="sm" color="success">Aktif</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc">Nonaktif</flux:badge>
                                @endif
                            </flux:table.cell> --}}
                        </flux:table.row>
                    @empty
                        <tr>
                            <td colspan="100%" class="py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-3 mb-4">
                                        <flux:icon.users class="w-6 h-6 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <flux:heading size="lg">Belum ada pelanggan</flux:heading>
                                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                        Tidak ada pelanggan yang terdaftar di pasar ini.
                                    </flux:text>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->customers->total() > 0)
            <flux:pagination :paginator="$this->customers" class="p-4" />
        @endif
    </div>
</div>
