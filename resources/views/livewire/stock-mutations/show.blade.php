<div>
    <!-- Warehouse Info Card -->
    <flux:card class="mb-8 p-6 relative overflow-hidden">
        <!-- Background Decoration for premium feel -->
        <div class="absolute top-0 right-0 -mt-10 -mr-10 text-zinc-100 dark:text-zinc-800/50 pointer-events-none transition-all">
            <flux:icon.building-office class="w-64 h-64 opacity-50" />
        </div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-start justify-between gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-4 mb-6">
                    <flux:button href="{{ route('stock-mutations.index') }}" wire:navigate variant="ghost" icon="arrow-left" class="hidden sm:flex" />
                    <div class="flex items-center gap-4">
                        <div class="rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 p-3 shadow-md">
                            <flux:icon.building-office class="w-6 h-6" />
                        </div>
                        <div>
                            <flux:heading size="xl" class="!font-bold">{{ $warehouse->name }}</flux:heading>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Informasi Buku Mutasi & Ledger</flux:text>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-2">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 p-2 text-zinc-500 dark:text-zinc-400">
                            <flux:icon.document-text class="w-4 h-4" />
                        </div>
                        <div>
                            <flux:text class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1">Deskripsi</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $warehouse->description ?: 'Tidak ada deskripsi' }}</flux:text>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col gap-3 sm:min-w-60 mt-4 sm:mt-0">
                <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-white/10 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-300">Macam Barang</flux:text>
                        <div class="rounded-full bg-zinc-200/50 dark:bg-zinc-700 p-1.5">
                            <flux:icon.cube class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <flux:heading size="2xl" class="!font-bold">{{ number_format($warehouse->stocks()->count()) }}</flux:heading>
                    </div>
                </div>
            </div>
        </div>
    </flux:card>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2">
            <flux:heading size="lg">Buku Mutasi (Ledger)</flux:heading>
            <flux:badge size="sm" color="zinc" class="rounded-full">{{ $this->mutations->total() }}</flux:badge>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
            <div class="w-full sm:w-36">
                <x-searchable-select wire:model.live="typeFilter" :options="['in' => 'Masuk (In)', 'out' => 'Keluar (Out)']" searchable="false" placeholder="Semua Tipe" />
            </div>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama barang..."
                icon="magnifying-glass" class="w-full sm:w-64" />
            @canany(['stock_mutations:create', 'stock_mutations:create-self'])
                <flux:button wire:click="openCreateModal" variant="primary" icon="plus" class="ms-auto w-full sm:w-auto">
                    Mutasi Manual
                </flux:button>
            @endcanany
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
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
                                    <flux:text class="mt-2 mb-4 text-sm text-zinc-500 dark:text-zinc-400">Belum ada riwayat stok keluar atau masuk di gudang ini.</flux:text>
                                    <div class="mt-2">
                                        @canany(['stock_mutations:create', 'stock_mutations:create-self'])
                                            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                                                Mutasi Manual</flux:button>
                                        @endcanany
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->mutations->total() > 0)
            <flux:pagination :paginator="$this->mutations" class="p-4" />
        @endif
    </div>

    <!-- Create Modal -->
    <flux:modal :closable="false" scroll="body" name="create-mutation-modal" class="md:max-w-2xl !rounded-3xl">
        <form wire:submit="save">
            <flux:heading>Mutasi Stok Manual</flux:heading>
            <flux:description>Input barang masuk atau keluar secara manual di {{ $warehouse->name }}.</flux:description>

            <!-- iOS Style Connected List -->
            <div class="mt-4 bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">
                <!-- Barang -->
                <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-pointer">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        Barang
                    </span>
                    <div class="flex-1 flex justify-end">
                        <x-searchable-select wire:model="item_id" :options="$this->items->mapWithKeys(fn($item) => [$item->id => $item->code . ' - ' . $item->name])" placeholder="Pilih Barang..." variant="ios" />
                    </div>
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </label>
                <x-error-ios name="item_id" />

                <!-- Tipe Mutasi -->
                <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-pointer">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        Tipe Mutasi
                    </span>
                    <div class="flex-1 flex justify-end">
                        <x-searchable-select wire:model="type" :options="['in' => 'Barang Masuk (In)', 'out' => 'Barang Keluar (Out)']" searchable="false" placeholder="Pilih Tipe..." variant="ios" />
                    </div>
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </label>
                <x-error-ios name="type" />

                <!-- Jumlah -->
                <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        Jumlah (Qty)
                    </span>
                    <div class="flex-1">
                        <x-stepper wire:model="quantity" min="1" placeholder="1" variant="ios" />
                    </div>
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </label>
                <x-error-ios name="quantity" />

                <!-- Catatan -->
                <label class="flex flex-col px-4 py-2.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white select-none mb-1">
                        Catatan
                    </span>
                    <textarea wire:model="notes" rows="2" placeholder="Masukkan catatan opsional..."
                        class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-1 resize-none"></textarea>
                </label>
                <x-error-ios name="notes" />
            </div>

            <div class="mt-6 flex flex-col gap-2">
                <flux:button class="!rounded-full" type="submit" variant="primary">Simpan Mutasi</flux:button>
                <flux:button class="!rounded-full" x-on:click="$flux.modal('create-mutation-modal').close()" variant="outline">Batal</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
