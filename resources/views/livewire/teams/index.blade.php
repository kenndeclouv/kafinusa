<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Master Tim Produksi</flux:heading>
            <flux:subheading>Kelola nama-nama tim dan penugasan gudang mereka.</flux:subheading>
        </div>
        <div class="flex flex-col lg:flex-row items-center gap-3 w-full lg:w-auto mt-4 lg:mt-0">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari tim..." class="w-full lg:w-64" />
            
            <x-searchable-select wire:model.live="filter_warehouse_id" :options="$this->warehouses" variant="ios" placeholder="Semua Gudang" class="w-full lg:w-48" />
            
            <flux:button wire:click="openCreateModal" variant="primary" icon="plus" class="ms-auto w-full lg:w-auto">
                Tambah Tim
            </flux:button>
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">Nama Tim</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'warehouse_id'" :direction="$sortDirection" wire:click="sort('warehouse_id')">Gudang Penugasan</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->teams as $team)
                    <flux:table.row :key="$team->id">
                        <flux:table.cell class="font-medium text-zinc-900 dark:text-white">
                            {{ $team->name }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($team->warehouse)
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    {{ $team->warehouse->name }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-400">
                                    Belum Ditugaskan
                                </span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item wire:click="editTeam({{ $team->id }})" icon="pencil-square">Edit</flux:menu.item>
                                    <x-delete-modal id="delete-team-{{ $team->id }}" action="deleteTeam({{ $team->id }})" requireSlide="true" title="Hapus Tim?" description="Tim yang dihapus tidak dapat dikembalikan." />
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3" class="py-12">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-3 mb-4">
                                    <flux:icon.users class="w-6 h-6 text-zinc-500 dark:text-zinc-400" />
                                </div>
                                <flux:heading size="lg">Belum ada data</flux:heading>
                                <flux:text class="mt-2 mb-4 text-sm text-zinc-500 dark:text-zinc-400">Mulai dengan menambahkan data baru ke dalam sistem.</flux:text>
                                <div class="mt-2">
                                    <flux:button wire:click="openCreateModal" variant="primary" icon="plus">Tambah Tim</flux:button>
                                </div>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->teams->total() > 0)
            <flux:pagination :paginator="$this->teams" class="p-4" />
        @endif
    </div>

    <!-- Modal Form -->
    <flux:modal name="team-modal" class="md:w-96 !rounded-3xl" scroll="body" :closable="false">
        <form wire:submit="save">
            <flux:heading>{{ $team_id ? 'Edit Tim' : 'Tambah Tim Baru' }}</flux:heading>
            <flux:description>Masukkan nama tim dan pilih lokasi gudangnya.</flux:description>

            <div class="mt-4 space-y-4">
                <div class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 overflow-hidden shadow-xs">
                    <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">Nama Tim</span>
                        <div class="flex-1">
                            <input wire:model="name" type="text" placeholder="Misal: Tim A" class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" required />
                        </div>
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    </label>

                    <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">Tugaskan ke</span>
                        <div class="flex-1">
                            <x-searchable-select wire:model="warehouse_id" :options="$this->warehouses" :searchable="true" variant="ios" placeholder="Pilih gudang..." />
                        </div>
                    </label>
                </div>
                <x-error-ios name="name" />
                <x-error-ios name="warehouse_id" />
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="outline">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
