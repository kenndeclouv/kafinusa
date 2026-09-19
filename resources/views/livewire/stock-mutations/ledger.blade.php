<div>
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <flux:button href="{{ route('stock-mutations.show', $warehouse->id) }}" wire:navigate variant="ghost" icon="arrow-left" size="sm" class="hidden lg:flex" />
                <flux:heading size="xl">Buku Mutasi (Ledger)</flux:heading>
                <flux:badge size="sm" color="zinc" class="rounded-full">{{ $this->mutations->total() }}</flux:badge>
            </div>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 sm:ms-10">
                Gudang: <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $warehouse->name }}</span>
            </flux:text>
        </div>
        <div class="flex flex-col lg:flex-row items-center gap-3 w-full lg:w-auto mt-4 lg:mt-0">
            <!-- Filter Bulan -->
            <div class="w-full sm:w-32">
                <x-searchable-select wire:model.live="month" :options="[
                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                ]" searchable="false" placeholder="Bulan" />
            </div>
            
            <!-- Filter Tahun -->
            <div class="w-full sm:w-28">
                @php
                    $currentYear = date('Y');
                    $years = [];
                    for($i = $currentYear; $i >= 2024; $i--) {
                        $years[$i] = $i;
                    }
                @endphp
                <x-searchable-select wire:model.live="year" :options="$years" searchable="false" placeholder="Tahun" />
            </div>

            <!-- Filter Tipe -->
            <div class="w-full sm:w-36">
                <x-searchable-select wire:model.live="typeFilter" :options="['in' => 'Masuk (In)', 'out' => 'Keluar (Out)']" searchable="false" placeholder="Semua Tipe" />
            </div>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama barang..."
                icon="magnifying-glass" class="w-full lg:w-64" />
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <flux:table
                class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column>Tanggal</flux:table.column>
                    <flux:table.column>Barang</flux:table.column>
                    <flux:table.column>Tipe</flux:table.column>
                    <flux:table.column>Qty</flux:table.column>
                    <flux:table.column>Sisa Stok</flux:table.column>
                    <flux:table.column>User</flux:table.column>
                    <flux:table.column>Catatan</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->mutations as $mutation)
                        <flux:table.row :key="$mutation->id">
                            <flux:table.cell>{{ $mutation->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                            <flux:table.cell>
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $mutation->item?->name }}</span>
                                <div class="text-xs text-zinc-500">{{ $mutation->item?->code }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($mutation->type === 'in')
                                    <flux:badge color="success" size="sm">Masuk</flux:badge>
                                @elseif($mutation->type === 'out')
                                    <flux:badge color="danger" size="sm">Keluar</flux:badge>
                                @else
                                    <flux:badge color="warning" size="sm">Penyesuaian</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="{{ $mutation->type === 'in' ? 'text-green-600' : 'text-red-600' }} font-medium">
                                    {{ $mutation->type === 'in' ? '+' : '-' }}{{ number_format($mutation->quantity) }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>{{ number_format($mutation->balance_after) }}</flux:table.cell>
                            <flux:table.cell>{{ $mutation->user?->name }}</flux:table.cell>
                            <flux:table.cell class="max-w-xs truncate" title="{{ $mutation->notes }}">{{ $mutation->notes ?? '-' }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <tr>
                            <td colspan="100%" class="py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-3 mb-4">
                                        <flux:icon.document-text class="w-6 h-6 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <flux:heading size="lg">Belum ada mutasi</flux:heading>
                                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Tidak ada data mutasi yang ditemukan untuk filter ini.</flux:text>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->mutations->total() > 0)
            <flux:pagination :paginator="$this->mutations" class="p-4 border-t border-zinc-200 dark:border-white/5" />
        @endif
    </div>
</div>
