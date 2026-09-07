<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Tutup Buku & Backup</flux:heading>
            <flux:subheading>Kelola status tutup buku bulanan. Bulan yang ditutup tidak bisa dimodifikasi.</flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
            @can('backups:create')
                <flux:button x-on:click="$flux.modal('create-backup-modal').show()" variant="primary" icon="lock-closed" class="w-full sm:w-auto">
                    Tutup Buku Manual
                </flux:button>
            @endcan
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table
                class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column>Bulan</flux:table.column>
                    <flux:table.column>Tanggal Ditutup</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->backups as $backup)
                        <flux:table.row :key="$backup->id">
                            <flux:table.cell>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($backup->month . '-01')->translatedFormat('F Y') }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $backup->locked_at ? $backup->locked_at->format('d M Y H:i') : '-' }}
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($backup->locked_at)
                                    <flux:badge size="sm" color="amber">Terkunci</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc">Draft</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button href="{{ route('backups.show', $backup->month) }}" wire:navigate size="sm" variant="filled" icon="archive-box">Lihat Arsip</flux:button>
                                    
                                    @can('backups:delete')
                                        <x-delete-modal id="delete-backup-{{ $backup->id }}"
                                            action="deleteBackup({{ $backup->id }})" requireSlide="true"
                                            title="Buka Kunci Bulan Ini?"
                                            description="Membuka kunci ini akan mengizinkan modifikasi data Buku Order pada bulan tersebut.">
                                            <flux:button variant="danger" size="sm" icon="lock-open">Buka Kunci</flux:button>
                                        </x-delete-modal>
                                    @endcan
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <tr>
                            <td colspan="100%" class="py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-3 mb-4">
                                        <flux:icon.lock-open class="w-6 h-6 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <flux:heading size="lg">Belum ada bulan yang ditutup</flux:heading>
                                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Data otomatis ditutup pada pergantian bulan.</flux:text>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->backups->total() > 0)
            <flux:pagination :paginator="$this->backups" class="p-4" />
        @endif
    </div>

    <!-- Create Backup Modal -->
    <flux:modal :closable="false" scroll="body" name="create-backup-modal" class="md:max-w-xl !rounded-3xl">
        <form wire:submit="lockMonth">
            <flux:heading>Tutup Buku Manual</flux:heading>
            <flux:description>
                Sistem mengunci secara otomatis pada awal bulan, namun Anda bisa mengunci bulan secara manual di sini.
            </flux:description>

            <div class="mt-6 space-y-6">
                <div
                    class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">
                    <label
                        class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span
                            class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Bulan (YYYY-MM)
                        </span>
                        <div class="flex-1">
                            <input wire:model="monthToLock" type="month" class="w-full bg-transparent border-0 focus:ring-0 p-0 text-zinc-900 dark:text-white focus:outline-none" />
                        </div>
                    </label>
                </div>
                @error('monthToLock')
                    <div class="px-4 py-1 text-sm text-red-500">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-8 flex flex-col gap-2">
                <flux:button type="submit" variant="primary" class="!rounded-full">Kunci Sekarang</flux:button>
                <flux:button x-on:click="$flux.modal('create-backup-modal').close()" variant="outline" class="!rounded-full">Batal</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
