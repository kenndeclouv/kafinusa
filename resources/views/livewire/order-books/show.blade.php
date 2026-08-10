<div>
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Isi Buku Order: {{ $orderBook->market->name }}</flux:heading>
            <flux:subheading>
                Tanggal: {{ $orderBook->book_date->format('d M Y') }} &bull; Sales: {{ $orderBook->employee->name }}
            </flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <flux:button href="{{ route('order-books.index') }}" wire:navigate variant="filled" icon="arrow-left"
                class="w-full sm:w-auto">
                Kembali
            </flux:button>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                @can('orders:create')
                    <flux:button wire:click="openCreateModal" variant="primary" icon="plus" class="flex-1 sm:flex-none">
                        Tambah Pesanan
                    </flux:button>
                @endcan
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <flux:table
                class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                <flux:table.columns>
                    <flux:table.column>No.</flux:table.column>
                    <flux:table.column class="!sticky !left-0 z-10 bg-zinc-50 dark:bg-zinc-800">Nama Pelanggan
                    </flux:table.column>
                    <flux:table.column>Daftar Barang (Item)</flux:table.column>
                    <flux:table.column>Tonase (Berat)</flux:table.column>
                    <flux:table.column>Aksi</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->orders as $index => $order)
                        <flux:table.row :key="$order->id">
                            <flux:table.cell>{{ $this->orders->firstItem() + $index }}</flux:table.cell>
                            <flux:table.cell class="!sticky !left-0 z-10 bg-zinc-50 dark:bg-zinc-800">
                                <span
                                    class="font-medium text-zinc-900 dark:text-white">{{ $order->customer->name }}</span>
                                <div class="text-xs text-zinc-500">{{ $order->customer->customerCategory->name ?? '' }}
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <ul class="list-disc list-inside text-sm text-zinc-700 dark:text-zinc-300">
                                    @foreach ($order->orderItems as $orderItem)
                                        <li class="mb-0.5">{{ $orderItem->item->name }} <flux:badge color="sky"
                                                size="sm">{{ $orderItem->quantity }} item</flux:badge>
                                        </li>
                                    @endforeach
                                </ul>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ formatWeight($order->total_calculated_weight) }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        @can('orders:update')
                                            <flux:menu.item wire:click="editOrder({{ $order->id }})" icon="pencil">Edit
                                            </flux:menu.item>
                                        @endcan
                                        @can('orders:delete')
                                            <x-delete-modal id="delete-order-{{ $order->id }}"
                                                action="deleteOrder({{ $order->id }})" requireSlide="true"
                                                title="Hapus Pesanan?"
                                                description="Menghapus pesanan pelanggan ini tidak dapat dibatalkan." />
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
                                    <flux:heading size="lg">Belum ada pesanan</flux:heading>
                                    <flux:text class="mt-2 mb-4 text-sm text-zinc-500 dark:text-zinc-400">Tambahkan
                                        pelanggan dan daftar belanjaan mereka ke buku ini.</flux:text>
                                    @can('orders:create')
                                        <div class="mt-2">
                                            <flux:button wire:click="openCreateModal" variant="primary" icon="plus">
                                                Tambah Pesanan</flux:button>
                                        </div>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </flux:table.rows>
                @if ($this->orders->count() > 0)
                    <tfoot class="border-t border-zinc-200 dark:border-white/10">
                        <flux:table.row class="bg-zinc-50 dark:bg-white/5">
                            <flux:table.cell></flux:table.cell>
                            <flux:table.cell>
                                <span class="font-semibold text-zinc-900 dark:text-white">Total Keseluruhan</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button x-on:click="$flux.modal('item-summary').show()" size="sm"
                                    variant="subtle" icon="list-bullet">Lihat Rekap Barang</flux:button>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span
                                    class="font-semibold text-zinc-900 dark:text-white">{{ formatWeight($this->summary['totalWeight']) }}</span>
                            </flux:table.cell>
                            <flux:table.cell></flux:table.cell>
                        </flux:table.row>
                    </tfoot>
                @endif
            </flux:table>
        </div>

        @if ($this->orders->total() > 0)
            <flux:pagination :paginator="$this->orders" class="p-4" />
        @endif

    </div>

    @if ($this->orders->total() > 0)
        <div
            class="py-4 flex flex-col sm:flex-row items-center justify-end gap-3">
            <flux:button href="{{ route('order-books.unordered-customers', $orderBook) }}" wire:navigate
                variant="outline" icon="users" class="w-full sm:w-auto">
                Pelanggan Tidak Beli
            </flux:button>

            <flux:button href="{{ route('order-books.shipments', $orderBook) }}" wire:navigate variant="primary"
                icon="truck" class="w-full sm:w-auto">
                Atur Pembagian Muatan
            </flux:button>
        </div>
    @endif

    <!-- Create/Edit Modal -->
    <flux:modal :closable="false" scroll="body" name="create-order-modal" class="md:max-w-2xl !rounded-3xl">
        <form wire:submit="save">
            <flux:heading>{{ $editingOrderId ? 'Edit Pesanan Pelanggan' : 'Tambah Pesanan Pelanggan' }}</flux:heading>
            <flux:description>
                {{ $editingOrderId ? 'Anda akan mengedit data pesanan.' : 'Masukkan daftar barang yang dipesan oleh pelanggan.' }}
            </flux:description>

            <div class="mt-6 space-y-6">
                <!-- Customer Selection -->
                <div>
                    <div
                        class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] {{ $editingOrderId ? 'cursor-not-allowed opacity-75' : 'cursor-text' }}">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Pelanggan
                            </span>
                            <div class="flex-1">
                                @if ($editingOrderId)
                                    <x-searchable-select wire:model="customer_id" :options="App\Models\Customer::where('id', $customer_id)
                                        ->pluck('name', 'id')
                                        ->toArray()" :disabled="true"
                                        variant="ios" />
                                @else
                                    <x-searchable-select wire:model="customer_id" :options="$this->customers->mapWithKeys(fn($c) => [$c->id => $c->name])->toArray()" :searchable="true"
                                        variant="ios" placeholder="Pilih pelanggan..." />
                                @endif
                            </div>
                        </label>
                        <x-error-ios name="customer_id" />
                    </div>
                </div>

                <!-- Dynamic Order Items -->
                <div>
                    <flux:heading size="sm" class="mb-3 px-1">Daftar Barang (Item)</flux:heading>

                    <div
                        class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">
                        @foreach ($orderItems as $index => $item)
                            <div class="flex flex-row items-center px-4 py-2 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07]"
                                wire:key="item-{{ $index }}">
                                <div class="flex-1 w-full">
                                    <x-searchable-select wire:model="orderItems.{{ $index }}.item_id"
                                        :options="$this->items->mapWithKeys(fn($i) => [$i->id => $i->name])->toArray()" variant="ios" placeholder="Pilih Barang..."
                                        class="[&>button]:!ms-0 [&>button]:!w-full" />
                                </div>

                                <div class="w-24 ml-4 shrink-0">
                                    <x-stepper wire:model="orderItems.{{ $index }}.quantity" variant="ios"
                                        min="1" step="1" />
                                </div>

                                <div class="ml-3 shrink-0">
                                    <flux:button type="button" size="sm"
                                        wire:click="removeOrderItem({{ $index }})" variant="danger"
                                        icon="trash" :disabled="count($orderItems) <= 1"
                                        class="{{ count($orderItems) <= 1 ? 'opacity-50' : '' }}" />
                                </div>

                                @if (!$loop->last)
                                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10">
                                    </div>
                                @endif
                            </div>
                            @error('orderItems.' . $index . '.item_id')
                                <div class="px-4 py-1.5 text-xs text-red-500 bg-red-50 dark:bg-red-500/10">
                                    {{ $message }}</div>
                            @enderror
                        @endforeach

                        <div class="relative">
                            <div class="absolute top-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                            <button type="button" wire:click="addOrderItem"
                                class="w-full py-3 flex items-center justify-center gap-2 text-[14px] font-medium text-accent hover:bg-zinc-50 dark:hover:bg-white/[0.02] transition-colors cursor-text">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Barang
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-2">
                <flux:button class="!rounded-full" type="submit" variant="primary">
                    {{ $editingOrderId ? 'Simpan Perubahan' : 'Tambah Pesanan' }}</flux:button>
                <flux:button class="!rounded-full" x-on:click="$flux.modal('create-order-modal').close()"
                    variant="outline">
                    Batal</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Item Summary Modal -->
    <flux:modal :closable="false" scroll="body" name="item-summary" class="md:max-w-xl !rounded-3xl">
        <flux:heading>Rekap Barang</flux:heading>
        <flux:subheading>Total keseluruhan barang yang dipesan di buku ini.</flux:subheading>

        <div class="mt-6">
            <div
                class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
                <flux:table
                    class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                    <flux:table.columns>
                        <flux:table.column>Nama Barang</flux:table.column>
                        <flux:table.column>Total Dipesan</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse($this->summary['items'] as $item)
                            <flux:table.row>
                                <flux:table.cell>{{ $item['name'] }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="sky">{{ $item['quantity'] }} item</flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="2" class="text-center text-zinc-500 py-6">Belum ada
                                    barang
                                    yang dipesan.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                    <tfoot class="border-t border-zinc-200 dark:border-white/10">
                        <flux:table.row class="bg-zinc-50 dark:bg-white/5">
                            <flux:table.cell>
                                <span class="font-semibold text-zinc-900 dark:text-white">Total Keseluruhan</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span
                                    class="font-semibold text-zinc-900 dark:text-white">{{ $this->summary['totalItemsCount'] }}
                                    item</span>
                            </flux:table.cell>
                        </flux:table.row>
                    </tfoot>
                </flux:table>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <flux:button x-on:click="$flux.modal('item-summary').close()" variant="ghost">Tutup</flux:button>
        </div>
    </flux:modal>
</div>
