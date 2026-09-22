<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Produksi Harian</flux:heading>
            <flux:subheading>Catatan repacking dan produksi barang harian.</flux:subheading>
        </div>
        <div class="flex flex-col lg:flex-row items-center gap-3 w-full lg:w-auto mt-4 lg:mt-0">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari bahan atau gudang..."
                icon="magnifying-glass" class="w-full lg:w-64" />
            @can('productions:create')
                <flux:button wire:click="openCreateModal" variant="primary" icon="plus" class="ms-auto w-full lg:w-auto">
                    Catat Produksi
                </flux:button>
            @endcan
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'date'" :direction="$sortDirection"
                        wire:click="sort('date')">Tanggal & Gudang</flux:table.column>
                    <flux:table.column>Bahan Baku (OUT)</flux:table.column>
                    <flux:table.column>Barang Jadi (IN)</flux:table.column>
                    <flux:table.column>Diinput Oleh</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($productions as $production)
                        <flux:table.row>
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="font-medium whitespace-nowrap">{{ \Carbon\Carbon::parse($production->date)->format('d M Y') }}</span>
                                    <span class="text-sm text-zinc-500">{{ $production->warehouse->name }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                                    <flux:icon.arrow-down-right class="w-4 h-4 shrink-0" />
                                    <div class="flex flex-col">
                                        <span class="font-medium whitespace-nowrap">{{ number_format($production->raw_quantity) }} pcs</span>
                                        <span class="text-sm truncate max-w-[150px] sm:max-w-none">{{ $production->rawItem->name }}</span>
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col gap-1">
                                    @foreach($production->results as $result)
                                        <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                                            <flux:icon.arrow-up-right class="w-4 h-4 shrink-0" />
                                            <span class="font-medium whitespace-nowrap">{{ number_format($result->quantity) }} pcs</span>
                                            <span class="text-sm truncate max-w-[150px] sm:max-w-none">{{ $result->item->name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:avatar :src="$production->user->avatarUrl()" :name="$production->user->name" :initials="$production->user->initials()" size="xs" />
                                    <span class="text-sm truncate">{{ $production->user->name }}</span>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-3 mb-4">
                                        <flux:icon.wrench-screwdriver class="w-6 h-6 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <flux:heading size="lg">Belum ada data</flux:heading>
                                    <flux:text class="mt-2 mb-4 text-sm text-zinc-500 dark:text-zinc-400">Mulai dengan mencatat produksi harian pertama Anda.</flux:text>
                                    <div class="mt-2">
                                        @can('productions:create')
                                            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                                                Catat Produksi
                                            </flux:button>
                                        @endcan
                                    </div>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($productions->total() > 0)
            <flux:pagination :paginator="$productions" class="p-4" />
        @endif
    </div>

    <!-- Create Modal -->
    <flux:modal :closable="false" scroll="body" name="create-production-modal" class="md:max-w-2xl !rounded-3xl">
        <form wire:submit="save">
            <flux:heading>Catat Produksi Harian</flux:heading>
            <flux:description>Masukkan bahan baku yang digunakan dan hasil jadinya.</flux:description>

            <div class="mt-4">
                <flux:heading size="sm" class="mb-2 px-1 text-zinc-500">INFORMASI DASAR</flux:heading>
                <div class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden mb-6">
                    <!-- Tanggal -->
                    <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">Tanggal</span>
                        <div class="flex-1">
                            <x-date-picker wire:model="date" type="date" variant="ios" placeholder="Pilih tanggal..." />
                        </div>
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    </label>
                    <x-error-ios name="date" />

                    <!-- Gudang -->
                    <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">Gudang</span>
                        <div class="flex-1">
                            <x-searchable-select wire:model="warehouse_id" :options="$this->warehouses" :searchable="true" variant="ios" placeholder="Pilih gudang..." />
                        </div>
                    </label>
                    <x-error-ios name="warehouse_id" />
                </div>

                <flux:heading size="sm" class="mb-2 px-1 text-red-500">BAHAN BAKU (BERKURANG)</flux:heading>
                <div class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden mb-6">
                    <!-- Bahan Mentah -->
                    <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">Bahan Baku</span>
                        <div class="flex-1">
                            <x-searchable-select wire:model="raw_item_id" :options="$this->items->mapWithKeys(fn($i) => [$i->id => ($i->category->name ?? 'Lain-lain') . ' ' . $i->name])->toArray()" :searchable="true" variant="ios" placeholder="Pilih bahan baku..." class="[&>button]:!ms-0 [&>button]:!w-full" />
                        </div>
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    </label>
                    <x-error-ios name="raw_item_id" />

                    <!-- Jumlah -->
                    <div class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07]">
                        <label class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">Jumlah Digunakan</label>
                        <div class="flex-1 flex items-center justify-between" x-data="{
                            increment() { $refs.num.stepUp(); $refs.num.dispatchEvent(new Event('input', { bubbles: true })); },
                            decrement() { $refs.num.stepDown(); $refs.num.dispatchEvent(new Event('input', { bubbles: true })); }
                        }">
                            <input x-ref="num" wire:model="raw_quantity" type="number" min="0" step="0.01" placeholder="0" class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                            <div class="flex items-center gap-1 bg-zinc-100 dark:bg-white/10 rounded-lg p-0.5 ml-2 shrink-0 border border-zinc-200/50 dark:border-white/5 shadow-xs">
                                <button type="button" @click="decrement" class="w-8 h-7 flex items-center justify-center rounded-md hover:bg-white dark:hover:bg-white/15 shadow-[0_1px_2px_rgba(0,0,0,0.05)] transition-all text-zinc-600 dark:text-zinc-300">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>
                                </button>
                                <button type="button" @click="increment" class="w-8 h-7 flex items-center justify-center rounded-md hover:bg-white dark:hover:bg-white/15 shadow-[0_1px_2px_rgba(0,0,0,0.05)] transition-all text-zinc-600 dark:text-zinc-300">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <x-error-ios name="raw_quantity" />
                </div>

                <flux:heading size="sm" class="mb-2 px-1 text-green-500">BARANG JADI (BERTAMBAH)</flux:heading>
                <div class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden mb-6">
                    @foreach($results as $index => $result)
                        <div wire:key="result-{{ $index }}" class="flex flex-col border-b border-zinc-200 dark:border-white/10 last:border-0 relative group">
                            <label class="flex flex-row items-center px-4 py-1.5 relative transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                                <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">Barang Jadi</span>
                                <div class="flex-1">
                                    <x-searchable-select wire:model="results.{{ $index }}.item_id" :options="$this->items->mapWithKeys(fn($i) => [$i->id => ($i->category->name ?? 'Lain-lain') . ' ' . $i->name])->toArray()" :searchable="true" variant="ios" placeholder="Pilih barang jadi..." class="[&>button]:!ms-0 [&>button]:!w-full" />
                                </div>
                                <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                            </label>
                            <x-error-ios name="results.{{ $index }}.item_id" />

                            <div class="flex flex-row items-center px-4 py-1.5 relative transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07]">
                                <label class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">Jumlah Hasil</label>
                                <div class="flex-1 flex items-center justify-between" x-data="{
                                    increment() { $refs.num.stepUp(); $refs.num.dispatchEvent(new Event('input', { bubbles: true })); },
                                    decrement() { $refs.num.stepDown(); $refs.num.dispatchEvent(new Event('input', { bubbles: true })); }
                                }">
                                    <input x-ref="num" wire:model="results.{{ $index }}.quantity" type="number" min="0" step="0.01" placeholder="0" class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                                    <div class="flex items-center gap-1 bg-zinc-100 dark:bg-white/10 rounded-lg p-0.5 ml-2 shrink-0 border border-zinc-200/50 dark:border-white/5 shadow-xs">
                                        <button type="button" @click="decrement" class="w-8 h-7 flex items-center justify-center rounded-md hover:bg-white dark:hover:bg-white/15 shadow-[0_1px_2px_rgba(0,0,0,0.05)] transition-all text-zinc-600 dark:text-zinc-300">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>
                                        </button>
                                        <button type="button" @click="increment" class="w-8 h-7 flex items-center justify-center rounded-md hover:bg-white dark:hover:bg-white/15 shadow-[0_1px_2px_rgba(0,0,0,0.05)] transition-all text-zinc-600 dark:text-zinc-300">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                        </button>
                                    </div>
                                </div>
                                
                                @if(count($results) > 1)
                                    <div class="ml-3 shrink-0">
                                        <flux:button variant="danger" size="sm" icon="trash" wire:click="removeResult({{ $index }})" />
                                    </div>
                                @endif
                            </div>
                            <x-error-ios name="results.{{ $index }}.quantity" />
                        </div>
                    @endforeach

                    <div class="relative">
                        <div class="absolute top-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        <button type="button" wire:click="addResult" class="w-full py-3 flex items-center justify-center gap-2 text-[14px] font-medium text-accent hover:bg-zinc-50 dark:hover:bg-white/[0.02] transition-colors cursor-text">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            Tambah Barang Jadi
                        </button>
                    </div>
                </div>

                <flux:heading size="sm" class="mb-2 px-1 text-zinc-500">CATATAN (OPSIONAL)</flux:heading>
                <div class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs relative overflow-hidden mb-6">
                    <textarea wire:model="notes" rows="3" class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 p-4 resize-none" placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>

            <div class="mt-2 flex flex-col gap-2">
                <flux:button class="!rounded-full" type="submit" variant="primary">Simpan Produksi</flux:button>
                <flux:button class="!rounded-full" x-on:click="$flux.modal('create-production-modal').close()" variant="outline">Batal</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
