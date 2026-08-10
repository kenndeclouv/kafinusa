<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Pelanggan</flux:heading>
            <flux:subheading>Kelola daftar pelanggan di sini.</flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari nama, pasar..." icon="magnifying-glass"
                class="w-full sm:w-64" />
            @can('customers:create')
                <flux:button wire:click="addCustomer" variant="primary" icon="plus" class="ms-auto w-full sm:w-auto">Tambah
                    Pelanggan
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
                    <flux:table.column>Kategori</flux:table.column>
                    <flux:table.column>Pasar</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    @canany(['customers:update', 'customers:delete'])
                        <flux:table.column>Aksi</flux:table.column>
                    @endcanany
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->customers as $customer)
                        <flux:table.row :key="$customer->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$customer->name" />
                                    <span>{{ $customer->name }}</span>
                                    @if ($customer->user_id)
                                        <flux:icon name="user" class="w-4 h-4 text-zinc-400" />
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="zinc" size="sm">{{ $customer->category?->name ?? '-' }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $customer->market?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($customer->status)
                                    <flux:badge color="green" size="sm">Aktif</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Tidak Aktif</flux:badge>
                                @endif
                            </flux:table.cell>

                            @canany(['customers:update', 'customers:delete'])
                                <flux:table.cell>
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            @can('customers:update')
                                                <flux:menu.item wire:click="editCustomer({{ $customer->id }})"
                                                    icon="pencil-square">Edit</flux:menu.item>
                                            @endcan
                                            @can('customers:delete')
                                                <x-delete-modal id="delete-customer-{{ $customer->id }}"
                                                    action="delete({{ $customer->id }})" requireSlide="true"
                                                    title="Hapus pelanggan?"
                                                    description="Anda akan menghapus pelanggan ini. Tindakan ini tidak dapat dibatalkan." />
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
                                        @can('customers:create')
                                            <flux:button wire:click="addCustomer" variant="primary" icon="plus">Tambah
                                                Pelanggan</flux:button>
                                        @endcan
                                    </div>
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

    <!-- Create/Edit Modal -->
    <flux:modal :closable="false" scroll="body" name="create-customer-modal" class="md:max-w-2xl !rounded-3xl">
        <form wire:submit="save">
            <flux:heading>{{ $editingCustomerId ? 'Edit Pelanggan' : 'Tambah Pelanggan' }}</flux:heading>
            <flux:description>
                {{ $editingCustomerId ? 'Perbarui data pelanggan di bawah ini.' : 'Tambahkan pelanggan baru ke dalam database.' }}
            </flux:description>

            <div class="mt-6 space-y-6">
                <!-- Data Pelanggan -->
                <div>
                    <div
                        class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">

                        <!-- Kode -->
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Kode Pelanggan
                            </span>
                            <div class="flex-1">
                                <input type="text" wire:model="code" placeholder="Misal: CUST-001"
                                    class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="code" />

                        <!-- Nama -->
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Nama Lengkap
                            </span>
                            <div class="flex-1">
                                <input type="text" wire:model="name" placeholder="Misal: Budi Santoso"
                                    class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="name" />

                        <!-- No HP -->
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Nomor HP
                            </span>
                            <div class="flex-1">
                                <input type="text" wire:model="phone" placeholder="Misal: 08123456789"
                                    class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="phone" />

                        <!-- Status -->
                        <label
                            class="flex flex-row items-center justify-between px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-pointer">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white shrink-0 py-2 select-none">
                                Status Pelanggan Aktif
                            </span>
                            <div class="flex items-center">
                                <x-switch wire:model="status" />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="status" />

                        <!-- Kategori -->
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Kategori
                            </span>
                            <div class="flex-1">
                                <x-searchable-select wire:model="customer_category_id" :options="$this->categories" variant="ios"
                                    placeholder="Pilih kategori..." />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                        <x-error-ios name="customer_category_id" />

                        <!-- Pasar -->
                        <div
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07]">
                            <label
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Pasar
                            </label>
                            <div class="flex-1">
                                <x-searchable-select wire:model="market_id" :options="$this->markets" variant="ios"
                                    placeholder="Pilih pasar..." />
                            </div>
                        </div>
                        <x-error-ios name="market_id" />
                    </div>


                </div>
            </div>

            <div class="mt-8 flex flex-col gap-2">
                <flux:button type="submit" variant="primary" class="!rounded-full">Simpan Pelanggan</flux:button>
                <flux:button x-on:click="$flux.modal('create-customer-modal').close()" variant="outline" class="!rounded-full">
                    Batal
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
