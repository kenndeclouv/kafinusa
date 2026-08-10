<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Barang</flux:heading>
            <flux:subheading>Manage your inventory items here.</flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari kode, nama, kategori..."
                icon="magnifying-glass" class="w-full sm:w-64" />
            @can('items:create')
                <flux:button wire:click="openCreateModal" variant="primary" icon="plus" class="ms-auto w-full sm:w-auto">
                    Tambah Barang
                </flux:button>
            @endcan
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table
                class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'code'" :direction="$sortDirection"
                        wire:click="sort('code')">Kode</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                        wire:click="sort('name')">Nama</flux:table.column>
                    <flux:table.column>Kategori</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'weight'" :direction="$sortDirection"
                        wire:click="sort('weight')">Berat</flux:table.column>
                    @canany(['items:update', 'items:delete'])
                        <flux:table.column>Aksi</flux:table.column>
                    @endcanany
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->items as $item)
                        <flux:table.row :key="$item->id">
                            <flux:table.cell>{{ $item->code }}</flux:table.cell>
                            <flux:table.cell>{{ $item->name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="zinc" size="sm">{{ $item->category?->name ?? '-' }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ formatWeight($item->weight) }}</flux:table.cell>

                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item wire:click="openShowModal({{ $item->id }})" icon="eye">
                                            Lihat Detail</flux:menu.item>
                                        @can('items:update')
                                            <flux:menu.item wire:click="openEditModal({{ $item->id }})"
                                                icon="pencil-square">Edit</flux:menu.item>
                                        @endcan
                                        @can('items:delete')
                                            <x-delete-modal id="delete-item-{{ $item->id }}"
                                                action="delete({{ $item->id }})" requireSlide="true"
                                                title="Hapus barang?"
                                                description="Anda akan menghapus barang ini. Tindakan ini tidak dapat dibatalkan." />
                                        @endcan
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <tr>
                            <td colspan="100%" class="py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-3 mb-4">
                                        <flux:icon.folder-open class="w-6 h-6 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <flux:heading size="lg">Belum ada data</flux:heading>
                                    <flux:text class="mt-2 mb-4 text-sm text-zinc-500 dark:text-zinc-400">Mulai dengan
                                        menambahkan data baru ke dalam sistem.</flux:text>
                                    <div class="mt-2">
                                        @can('items:create')
                                            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                                                Tambah Barang</flux:button>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->items->total() > 0)
            <flux:pagination :paginator="$this->items" class="p-4" />
        @endif
    </div>

    <flux:modal :closable="false" scroll="body" name="create-item-modal" class="md:max-w-2xl !rounded-3xl">
        <form wire:submit="save">
            <flux:heading>{{ $itemId ? 'Edit' : 'Create' }} Barang</flux:heading>
            <flux:description>{{ $itemId ? 'Anda akan mengedit data barang' : 'Anda akan menambahkan barang baru' }}
            </flux:description>

            <div
                class="mt-4 bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">

                <!-- Kategori -->
                <label
                    class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        Kategori
                    </span>
                    <div class="flex-1">
                        <x-searchable-select wire:model="item_category_id" :options="$this->categories" :searchable="true"
                            variant="ios" placeholder="Pilih kategori..." />
                    </div>
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </label>
                <x-error-ios name="item_category_id" />

                <!-- Kode -->
                <label
                    class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        Kode
                    </span>
                    <div class="flex-1">
                        <input wire:model="code" placeholder="e.g. BRS-01"
                            class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                    </div>
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </label>
                <x-error-ios name="code" />

                <!-- Nama -->
                <div
                    class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07]">
                    <label
                        class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        Nama
                    </label>
                    <div class="flex-1">
                        <input wire:model="name" placeholder="e.g. Beras Premium"
                            class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                    </div>
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </div>
                <x-error-ios name="name" />

                <!-- Berat -->
                <div
                    class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07]">
                    <label
                        class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        Berat (g)
                    </label>
                    <div class="flex-1 flex items-center justify-between" x-data="{
                        increment() {
                                $refs.num.stepUp();
                                $refs.num.dispatchEvent(new Event('input', { bubbles: true }));
                            },
                            decrement() {
                                $refs.num.stepDown();
                                $refs.num.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                    }">
                        <input x-ref="num" wire:model="weight" type="number" min="0" step="1"
                            placeholder="e.g. 1000"
                            class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />

                        <!-- Plus Minus iOS pill -->
                        <div
                            class="flex items-center gap-1 bg-zinc-100 dark:bg-white/10 rounded-lg p-0.5 ml-2 shrink-0 border border-zinc-200/50 dark:border-white/5 shadow-xs">
                            <button type="button" @click="decrement"
                                class="w-8 h-7 flex items-center justify-center rounded-md hover:bg-white dark:hover:bg-white/15 shadow-[0_1px_2px_rgba(0,0,0,0.05)] transition-all text-zinc-600 dark:text-zinc-300">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                                </svg>
                            </button>
                            <button type="button" @click="increment"
                                class="w-8 h-7 flex items-center justify-center rounded-md hover:bg-white dark:hover:bg-white/15 shadow-[0_1px_2px_rgba(0,0,0,0.05)] transition-all text-zinc-600 dark:text-zinc-300">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </div>
                <x-error-ios name="weight" />

                <!-- Foto Produk -->
                <div
                    class="px-4 py-4 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07]">
                    <div class="w-full" x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
                        x-on:livewire-upload-finish="isUploading = false"
                        x-on:livewire-upload-error="isUploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress">

                        <label for="photo-upload"
                            class="flex flex-col items-center justify-center w-full min-h-36 border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-2xl cursor-pointer bg-zinc-50 dark:bg-white/5 hover:bg-zinc-100 dark:hover:bg-white/10 transition-colors relative overflow-hidden group/upload">

                            @if ($photo)
                                <!-- Preview new uploaded image -->
                                <img src="{{ $photo->temporaryUrl() }}"
                                    class="absolute inset-0 w-full h-full object-cover opacity-60" />
                                <div
                                    class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center z-10 opacity-0 group-hover/upload:opacity-100 transition-opacity">
                                    <flux:icon.arrow-path class="w-6 h-6 text-white mb-1" />
                                    <span class="text-sm font-medium text-white">Ganti Foto Produk</span>
                                </div>
                            @elseif($existingPhoto)
                                <!-- Preview existing image -->
                                <img src="{{ Storage::url($existingPhoto) }}"
                                    class="absolute inset-0 w-full h-full object-cover opacity-60" />
                                <div
                                    class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center z-10 opacity-0 group-hover/upload:opacity-100 transition-opacity">
                                    <flux:icon.arrow-path class="w-6 h-6 text-white mb-1" />
                                    <span class="text-sm font-medium text-white">Ganti Foto Produk</span>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center pt-6 pb-6 text-center">
                                    <flux:icon.photo class="w-8 h-8 text-zinc-400 mb-2" />
                                    <h3 class="text-[15px] font-medium text-zinc-900 dark:text-white mb-1">Unggah Foto
                                        Produk</h3>
                                    <p class="text-[13px] text-zinc-500 dark:text-zinc-400"><span
                                            class="font-medium text-accent">Klik untuk menelusuri</span> atau seret
                                        file kesini</p>
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">PNG, JPG (Maks. 2MB)
                                        &bull; Opsional</p>
                                </div>
                            @endif

                            <input id="photo-upload" wire:model="photo" type="file" accept="image/*"
                                class="hidden" />

                            <!-- Progress Bar -->
                            <div x-show="isUploading"
                                class="absolute bottom-0 left-0 w-full h-1 bg-zinc-200 dark:bg-zinc-700 z-20">
                                <div class="h-full bg-accent transition-all duration-300"
                                    :style="`width: ${progress}%`"></div>
                            </div>

                            <!-- Uploading Overlay -->
                            <div x-show="isUploading"
                                class="absolute inset-0 bg-white/50 dark:bg-black/50 backdrop-blur-sm flex flex-col items-center justify-center z-20">
                                <flux:icon.arrow-path class="w-6 h-6 text-accent animate-spin mb-2" />
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-200">Mengunggah...</span>
                            </div>
                        </label>
                    </div>
                </div>
                <x-error-ios name="photo" />

            </div>
            <!-- Harga -->
            <div>
                <flux:heading size="sm" class="mb-3 px-1 mt-4">Harga Jual</flux:heading>
                <div
                    class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">

                    @forelse($prices as $index => $price)
                        <div
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07]">
                            <div class="w-1/3 shrink-0 py-2 pe-2">
                                <input wire:model="prices.{{ $index }}.name" type="text"
                                    placeholder="Nama (e.g. Normal)"
                                    class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] font-medium text-zinc-900 dark:text-white placeholder-zinc-400 px-0 py-0" />
                            </div>
                            <div
                                class="flex-1 flex items-center gap-2 border-l border-zinc-200 dark:border-white/10 pl-3">
                                <span class="text-zinc-500 font-medium text-[15px]">Rp</span>
                                <div x-data="{
                                    val: @entangle('prices.' . $index . '.value'),
                                    get formatted() {
                                        if (!this.val) return '';
                                        return this.val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                    },
                                    set formatted(value) {
                                        let raw = value.toString().replace(/\D/g, '');
                                        this.val = raw ? parseInt(raw, 10) : '';
                                    }
                                }" class="w-full">
                                    <input x-model="formatted" type="text" placeholder="0"
                                        class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                                </div>
                            </div>
                            <div class="ml-3">
                                <flux:button variant="danger" size="sm" icon="trash"
                                    wire:click="removePrice({{ $index }})" />
                            </div>

                            @if (!$loop->last)
                                <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                            @endif
                        </div>
                    @empty
                        <div class="p-4 text-center text-[14px] text-zinc-500 dark:text-zinc-400">
                            Belum ada variasi harga yang ditambahkan.
                        </div>
                    @endforelse

                    <div class="relative">
                        <div class="absolute top-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        <button type="button" wire:click="addPrice"
                            class="w-full py-3 flex items-center justify-center gap-2 text-[14px] font-medium text-accent hover:bg-zinc-50 dark:hover:bg-white/[0.02] transition-colors cursor-text">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Harga Baru
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-2">
                <flux:button class="!rounded-full" type="submit" variant="primary">Simpan</flux:button>
                <flux:button class="!rounded-full" x-on:click="$flux.modal('create-item-modal').close()" variant="outline">Batal
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Show Detail Modal -->
    <flux:modal :closable="false" scroll="body" name="detail-item-modal"
        class="md:w-[500px] !rounded-3xl">
        @if ($showItem)
            <div class="flex flex-col gap-6">
                <div>
                    <flux:heading size="lg">Detail Barang</flux:heading>
                    <flux:subheading>Informasi lengkap mengenai barang dan harga jual.</flux:subheading>
                </div>

                <div class="flex flex-col gap-5">
                    @if ($showItem->photo)
                        <div
                            class="w-full h-48 rounded-2xl overflow-hidden border border-zinc-200 dark:border-white/10 relative">
                            <img src="{{ Storage::url($showItem->photo) }}" alt="{{ $showItem->name }}"
                                class="absolute inset-0 w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="mt-1">
                        <div class="text-sm font-medium text-zinc-900 dark:text-white mb-2">Informasi Umum</div>
                        <div
                            class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">
                            <!-- Kode Barang -->
                            <div class="flex flex-row items-center px-4 py-2 relative">
                                <div class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0">Kode
                                    Barang</div>
                                <div
                                    class="flex-1 border-l border-zinc-200 dark:border-white/10 pl-4 py-1 text-[15px] text-zinc-700 dark:text-zinc-300">
                                    {{ $showItem->code }}
                                </div>
                                <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                            </div>

                            <!-- Kategori -->
                            <div class="flex flex-row items-center px-4 py-2 relative">
                                <div class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0">
                                    Kategori</div>
                                <div
                                    class="flex-1 border-l border-zinc-200 dark:border-white/10 pl-4 py-1 text-[15px] text-zinc-700 dark:text-zinc-300">
                                    {{ $showItem->category?->name ?? '-' }}
                                </div>
                                <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                            </div>

                            <!-- Nama Barang -->
                            <div class="flex flex-row items-center px-4 py-2 relative">
                                <div class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0">Nama
                                    Barang</div>
                                <div
                                    class="flex-1 border-l border-zinc-200 dark:border-white/10 pl-4 py-1 text-[15px] text-zinc-700 dark:text-zinc-300">
                                    {{ $showItem->name }}
                                </div>
                                <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                            </div>

                            <!-- Berat -->
                            <div class="flex flex-row items-center px-4 py-2 relative">
                                <div class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0">Berat
                                </div>
                                <div
                                    class="flex-1 border-l border-zinc-200 dark:border-white/10 pl-4 py-1 text-[15px] text-zinc-700 dark:text-zinc-300">
                                    {{ formatWeight($showItem->weight) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-1">
                        <div class="text-sm font-medium text-zinc-900 dark:text-white mb-2">Variasi Harga Jual</div>
                        <div
                            class="bg-white dark:bg-white/5 rounded-2xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col overflow-hidden">
                            @if (is_array($showItem->prices) && count($showItem->prices) > 0)
                                @foreach ($showItem->prices as $name => $value)
                                    <div
                                        class="flex flex-row items-center justify-between px-4 py-2.5 {{ !$loop->last ? 'border-b border-zinc-200 dark:border-white/10' : '' }}">
                                        <div
                                            class="text-[14px] font-medium text-zinc-700 dark:text-zinc-300 capitalize ">
                                            {{ str_replace('_', ' ', $name) }}</div>
                                        <div class="text-[14px] text-zinc-900 dark:text-white ">Rp
                                            {{ number_format($value, 0, ',', '.') }}</div>
                                    </div>
                                @endforeach
                            @else
                                <div class="px-4 py-3 text-[14px] text-zinc-500 text-center">Belum ada harga</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-2 flex">
                    <flux:button class="w-full !rounded-full" x-on:click="$flux.modal('detail-item-modal').close()"
                        variant="outline">Tutup</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
