<div>
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Pelanggan Tidak Beli: {{ $orderBook->market->name }}</flux:heading>
            <flux:subheading>
                Tanggal: {{ $orderBook->book_date->format('d M Y') }} &bull; Sales: {{ $orderBook->employee->name }}
            </flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <flux:button href="{{ route('order-books.show', $orderBook) }}" wire:navigate variant="filled"
                icon="arrow-left" class="w-full sm:w-auto">Kembali
            </flux:button>
        </div>
    </div>

    <div class="space-y-6">
        @if ($this->customersNotBuying->isNotEmpty())
            @php
                $groupedCustomers = $this->customersNotBuying->groupBy(fn($c) => $c->category->name ?? 'Lain-lain');
            @endphp

            @foreach ($groupedCustomers as $categoryName => $customers)
                <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
                    <div class="px-6 py-4 border-b border-zinc-200 dark:border-white/5 bg-zinc-100/50 dark:bg-white/5">
                        <flux:heading size="lg">{{ $categoryName }}</flux:heading>
                    </div>
                    <flux:table
                        class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                        <flux:table.columns>
                            <flux:table.column>No.</flux:table.column>
                            <flux:table.column>Nama Pelanggan</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($customers as $index => $customer)
                                <flux:table.row :key="$customer->id">
                                    <flux:table.cell>{{ $index + 1 }}</flux:table.cell>
                                    <flux:table.cell class="font-medium text-zinc-900 dark:text-white">
                                        {{ $customer->name }}
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endforeach
        @else
            <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
                <div class="text-center py-12 text-zinc-500">
                    Luar biasa! Semua pelanggan di pasar ini belanja hari ini. 🎉
                </div>
            </div>
        @endif
    </div>
</div>
