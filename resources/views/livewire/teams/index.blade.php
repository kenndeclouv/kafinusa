<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Master Tim & Koki</flux:heading>
            <flux:subheading>Kelola tim produksi harian dan juru masak petis per gudang.</flux:subheading>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari..." class="w-full sm:w-60" />

            <x-searchable-select wire:model.live="filter_warehouse_id" :options="$this->warehouses" variant="ios" placeholder="Semua Gudang" class="w-full sm:w-48" />

            @if ($activeTab === 'teams')
                <flux:button wire:click="openCreateModal" variant="primary" icon="plus" class="ms-auto w-full sm:w-auto">
                    Tambah Tim
                </flux:button>
            @else
                @can('petis_cookers:create')
                    <flux:button wire:click="openCreateCookerModal" variant="primary" icon="plus" class="ms-auto w-full sm:w-auto">
                        Tambah Koki
                    </flux:button>
                @endcan
            @endif
        </div>
    </div>

    {{-- Tabs Segmented Control --}}
    <div class="mb-5">
        <x-tabs :active="$activeTab" @tab-change="$wire.set('activeTab', $event.detail)">
            <button type="button" class="x-tabs-btn" data-tab="teams" @click="select('teams', $el)">
                Tim Produksi
            </button>
            <button type="button" class="x-tabs-btn" data-tab="cookers" @click="select('cookers', $el)">
                Koki Petis
            </button>
        </x-tabs>
    </div>

    {{-- TAB 1: TIM PRODUKSI --}}
    @if ($activeTab === 'teams')
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
                            <flux:table.row :key="'team-'.$team->id">
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
                                        <flux:heading size="lg">Belum ada tim</flux:heading>
                                        <flux:text class="mt-2 mb-4 text-sm text-zinc-500 dark:text-zinc-400">Mulai dengan menambahkan tim produksi baru.</flux:text>
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
    @else
        {{-- TAB 2: KOKI PETIS --}}
        <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <flux:table class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                    <flux:table.columns>
                        <flux:table.column class="w-16 text-center">Urutan</flux:table.column>
                        <flux:table.column>Nama Koki / Tim</flux:table.column>
                        <flux:table.column>Gudang Penugasan</flux:table.column>
                        <flux:table.column class="text-center">Status</flux:table.column>
                        <flux:table.column>Aksi</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($this->cookers as $cooker)
                            <flux:table.row :key="'cooker-'.$cooker->id">
                                <flux:table.cell class="text-center font-mono font-medium text-zinc-500">
                                    {{ $cooker->sort_order }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center">
                                            {{ strtoupper(substr($cooker->name, 0, 2)) }}
                                        </div>
                                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $cooker->name }}</span>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    @if($cooker->warehouse)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                            {{ $cooker->warehouse->name }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-400">
                                            Semua Gudang
                                        </span>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell class="text-center">
                                    @can('petis_cookers:update')
                                        <button type="button" wire:click="toggleCookerActive({{ $cooker->id }})" class="cursor-pointer">
                                            @if ($cooker->is_active)
                                                <flux:badge color="green" size="sm" icon="check-circle">Aktif</flux:badge>
                                            @else
                                                <flux:badge color="zinc" size="sm" icon="x-circle">Non-aktif</flux:badge>
                                            @endif
                                        </button>
                                    @else
                                        @if ($cooker->is_active)
                                            <flux:badge color="green" size="sm" icon="check-circle">Aktif</flux:badge>
                                        @else
                                            <flux:badge color="zinc" size="sm" icon="x-circle">Non-aktif</flux:badge>
                                        @endif
                                    @endcan
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            @can('petis_cookers:update')
                                                <flux:menu.item wire:click="editCooker({{ $cooker->id }})" icon="pencil-square">Edit</flux:menu.item>
                                            @endcan
                                            @can('petis_cookers:delete')
                                                <flux:menu.separator />
                                                <flux:menu.item wire:click="deleteCooker({{ $cooker->id }})"
                                                    wire:confirm="Yakin ingin menghapus koki {{ $cooker->name }}? Riwayat produksi lampau tetap aman."
                                                    variant="danger" icon="trash">
                                                    Hapus
                                                </flux:menu.item>
                                            @endcan
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="py-12">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="rounded-full bg-amber-500/10 p-3 mb-4 text-amber-600">
                                            <flux:icon.fire class="w-6 h-6" />
                                        </div>
                                        <flux:heading size="lg">Belum ada data koki</flux:heading>
                                        <flux:text class="mt-2 mb-4 text-sm text-zinc-500 dark:text-zinc-400">Tambahkan juru masak petis baru untuk gudang ini.</flux:text>
                                        @can('petis_cookers:create')
                                            <div class="mt-2">
                                                <flux:button wire:click="openCreateCookerModal" variant="primary" icon="plus">Tambah Koki</flux:button>
                                            </div>
                                        @endcan
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>

            @if ($this->cookers->total() > 0)
                <flux:pagination :paginator="$this->cookers" class="p-4" />
            @endif
        </div>
    @endif

    <!-- Modal Form Tim -->
    <flux:modal name="team-modal" class="md:w-96 !rounded-3xl" scroll="body" :closable="false">
        <form wire:submit="saveTeam">
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
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Modal Form Koki Petis -->
    <flux:modal name="cooker-modal" class="md:w-96 !rounded-3xl" scroll="body" :closable="false">
        <form wire:submit="saveCooker" class="space-y-5">
            <div>
                <flux:heading size="lg">{{ $cooker_id ? 'Edit Koki Petis' : 'Tambah Koki Petis' }}</flux:heading>
                <flux:subheading size="sm">Masukkan nama juru masak dan gudang penugasan.</flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:input wire:model="cooker_name" label="Nama Koki / Tim" placeholder="Misal: Samsul, Tim A" required autofocus />

                <div>
                    <label class="block text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-1.5">Gudang Penugasan</label>
                    <x-searchable-select wire:model="cooker_warehouse_id" :options="$this->warehouses" variant="ios" placeholder="Pilih gudang..." />
                </div>

                <flux:input type="number" wire:model="cooker_sort_order" label="Urutan Tampilan Kolom" min="1" required />

                <div class="bg-zinc-50 dark:bg-white/5 rounded-2xl border border-zinc-200 dark:border-white/10 p-3.5">
                    <label class="flex items-center justify-between cursor-pointer select-none">
                        <div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">Status Aktif</div>
                            <div class="text-xs text-zinc-500 mt-0.5">Koki aktif akan muncul sebagai kolom di tabel proses masakan</div>
                        </div>
                        <x-switch wire:model="cooker_is_active" />
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Simpan</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
