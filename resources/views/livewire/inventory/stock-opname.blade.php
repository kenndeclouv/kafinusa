<div>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Stock Opname</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Kalibrasi fisik gudang untuk Saldo Awal laporan sirkulasi bulanan.</p>
        </div>
        <div class="flex gap-2">
            <flux:button wire:click="save" variant="primary" icon="check">
                Simpan Opname
            </flux:button>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 shadow-sm">
        <x-searchable-select wire:model.live="warehouse_id" label="Gudang" :options="$warehouses" />
        
        <x-searchable-select wire:model.live="item_category_id" label="Kategori Barang" :options="$categories" />
        
        <x-searchable-select wire:model.live="month" label="Bulan" :options="$months" :searchable="false" />

        <x-searchable-select wire:model.live="year" label="Tahun" :options="$years" :searchable="false" />
    </div>

    <!-- Opname Table -->
    <div class="rounded-xl border border-zinc-200 dark:border-white/10 overflow-hidden bg-white dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-zinc-50 dark:bg-zinc-800/50 text-zinc-700 dark:text-zinc-300 border-b border-zinc-200 dark:border-white/10">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Nama Barang</th>
                        <th class="px-4 py-3 font-semibold text-center w-32">Stok Sistem</th>
                        <th class="px-4 py-3 font-semibold text-center w-48">Stok Fisik (Opname)</th>
                        <th class="px-4 py-3 font-semibold text-center w-32">Selisih</th>
                        <th class="px-4 py-3 font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                    @forelse($this->items as $item)
                        @php
                            $sys = $this->getSystemStock($item->id);
                            $act = is_numeric($actualStocks[$item->id] ?? null) ? (float)$actualStocks[$item->id] : $sys;
                            $diff = $act - $sys;
                        @endphp
                        <tr class="hover:bg-zinc-50/50 dark:hover:bg-white/[0.02]">
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">
                                {{ $item->name }}
                            </td>
                            <td class="px-4 py-3 text-center text-zinc-500 dark:text-zinc-400">
                                {{ number_format($sys) }} 
                                <span class="text-[10px]">{{ $item->unit }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <x-stepper wire:model="actualStocks.{{ $item->id }}" min="0" placeholder="0" variant="inside" />
                            </td>
                            <td class="px-4 py-3 text-center font-medium {{ $diff < 0 ? 'text-red-600' : ($diff > 0 ? 'text-emerald-600' : 'text-zinc-500') }}">
                                {{ $diff > 0 ? '+' : '' }}{{ number_format($diff) }}
                            </td>
                            <td class="px-4 py-3">
                                <flux:input wire:model="notes.{{ $item->id }}" placeholder="Catatan selisih..." />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-zinc-500">
                                Tidak ada barang di kategori ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
