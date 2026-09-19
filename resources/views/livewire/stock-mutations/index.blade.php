<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div>
            <flux:heading size="xl">Dashboard Mutasi Stok</flux:heading>
            <flux:subheading>Pilih gudang untuk melihat buku mutasi (ledger) dan mengatur stok manual.</flux:subheading>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
        @forelse($this->warehouses as $warehouse)
            <a wire:key="warehouse-{{ $warehouse->id }}" href="{{ route('stock-mutations.show', $warehouse->id) }}" wire:navigate class="block">
                <flux:card class="h-full hover:border-accent hover:shadow-md relative overflow-hidden group cursor-pointer">
                    <!-- Background Decoration -->
                    <div class="absolute top-0 right-0 -mt-6 -mr-6 text-zinc-100 dark:text-zinc-800/50 pointer-events-none group-hover:scale-110 transition-transform duration-300">
                        <flux:icon.building-office class="w-32 h-32 opacity-50" />
                    </div>

                    <div class="relative z-10 flex flex-col h-full">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 p-3 shadow-md">
                                <flux:icon.building-office class="w-5 h-5" />
                            </div>
                            <div>
                                <flux:heading size="lg" class="!font-bold">{{ $warehouse->name }}</flux:heading>
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-1">{{ $warehouse->description ?: 'Gudang operasional' }}</flux:text>
                            </div>
                        </div>

                        {{-- <div class="mt-auto pt-6 grid grid-cols-1 gap-4">
                            <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-white/10 p-4 shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-300">Macam Barang</flux:text>
                                    <div class="rounded-full bg-zinc-200/50 dark:bg-zinc-700 p-1.5">
                                        <flux:icon.cube class="w-4 h-4 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                </div>
                                <div class="flex items-baseline gap-2">
                                    <flux:heading size="2xl" class="!font-bold">{{ number_format($warehouse->stocks_count ?? 0) }}</flux:heading>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </flux:card>
            </a>
        @empty
            <div class="col-span-full py-12 text-center">
                <div class="flex flex-col items-center justify-center">
                    <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-4 mb-4">
                        <flux:icon.building-office class="w-8 h-8 text-zinc-500 dark:text-zinc-400" />
                    </div>
                    <flux:heading size="lg">Tidak Ada Akses Gudang</flux:heading>
                    <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-400 max-w-md mx-auto">
                        Anda saat ini belum memiliki akses ke gudang mana pun, atau belum ada gudang yang terdaftar di sistem.
                    </flux:text>
                </div>
            </div>
        @endforelse
    </div>
</div>
