<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Arsip Buku Order: {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</flux:heading>
            <flux:subheading>Menampilkan buku order dari bulan yang sudah ditutup (read-only).</flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari pasar, sales, status..."
                icon="magnifying-glass" class="w-full sm:w-64" />
            
            <flux:button href="{{ route('backups.index') }}" wire:navigate variant="outline" icon="arrow-left" class="w-full sm:w-auto">
                Kembali
            </flux:button>
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table
                class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'book_date'" :direction="$sortDirection"
                        wire:click="sort('book_date')">Tanggal</flux:table.column>
                    <flux:table.column class="!sticky !left-0 z-10 bg-zinc-50 dark:bg-zinc-800" sortable
                        :sorted="$sortBy === 'market_id'" :direction="$sortDirection" wire:click="sort('market_id')">
                        Pasar</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'employee_id'" :direction="$sortDirection"
                        wire:click="sort('employee_id')">Sales</flux:table.column>
                    <flux:table.column>Total Pesanan</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                        wire:click="sort('status')">Status</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->orderBooks as $book)
                        <flux:table.row :key="$book->id"
                            class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            x-on:click="Livewire.navigate('{{ route('order-books.show', $book->id) }}')">
                            
                            <flux:table.cell>{{ $book->book_date->format('d M Y') }}</flux:table.cell>
                            <flux:table.cell
                                class="!sticky !left-0 z-10 bg-zinc-50 dark:bg-zinc-800 group-hover:bg-zinc-50 dark:group-hover:bg-zinc-800/50">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $book->market->name }}</span>
                                <span class="text-xs text-zinc-500 ml-1">({{ $book->market->code }})</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:avatar size="xs" :name="$book->employee->name" />
                                    <span>{{ $book->employee->name }}</span>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $book->orders_count }} Pelanggan
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($book->status === 'draft')
                                    <flux:badge size="sm" color="amber">Draft</flux:badge>
                                @elseif($book->status === 'locked_for_delivery')
                                    <flux:badge size="sm" color="blue">Terkunci (Pengiriman)</flux:badge>
                                @else
                                    <flux:badge size="sm" color="green">Selesai</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell x-on:click.stop>
                                <div class="flex items-center gap-2">
                                    <flux:button href="{{ route('order-books.show', $book->id) }}" wire:navigate
                                        size="sm" variant="filled" icon="eye">Lihat Arsip</flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <tr>
                            <td colspan="100%" class="py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-3 mb-4">
                                        <flux:icon.folder-open class="w-6 h-6 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <flux:heading size="lg">Tidak ada data</flux:heading>
                                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Tidak ada buku order pada bulan ini.</flux:text>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>

        @if ($this->orderBooks->total() > 0)
            <flux:pagination :paginator="$this->orderBooks" class="p-4" />
        @endif
    </div>
</div>
