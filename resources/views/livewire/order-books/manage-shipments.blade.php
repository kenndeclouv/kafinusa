<div x-data="shipmentManager(@js($this->ordersWithItems->flatMap->orderItems->mapWithKeys(fn($i) => [$i->id => $i->item->weight])), @entangle('assignments'), @entangle('totalBatches'))">
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Atur Pembagian Muatan</flux:heading>
            <flux:subheading>
                {{ $orderBook->market->name }} &bull; {{ $orderBook->book_date->format('d M Y') }} &bull; Sales:
                {{ $orderBook->employee->name }}
            </flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <flux:button :href="route('order-books.show', $orderBook)" wire:navigate variant="filled" icon="arrow-left"
                class="w-full sm:w-auto">
                Kembali
            </flux:button>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <flux:button wire:click="addBatch" variant="primary" icon="plus" class="flex-1 sm:flex-none">
                    Tambah Muatan
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Batch Weight Summary Cards --}}
    <div class="flex overflow-x-auto gap-3 mb-6 pb-2 snap-x">
        {{-- Grand Total Card --}}
        <div
            class="min-w-[220px] flex-1 shrink-0 rounded-2xl bg-accent p-4 flex flex-col gap-1 snap-start relative overflow-hidden">
            <div class="flex items-center justify-between relative z-10">
                <span class="text-sm font-semibold ">Total Keseluruhan</span>
            </div>
            <span class="text-xl font-bold relative z-10"
                x-text="formatWeight(Object.values(batchTotals).reduce((a, b) => a + b, 0))">
                0 gr
            </span>
            <div class="mt-auto relative z-10 pt-2">
                <span class="text-xs text-white/70">Semua muatan digabung</span>
            </div>
        </div>

        @for ($b = 1; $b <= $totalBatches; $b++)
            <div
                class="min-w-[220px] flex-1 shrink-0 rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 p-4 flex flex-col gap-1 snap-start">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Muatan
                        {{ $b }}</span>
                    @if ($totalBatches > 1 && $b > 1)
                        <flux:button wire:click="removeBatch({{ $b }})" variant="ghost" size="sm"
                            icon="trash" class="text-red-500!" />
                    @endif
                </div>
                <span class="text-xl font-bold text-zinc-900 dark:text-white"
                    x-text="formatWeight(batchTotals[{{ $b }}] || 0)">
                    0 gr
                </span>
                <div class="w-full h-1.5 rounded-full bg-zinc-200 dark:bg-white/10 mt-1">
                    <div class="h-1.5 rounded-full transition-all duration-300"
                        x-bind:class="{
                            'bg-red-500': ((batchTotals[{{ $b }}] || 0) / 7000000) * 100 > 100,
                            'bg-amber-500': ((batchTotals[{{ $b }}] || 0) / 7000000) * 100 > 85 && ((
                                batchTotals[{{ $b }}] || 0) / 7000000) * 100 <= 100,
                            'bg-emerald-500': ((batchTotals[{{ $b }}] || 0) / 7000000) * 100 <= 85
                        }"
                        x-bind:style="`width: ${Math.min(100, ((batchTotals[{{ $b }}] || 0) / 7000000) * 100)}%`">
                    </div>
                </div>
                <span class="text-xs text-zinc-500">Maks. 7 ton/truk</span>
            </div>
        @endfor
    </div>

    {{-- Main Table --}}
    <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-white/10">
                        <th class="text-left px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-300 min-w-[140px]">
                            Pelanggan</th>
                        <th class="text-left px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-300 min-w-[120px]">
                            Barang</th>
                        <th class="text-center px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-300 min-w-[80px]">
                            Total Qty</th>
                        <th class="text-center px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-300 min-w-[80px]">
                            Berat/Unit</th>
                        @for ($b = 1; $b <= $totalBatches; $b++)
                            <th
                                class="text-center px-4 py-3 font-semibold min-w-[120px] {{ $b === 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-blue-600 dark:text-blue-400' }}">
                                Muatan {{ $b }}
                            </th>
                        @endfor
                        <th class="text-center px-4 py-3 font-semibold text-zinc-700 dark:text-zinc-300 min-w-[80px]">
                            Sisa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->ordersWithItems as $order)
                        @foreach ($order->orderItems as $orderItemIndex => $orderItem)
                            @php
                                $totalAssigned = collect($assignments[$orderItem->id] ?? [])->sum();
                                $remaining = $orderItem->quantity - $totalAssigned;
                            @endphp
                            <tr wire:key="row-{{ $orderItem->id }}"
                                class="border-b border-zinc-100 dark:border-white/5 hover:bg-white dark:hover:bg-white/[0.03] transition-colors">
                                {{-- Customer (only show on first item of each order) --}}
                                @if ($orderItemIndex === 0)
                                    <td class="px-4 py-2.5 " rowspan="{{ $order->orderItems->count() }}">
                                        <div class="flex items-start gap-2">
                                            <flux:dropdown align="start">
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" class="px-2 -ml-2" />
                                                
                                                <flux:menu>
                                                    @for ($b = 1; $b <= $totalBatches; $b++)
                                                        <flux:menu.item wire:click="moveToBatch({{ $order->id }}, {{ $b }})">
                                                            Pindah semua ke Muatan {{ $b }}
                                                        </flux:menu.item>
                                                    @endfor
                                                </flux:menu>
                                            </flux:dropdown>
                                            <div>
                                                <span
                                                    class="font-medium text-zinc-900 dark:text-white">{{ $order->customer->name }}</span>
                                                <div class="text-xs text-zinc-500 mt-0.5">
                                                    {{ $order->customer->category->name ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                @endif

                                {{-- Item --}}
                                <td class="px-4 py-2.5 text-zinc-700 dark:text-zinc-300">{{ $orderItem->item->name }}
                                </td>

                                {{-- Total Qty --}}
                                <td class="px-4 py-2.5 text-center font-medium text-zinc-900 dark:text-white">
                                    {{ $orderItem->quantity }}</td>

                                {{-- Weight per unit --}}
                                <td class="px-4 py-2.5 text-center text-zinc-500 dark:text-zinc-400 text-xs">
                                    {{ formatWeight($orderItem->item->weight) }}
                                </td>

                                {{-- Batch input columns --}}
                                @for ($b = 1; $b <= $totalBatches; $b++)
                                    <td class="px-2 py-1.5 text-center">
                                        <div class="w-28 mx-auto">
                                            <x-stepper variant="ios"
                                                x-model.number="assignments[{{ $orderItem->id }}][{{ $b }}]"
                                                x-on:input="updateAssignment({{ $orderItem->id }}, {{ $b }}, {{ $orderItem->quantity }})"
                                                min="0" x-bind:max="(parseInt(assignments[{{ $orderItem->id }}][{{ $b }}]) || 0) + remaining({{ $orderItem->id }}, {{ $orderItem->quantity }})"
                                                placeholder="0" />
                                        </div>
                                    </td>
                                @endfor

                                {{-- Remaining --}}
                                <td class="px-4 py-2.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium"
                                        x-bind:class="{
                                            'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-300': remaining(
                                                {{ $orderItem->id }}, {{ $orderItem->quantity }}) === 0,
                                            'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-300': remaining(
                                                {{ $orderItem->id }}, {{ $orderItem->quantity }}) < 0,
                                            'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300': remaining(
                                                {{ $orderItem->id }}, {{ $orderItem->quantity }}) > 0
                                        }"
                                        x-text="remaining({{ $orderItem->id }}, {{ $orderItem->quantity }})">
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="{{ 4 + $totalBatches + 1 }}" class="py-12 text-center text-zinc-500">
                                Belum ada pesanan di buku order ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                {{-- Footer Total Row --}}
                @if ($this->ordersWithItems->isNotEmpty())
                    <tfoot class="border-t border-zinc-200 dark:border-white/10">
                        <tr class="bg-zinc-100 dark:bg-white/[0.07]">
                            <td class="px-4 py-3 font-bold text-zinc-900 dark:text-white " colspan="4">
                                Total per Muatan
                            </td>
                            @for ($b = 1; $b <= $totalBatches; $b++)
                                <td class="px-4 py-3 text-center font-bold"
                                    x-bind:class="(batchTotals[{{ $b }}] || 0) > 7000000 ? 'text-red-500' :
                                        'text-zinc-900 dark:text-white'">
                                    <span x-text="formatWeight(batchTotals[{{ $b }}] || 0)"></span>
                                    <div x-show="(batchTotals[{{ $b }}] || 0) > 7000000"
                                        class="text-xs font-normal text-red-400" x-cloak>Melebihi 7 ton!</div>
                                </td>
                            @endfor
                            <td></td>
                        </tr>
                        <tr class="bg-zinc-200/80 dark:bg-white/[0.1]">
                            <td class="px-4 py-3 font-bold text-zinc-900 dark:text-white " colspan="4">
                                Total Keseluruhan (Semua Muatan)
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-emerald-700 dark:text-emerald-400"
                                colspan="{{ $totalBatches }}">
                                <span
                                    x-text="formatWeight(Object.values(batchTotals).reduce((a, b) => a + b, 0))"></span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Quick actions help text & Save Buttons --}}
    <div class="mt-6 flex flex-col lg:flex-row items-center justify-between gap-4">
        <p class="text-xs text-zinc-500 dark:text-zinc-400 text-center lg:text-left">
            Isi kolom muatan sesuai jumlah yang ingin dikirim. Kolom "Sisa" harus 0 sebelum bisa disimpan.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
            {{-- <flux:button wire:click="save('nota')" variant="outline" icon="document-text" class="w-full sm:w-auto">Simpan & Cetak Nota</flux:button> --}}
            <flux:button wire:click="save('delivery')" variant="outline" icon="clipboard-document-list" class="w-full sm:w-auto">Simpan & Cetak Pengiriman</flux:button>
            <flux:button wire:click="save('summary')" variant="primary" icon="truck" class="w-full sm:w-auto">Simpan & Cetak Daftar</flux:button>
        </div>
    </div>
</div>

@script
    <script>
        Alpine.data('shipmentManager', (itemsWeight, assignments, totalBatches) => ({
            itemsWeight,
            assignments,
            totalBatches,

            get batchTotals() {
                let totals = {};
                for (let b = 1; b <= this.totalBatches; b++) totals[b] = 0;

                for (let itemId in this.assignments) {
                    let weight = this.itemsWeight[itemId] || 0;
                    let batches = this.assignments[itemId];
                    for (let b = 1; b <= this.totalBatches; b++) {
                        totals[b] += (parseInt(batches[b]) || 0) * weight;
                    }
                }
                return totals;
            },

            formatWeight(grams) {
                let kg = grams / 1000;
                if (kg >= 1000) {
                    return (kg / 1000).toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' Ton';
                } else if (kg >= 1) {
                    return kg.toLocaleString('id-ID', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + ' Kg';
                }
                return Math.floor(grams).toLocaleString('id-ID') + ' gr';
            },

            updateAssignment(itemId, changedBatch, maxQty) {
                this.$nextTick(() => {
                    let max = parseInt(maxQty);
                    let val = parseInt(this.assignments[itemId][changedBatch]) || 0;

                    if (val < 0) {
                        val = 0;
                        this.assignments[itemId][changedBatch] = 0;
                    }

                    let sumOther = 0;
                    for (let b = 1; b <= this.totalBatches; b++) {
                        if (b !== changedBatch) {
                            sumOther += parseInt(this.assignments[itemId]?.[b] || 0);
                        }
                    }

                    let allowedMax = max - sumOther;

                    if (val > allowedMax) {
                        this.assignments[itemId][changedBatch] = Math.max(0, allowedMax);
                    }
                });
            },

            remaining(itemId, maxQty) {
                let sum = 0;
                for (let b = 1; b <= this.totalBatches; b++) {
                    sum += parseInt(this.assignments[itemId]?.[b] || 0);
                }
                return maxQty - sum;
            }
        }));
    </script>
@endscript
