<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Kategori Barang</flux:heading>
            <flux:subheading>Kelola daftar kategori barang di sini.</flux:subheading>
        </div>
        @can('item_categories:create')
            <flux:button wire:click="openCreateModal" variant="primary" icon="plus" class="w-full sm:w-auto">Tambah Kategori
            </flux:button>
        @endcan
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table
                class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                        wire:click="sort('name')">Nama</flux:table.column>
                    @canany(['item_categories:update', 'item_categories:delete'])
                        <flux:table.column>Aksi</flux:table.column>
                    @endcanany
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->categories as $category)
                        <flux:table.row :key="$category->id">
                            <flux:table.cell>{{ $category->name }}</flux:table.cell>

                            @canany(['item_categories:update', 'item_categories:delete'])
                                <flux:table.cell>
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            <flux:menu.item wire:click="openEditModal({{ $category->id }})"
                                                icon="pencil-square">Edit</flux:menu.item>
                                            @can('item_categories:delete')
                                                <x-delete-modal id="delete-item-cat-{{ $category->id }}"
                                                    action="delete({{ $category->id }})" requireSlide="true"
                                                    title="Hapus kategori?"
                                                    description="You are about to delete this item category. This action cannot be reversed." />
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
                                        @can('item_categories:create')
                                            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                                                Tambah Kategori</flux:button>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->categories->total() > 0)
            <flux:pagination :paginator="$this->categories" class="p-4" />
        @endif
    </div>

    <flux:modal :closable="false" scroll="body" name="create-item-category-modal" class="md:max-w-2xl !rounded-3xl">
        <form wire:submit="save">
            <flux:heading>{{ $categoryId ? 'Edit' : 'Create' }} Category</flux:heading>
            <flux:description>
                {{ $categoryId ? 'Anda akan mengedit data kategori barang' : 'Anda akan membuat kategori barang baru' }}
            </flux:description>

            <!-- iOS Style Connected List -->
            <div
                class="mt-4 bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">
                <!-- Nama Kategori -->
                <label
                    class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        Nama Kategori
                    </span>
                    <div class="flex-1">
                        <input wire:model="name" placeholder="e.g. Elektronik"
                            class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                    </div>
                </label>
                <x-error-ios name="name" />
            </div>

            <div class="mt-6 flex flex-col gap-2">
                <flux:button class="!rounded-full" type="submit" variant="primary">Simpan</flux:button>
                <flux:button class="!rounded-full" x-on:click="$flux.modal('create-item-category-modal').close()" variant="outline">Batal
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
