<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Buku Order</flux:heading>
            <flux:subheading>Kelola buku order harian per pasar.</flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari pasar, sales, status..."
                icon="magnifying-glass" class="w-full sm:w-64" />
            <x-searchable-select wire:model.live="filterMonth" class="w-full sm:w-48" :options="['all' => 'Semua Bulan'] + $this->availableMonths"
                :searchable="false" />
            @can('order_books:create')
                <flux:button wire:click="openGenerateModal" variant="outline" icon="calendar-days" class="ms-auto w-full sm:w-auto">
                    Generate dari Jadwal
                </flux:button>
                <flux:button wire:click="openCreateModal" variant="primary" icon="plus" class="w-full sm:w-auto">
                    Buka Buku Baru
                </flux:button>
            @endcan
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table
                class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'book_date'" :direction="$sortDirection"
                        wire:click="sort('book_date')">Tanggal</flux:table.column>
                    <flux:table.column class="!sticky !left-0 z-10 bg-zinc-50 dark:bg-zinc-800" sortable :sorted="$sortBy === 'market_id'" :direction="$sortDirection"
                        wire:click="sort('market_id')">
                        Pasar</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'employee_id'" :direction="$sortDirection"
                        wire:click="sort('employee_id')">Sales</flux:table.column>
                    <flux:table.column>Total Pesanan</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                        wire:click="sort('status')">Status</flux:table.column>
                    @canany(['order_books:read', 'order_books:update', 'order_books:delete'])
                        <flux:table.column>Aksi</flux:table.column>
                    @endcanany
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->orderBooks as $book)
                        <flux:table.row :key="$book->id"
                            class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            x-on:click="Livewire.navigate('{{ route('order-books.show', $book->id) }}')">
                            <flux:table.cell>{{ $book->book_date->format('d M Y') }}</flux:table.cell>
                            <flux:table.cell class="!sticky !left-0 z-10 bg-zinc-50 dark:bg-zinc-800 group-hover:bg-zinc-50 dark:group-hover:bg-zinc-800/50">
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

                            @canany(['order_books:read', 'order_books:read-self', 'order_books:update',
                                'order_books:delete'])
                                <flux:table.cell x-on:click.stop>
                                    <div class="flex items-center gap-2">
                                        @canany(['order_books:read', 'order_books:read-self'])
                                            <flux:button href="{{ route('order-books.show', $book->id) }}" wire:navigate
                                                size="sm" variant="filled" icon="document-text">Isi Buku</flux:button>
                                        @endcanany

                                        <flux:dropdown>
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                            <flux:menu>
                                                @canany(['order_books:read', 'order_books:read-self'])
                                                    <flux:menu.item :href="route('order-books.shipments', $book->id)"
                                                        wire:navigate icon="truck">
                                                        Atur Muatan</flux:menu.item>
                                                @endcanany
                                                @can('order_books:update')
                                                    <flux:menu.item wire:click="editBook({{ $book->id }})" icon="pencil">
                                                        Edit</flux:menu.item>
                                                @endcan
                                                @can('order_books:delete')
                                                    <x-delete-modal id="delete-orderbook-{{ $book->id }}"
                                                        action="delete({{ $book->id }})" requireSlide="true"
                                                        title="Hapus Buku Order?"
                                                        description="Menghapus buku ini akan menghapus semua pesanan pelanggan di dalamnya. Tindakan ini tidak dapat dibatalkan." />
                                                @endcan
                                            </flux:menu>
                                        </flux:dropdown>
                                    </div>
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
                                    <flux:heading size="lg">Belum ada buku order</flux:heading>
                                    <flux:text class="mt-2 mb-4 text-sm text-zinc-500 dark:text-zinc-400">Mulai dengan
                                        membuat buku order untuk pasar tertentu.</flux:text>
                                    @can('order_books:create')
                                        <div class="mt-2">
                                            @can('order_books:create')
                                                <flux:button wire:click="openCreateModal" variant="primary" icon="plus">Buka
                                                    Buku Baru</flux:button>
                                            @endcan
                                        </div>
                                    @endcan
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

    <!-- Create/Edit Modal -->
    <flux:modal :closable="false" scroll="body" name="create-book-modal" class="md:max-w-2xl !rounded-3xl">
        <form wire:submit="save">
            <flux:heading>{{ $editingBookId ? 'Edit Buku Order' : 'Buka Buku Order Baru' }}</flux:heading>
            <flux:description>
                {{ $editingBookId ? 'Perbarui informasi buku order ini.' : 'Buat buku order harian untuk pasar tertentu.' }}
            </flux:description>

            <!-- iOS Style Connected List -->
            <div
                class="mt-4 bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">

                <!-- Pasar -->
                <label
                    class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        Pasar
                    </span>
                    <div class="flex-1 w-full">
                        <x-searchable-select wire:model="market_id" :options="$markets->mapWithKeys(fn($m) => [$m->id => $m->name])->toArray()" :searchable="true" variant="ios"
                            placeholder="Pilih pasar" class="w-full" />
                    </div>
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </label>
                <x-error-ios name="market_id" />

                <!-- Tanggal -->
                <label
                    class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        Tanggal
                    </span>
                    <div class="flex-1">
                        <x-date-picker variant="ios" wire:model="book_date" placeholder="Pilih Tanggal" />
                    </div>
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </label>
                <x-error-ios name="book_date" />

                <!-- Sales -->
                <label
                    class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        Sales
                    </span>
                    <div class="flex-1">
                        <x-searchable-select wire:model="employee_id" :options="$employees->mapWithKeys(fn($e) => [$e->id => $e->name])->toArray()" :searchable="true" variant="ios"
                            placeholder="Pilih sales" />
                    </div>
                    @if ($editingBookId)
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    @endif
                </label>
                <x-error-ios name="employee_id" />

                @if ($editingBookId)
                    <!-- Status -->
                    <label
                        class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span
                            class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Status
                        </span>
                        <div class="flex-1">
                            <x-searchable-select wire:model="status" :options="[
                                'draft' => 'Draft',
                                'locked_for_delivery' => 'Terkunci (Pengiriman)',
                                'completed' => 'Selesai',
                            ]" :searchable="false"
                                variant="ios" />
                        </div>
                    </label>
                    <x-error-ios name="status" />
                @endif
            </div>

            <div class="mt-6 flex flex-col gap-2">
                <flux:button class="!rounded-full" type="submit" variant="primary">
                    {{ $editingBookId ? 'Simpan Perubahan' : 'Buka Buku' }}</flux:button>
                <flux:button class="!rounded-full" x-on:click="$flux.modal('create-book-modal').close()"
                    variant="outline">
                    Batal</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Generate Books Modal -->
    <flux:modal :closable="false" scroll="body" name="generate-books-modal" class="md:max-w-xl !rounded-3xl">
        <form wire:submit="generateFromSchedule">
            <flux:heading>Generate Buku Order (Dari Jadwal)</flux:heading>
            <flux:description>
                Sistem akan membuat buku order secara otomatis berdasarkan Template Jadwal Kunjungan untuk rentang tanggal yang dipilih. Hari Jumat akan dilewati.
            </flux:description>

            <div class="mt-6 space-y-6">
                <div class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">
                    
                    <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Dari Tanggal
                        </span>
                        <div class="flex-1">
                            <x-date-picker variant="ios" wire:model="generateStartDate" placeholder="Pilih Tanggal" />
                        </div>
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    </label>
                    <x-error-ios name="generateStartDate" />

                    <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Sampai Tanggal
                        </span>
                        <div class="flex-1">
                            <x-date-picker variant="ios" wire:model="generateEndDate" placeholder="Pilih Tanggal" />
                        </div>
                    </label>
                    <x-error-ios name="generateEndDate" />

                </div>
            </div>

            <div class="mt-8 flex flex-col gap-2">
                <flux:button type="submit" variant="primary" class="!rounded-full">Generate Sekarang</flux:button>
                <flux:button x-on:click="$flux.modal('generate-books-modal').close()" variant="outline" class="!rounded-full">
                    Batal
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
