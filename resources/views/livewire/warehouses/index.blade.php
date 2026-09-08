<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Gudang</flux:heading>
            <flux:subheading>Kelola daftar gudang di sini.</flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama, deskripsi..."
                icon="magnifying-glass" class="w-full sm:w-64" />
            @can('warehouses:create')
                <flux:button wire:click="openCreateModal" variant="primary" icon="plus" class="ms-auto w-full sm:w-auto">
                    Tambah Gudang
                </flux:button>
            @endcan
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table
                class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                        wire:click="sort('name')">Nama</flux:table.column>
                    <flux:table.column>Deskripsi</flux:table.column>
                    @canany(['warehouses:update', 'warehouses:delete'])
                        <flux:table.column>Aksi</flux:table.column>
                    @endcanany
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->warehouses as $warehouse)
                        <flux:table.row :key="$warehouse->id">
                            <flux:table.cell>{{ $warehouse->name }}</flux:table.cell>
                            <flux:table.cell>{{ $warehouse->description }}</flux:table.cell>

                            @canany(['warehouses:update', 'warehouses:delete'])
                                <flux:table.cell>
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            @can('warehouses:update')
                                                <flux:menu.item wire:click="openEditModal({{ $warehouse->id }})"
                                                    icon="pencil-square">Edit</flux:menu.item>
                                            @endcan
                                            @can('warehouses:delete')
                                                <x-delete-modal id="delete-warehouse-{{ $warehouse->id }}"
                                                    action="delete({{ $warehouse->id }})" requireSlide="true"
                                                    title="Hapus Gudang?"
                                                    description="Anda akan menghapus gudang ini. Semua data stok mungkin akan terdampak. Tindakan ini tidak dapat dibatalkan." />
                                            @endcan
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            @endcanany
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
                                        @can('warehouses:create')
                                            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                                                Tambah Gudang</flux:button>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->warehouses->total() > 0)
            <flux:pagination :paginator="$this->warehouses" class="p-4" />
        @endif
    </div>

    <!-- Create/Edit Modal -->
    <flux:modal :closable="false" scroll="body" name="create-warehouse-modal" class="md:max-w-2xl !rounded-3xl">
        <form wire:submit="save">
            <flux:heading>{{ $warehouseId ? 'Edit' : 'Create' }} Gudang</flux:heading>
            <flux:description>{{ $warehouseId ? 'Anda akan mengedit data gudang' : 'Anda akan menambahkan gudang baru' }}
            </flux:description>

            <!-- iOS Style Connected List -->
            <div
                class="mt-4 bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">

                <!-- Nama -->
                <label
                    class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        Nama Gudang
                    </span>
                    <div class="flex-1">
                        <input wire:model="name" placeholder="e.g. Gudang Utama"
                            class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                    </div>
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </label>
                <x-error-ios name="name" />

                <!-- Deskripsi -->
                <label
                    class="flex flex-col px-4 py-2.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white select-none mb-1">
                        Deskripsi
                    </span>
                    <textarea wire:model="description" rows="3" placeholder="Masukkan deskripsi (opsional)"
                        class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-1 resize-none"></textarea>
                </label>
                <x-error-ios name="description" />
            </div>

            <div class="mt-6 flex flex-col gap-2">
                <flux:button class="!rounded-full" type="submit" variant="primary">Simpan</flux:button>
                <flux:button class="!rounded-full" x-on:click="$flux.modal('create-warehouse-modal').close()" variant="outline">Batal
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
