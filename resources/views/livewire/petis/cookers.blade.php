<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <flux:badge color="amber" size="sm" icon="fire">Produksi Petis</flux:badge>
                <flux:heading size="xl" class="font-bold">Master Koki Petis</flux:heading>
            </div>
            <flux:subheading size="sm" class="mt-1">
                Kelola daftar juru masak / tim pembuat petis. Perubahan koki tidak mempengaruhi riwayat produksi sebelumnya.
            </flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            @can('petis_cookers:create')
                <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                    Tambah Koki
                </flux:button>
            @endcan
        </div>
    </div>

    {{-- Filter Gudang --}}
    <div class="flex flex-wrap items-center gap-4 bg-zinc-50 dark:bg-zinc-900/60 p-3.5 rounded-2xl border border-zinc-200 dark:border-white/10">
        <div class="flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
            <flux:icon.building-office class="w-4 h-4 text-zinc-500" />
            <span>Pilih Gudang:</span>
        </div>
        <div class="w-64">
            <flux:select wire:model.live="warehouse_id" placeholder="Pilih Gudang">
                @foreach ($this->warehouses as $wh)
                    <flux:select.option value="{{ $wh->id }}">{{ $wh->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    {{-- Table Koki --}}
    <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-white/10 overflow-hidden shadow-xs">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="w-16 text-center">Urutan</flux:table.column>
                <flux:table.column>Nama Koki / Tim</flux:table.column>
                <flux:table.column>Gudang</flux:table.column>
                <flux:table.column class="text-center">Status</flux:table.column>
                @canany(['petis_cookers:update', 'petis_cookers:delete'])
                    <flux:table.column class="text-end">Aksi</flux:table.column>
                @endcanany
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->cookers as $cooker)
                    <flux:table.row :key="$cooker->id">
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

                        <flux:table.cell class="text-zinc-600 dark:text-zinc-400">
                            {{ $cooker->warehouse->name ?? '-' }}
                        </flux:table.cell>

                        <flux:table.cell class="text-center">
                            @can('petis_cookers:update')
                                <button type="button" wire:click="toggleActive({{ $cooker->id }})" class="cursor-pointer">
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

                        @canany(['petis_cookers:update', 'petis_cookers:delete'])
                            <flux:table.cell class="text-end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="bottom" />
                                    <flux:menu>
                                        @can('petis_cookers:update')
                                            <flux:menu.item wire:click="openEditModal({{ $cooker->id }})" icon="pencil-square">
                                                Edit
                                            </flux:menu.item>
                                        @endcan
                                        @can('petis_cookers:delete')
                                            <flux:menu.separator />
                                            <flux:menu.item wire:click="delete({{ $cooker->id }})"
                                                wire:confirm="Yakin ingin menghapus koki {{ $cooker->name }}? Riwayat produksi lampau tetap aman tersimpan."
                                                variant="danger" icon="trash">
                                                Hapus
                                            </flux:menu.item>
                                        @endcan
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        @endcanany
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5" class="py-8 text-center text-zinc-500">
                            Belum ada koki terdaftar di gudang ini. Klik <strong>Tambah Koki</strong> untuk menambahkan.
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Modal Tambah / Edit Koki --}}
    <flux:modal :closable="false" scroll="body" name="cooker-modal" class="md:w-96 !rounded-3xl">
        <form wire:submit="save" class="space-y-5">
            <div>
                <flux:heading size="lg">{{ $cooker_id ? 'Edit Koki Petis' : 'Tambah Koki Petis' }}</flux:heading>
                <flux:subheading size="sm">
                    Masukkan nama juru masak atau tim produksi.
                </flux:subheading>
            </div>

            <div class="space-y-4">
                <flux:input wire:model="name" label="Nama Koki / Tim" placeholder="Misal: Samsul, Tim A" required autofocus />

                <flux:input type="number" wire:model="sort_order" label="Urutan Tampilan Kolom" min="1" required />

                {{-- Switch Aktif --}}
                <div class="bg-zinc-50 dark:bg-white/5 rounded-2xl border border-zinc-200 dark:border-white/10 p-3.5">
                    <label class="flex items-center justify-between cursor-pointer select-none">
                        <div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">Status Aktif</div>
                            <div class="text-xs text-zinc-500 mt-0.5">Koki aktif akan muncul sebagai kolom di form proses masakan</div>
                        </div>
                        <x-switch wire:model="is_active" />
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Simpan
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
