<div>
    <!-- Warehouse Info Card -->
    <flux:card class="mb-8 p-6 relative overflow-hidden">
        <!-- Background Decoration for premium feel -->
        <div
            class="absolute top-0 right-0 -mt-10 -mr-10 text-zinc-100 dark:text-zinc-800/50 pointer-events-none transition-all">
            <flux:icon.building-office class="w-64 h-64 opacity-50" />
        </div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-start justify-between gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-4 mb-6">
                    <flux:button href="{{ route('stock-mutations.index') }}" wire:navigate variant="ghost"
                        icon="arrow-left" class="hidden sm:flex" />
                    <div class="flex items-center gap-4">
                        <div class="rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 p-3 shadow-md">
                            <flux:icon.building-office class="w-6 h-6" />
                        </div>
                        <div>
                            <flux:heading size="xl" class="!font-bold">{{ $warehouse->name }}</flux:heading>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">Informasi Stok Gudang &
                                Ledger</flux:text>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-2">
                    <div class="flex items-start gap-3">
                        <div
                            class="mt-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 p-2 text-zinc-500 dark:text-zinc-400">
                            <flux:icon.document-text class="w-4 h-4" />
                        </div>
                        <div>
                            <flux:text
                                class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-1">
                                Deskripsi</flux:text>
                            <flux:text class="font-medium text-zinc-900 dark:text-white">
                                {{ $warehouse->description ?: 'Tidak ada deskripsi' }}</flux:text>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:min-w-60 mt-4 sm:mt-0">
                <div
                    class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-white/10 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-300">Macam Barang</flux:text>
                        <div class="rounded-full bg-zinc-200/50 dark:bg-zinc-700 p-1.5">
                            <flux:icon.cube class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <flux:heading size="2xl" class="!font-bold">
                            {{ number_format($warehouse->stocks()->count()) }}</flux:heading>
                    </div>
                </div>
            </div>
        </div>
    </flux:card>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2">
            <flux:heading size="lg">Daftar Stok Barang</flux:heading>
            <flux:badge size="sm" color="zinc" class="rounded-full">{{ $this->stocks->total() }}</flux:badge>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama barang..."
                icon="magnifying-glass" class="w-full sm:w-64" />

            <flux:button href="{{ route('stock-mutations.ledger', $warehouse->id) }}" wire:navigate variant="outline"
                icon="document-text" class="w-full sm:w-auto">
                Buku Mutasi
            </flux:button>

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
                    {{-- <flux:table.column>Kode Barang</flux:table.column> --}}
                    <flux:table.column>Daftar Barang (Item)</flux:table.column>
                    <flux:table.column>Stok Tertulis (Sistem)</flux:table.column>
                    <flux:table.column>Stok Opname (Fisik)</flux:table.column>
                    <flux:table.column>Selisih</flux:table.column>
                    <flux:table.column>Total Tonase (Opname)</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->stocks as $stock)
                        <flux:table.row :key="$stock->id">
                            {{-- <flux:table.cell>
                                <flux:badge color="zinc" size="sm">{{ $stock->item?->code }}</flux:badge>
                            </flux:table.cell> --}}
                            <flux:table.cell>
                                <span
                                    class="font-medium text-zinc-900 dark:text-white">{{ $stock->item?->category?->name }}
                                    {{ $stock->item?->name ?? 'Barang Terhapus' }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span
                                    class="font-bold {{ $stock->current_stock > 0 ? 'text-zinc-900 dark:text-white' : 'text-red-600' }}">
                                    {{ number_format($stock->current_stock) }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span
                                    class="font-bold {{ $stock->physical_stock > 0 ? 'text-zinc-900 dark:text-white' : 'text-red-600' }}">
                                    {{ number_format($stock->physical_stock) }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $selisih = $stock->current_stock - $stock->physical_stock;
                                @endphp
                                <span class="font-bold {{ $selisih == 0 ? 'text-zinc-500' : 'text-amber-600' }}">
                                    {{ number_format($selisih) }}
                                </span>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ number_format($stock->physical_stock * ($stock->item?->weight ?? 0), 2) }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <tr>
                            <td colspan="100%" class="py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-3 mb-4">
                                        <flux:icon.cube class="w-6 h-6 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <flux:heading size="lg">Stok Kosong</flux:heading>
                                    <flux:text class="mt-2 mb-4 text-sm text-zinc-500 dark:text-zinc-400">Belum ada
                                        barang yang memiliki stok di gudang ini.</flux:text>
                                    <div class="mt-2">
                                        @canany(['stock_mutations:create', 'stock_mutations:create-self'])
                                            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                                                Input Stok Awal (Mutasi)</flux:button>
                                        @endcanany
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->stocks->total() > 0)
            <flux:pagination :paginator="$this->stocks" class="p-4 border-t border-zinc-200 dark:border-white/5" />
        @endif
    </div>

    <!-- Create Modal -->
    <flux:modal :closable="false" scroll="body" name="create-mutation-modal" class="md:max-w-2xl !rounded-3xl">
        <form wire:submit="save">
            @if ($step === 1)
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <flux:heading>Pilih Jenis Mutasi</flux:heading>
                        <flux:description>Apakah ini barang masuk atau barang keluar?</flux:description>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    <button type="button" wire:click="$set('type', 'in'); $set('step', 2)"
                        class="flex flex-col items-center justify-center p-8 rounded-3xl border-2 border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/5 hover:border-emerald-500 hover:bg-zinc-100 dark:hover:border-emerald-500/50 dark:hover:bg-white/10 transition-all group">
                        <div class="rounded-full bg-emerald-100 dark:bg-emerald-500/20 p-4 mb-4 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                            <flux:icon.arrow-down-tray class="w-8 h-8" />
                        </div>
                        <flux:heading size="lg" class="!font-bold">Barang Masuk</flux:heading>
                        <flux:text class="text-sm text-zinc-500 mt-1 text-center">Pembelian, Stok Awal, dll.</flux:text>
                    </button>

                    <button type="button" wire:click="$set('type', 'out'); $set('step', 2)"
                        class="flex flex-col items-center justify-center p-8 rounded-3xl border-2 border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/5 hover:border-rose-500 hover:bg-zinc-100 dark:hover:border-rose-500/50 dark:hover:bg-white/10 transition-all group">
                        <div class="rounded-full bg-rose-100 dark:bg-rose-500/20 p-4 mb-4 text-rose-600 dark:text-rose-400 group-hover:scale-110 transition-transform">
                            <flux:icon.arrow-up-tray class="w-8 h-8" />
                        </div>
                        <flux:heading size="lg" class="!font-bold">Barang Keluar</flux:heading>
                        <flux:text class="text-sm text-zinc-500 mt-1 text-center">Penjualan, Produksi, dll.</flux:text>
                    </button>
                </div>

                <div class="mt-8 flex justify-center">
                    <flux:button variant="ghost" size="sm" wire:click="$set('type', 'adjustment'); $set('step', 2)" class="text-zinc-500">
                        Atau sesuaikan stok opname (Adjustment)
                    </flux:button>
                </div>

                <div class="mt-6 flex flex-col gap-2">
                    <flux:button class="!rounded-full" x-on:click="$flux.modal('create-mutation-modal').close()" variant="outline">Batal</flux:button>
                </div>
            @endif

            @if ($step === 2)
                <div class="flex items-center gap-3 mb-2">
                    <button type="button" wire:click="$set('step', 1)" class="p-1.5 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors text-zinc-500">
                        <flux:icon.arrow-left class="w-5 h-5" />
                    </button>
                    <div>
                        <flux:heading>
                            {{ $type === 'in' ? 'Detail Barang Masuk' : ($type === 'out' ? 'Detail Barang Keluar' : 'Penyesuaian Stok (Opname)') }}
                        </flux:heading>
                        <flux:description>Lengkapi informasi mutasi stok di bawah ini.</flux:description>
                    </div>
                </div>

                <!-- iOS Style Connected List -->
                <div class="mt-6 bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">
                    <!-- Tanggal -->
                    <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-pointer">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Tanggal
                        </span>
                        <div class="flex-1 w-full flex justify-end">
                            <x-date-picker wire:model="mutation_date" variant="ios" />
                        </div>
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    </label>
                    <x-error-ios name="mutation_date" />

                    <!-- Barang -->
                    <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-pointer">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Barang
                        </span>
                        <div class="flex-1 w-full">
                            <x-searchable-select wire:model="item_id" :options="$this->items
                                ->mapWithKeys(fn($i) => [$i->id => ($i->category->name ?? 'Lain-lain') . ' ' . $i->name])
                                ->toArray()" placeholder="Pilih Barang..."
                                variant="ios" class="[&>button]:!ms-0 [&>button]:!w-full" />
                        </div>
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    </label>
                    <x-error-ios name="item_id" />

                    @if ($type === 'in' || $type === 'out')
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
                    @endif

                    @if ($type === 'in')
                        <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Pengirim / Vendor
                            </span>
                            <div class="flex-1 w-full">
                                <input type="text" wire:model="sender_name" placeholder="Opsional" class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-1" />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="sender_name" />
                    @endif

                    @if ($type === 'out')
                        <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-pointer">
                            <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Jenis Keluar
                            </span>
                            <div class="flex-1 flex justify-end w-full">
                                <x-searchable-select wire:model="transaction_category" :options="[
                                    'Penjualan' => 'Penjualan',
                                    'Produksi' => 'Produksi',
                                    'Lainnya' => 'Lainnya',
                                ]" searchable="false"
                                    placeholder="Pilih Jenis..." variant="ios" class="[&>button]:!ms-0 [&>button]:!w-full" />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="transaction_category" />
                    @endif

                    @if ($type === 'adjustment')


                        <!-- Set Stok Opname -->
                        <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Opname (Fisik)
                            </span>
                            <div class="flex-1">
                                <x-stepper wire:model="physical_quantity" min="0" placeholder="Kosongkan jika tak diubah" variant="ios" />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="physical_quantity" />
                    @endif

                    <!-- Catatan -->
                    <label class="flex flex-col px-4 py-2.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white select-none mb-1">
                            Catatan
                        </span>
                        <textarea wire:model="notes" rows="2" placeholder="Masukkan catatan opsional..." class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-1 resize-none"></textarea>
                    </label>
                    <x-error-ios name="notes" />
                </div>

                <div class="mt-6 flex flex-col gap-2">
                    <flux:button class="!rounded-full" type="submit" variant="primary">Simpan Mutasi</flux:button>
                    <flux:button class="!rounded-full" type="button" wire:click="$set('step', 1)" variant="outline">Kembali</flux:button>
                </div>
            @endif
        </form>
    </flux:modal>
</div>
