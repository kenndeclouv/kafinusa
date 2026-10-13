<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <flux:badge color="amber" size="sm" icon="fire">Produksi Petis</flux:badge>
                <flux:heading size="xl" class="font-bold">Sirkulasi Bahan Baku Petis</flux:heading>
            </div>
            <flux:subheading size="sm" class="mt-1">
                Pencatatan langsung (inline) Pembelian, Pemakaian, Penjualan, Stok Buku, dan Stok Opname 9 Bahan Baku Petis.
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Auto-save status indicator --}}
            <div class="hidden sm:flex items-center px-3 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-white/10">
                <div wire:loading class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400 font-medium">
                    <flux:icon name="arrow-path" class="w-3.5 h-3.5 animate-spin" />
                    <span>Menyimpan otomatis...</span>
                </div>
                <div wire:loading.remove class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                    <flux:icon name="check-circle" class="w-3.5 h-3.5" />
                    <span>Tersimpan otomatis</span>
                </div>
            </div>

            <flux:button variant="ghost" icon="printer"
                :href="route('petis.raw-materials.print', ['warehouse' => $warehouse_id, 'month' => $month])"
                target="_blank">
                Cetak
            </flux:button>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-4 bg-zinc-50 dark:bg-zinc-900/60 p-3.5 rounded-2xl border border-zinc-200 dark:border-white/10">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Gudang:</span>
                <div class="w-48">
                    <flux:select wire:model.live="warehouse_id" size="sm">
                        @foreach ($this->warehouses as $wh)
                            <flux:select.option value="{{ $wh->id }}">{{ $wh->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Bulan:</span>
                <flux:input type="month" wire:model.live="month" size="sm" class="w-40" />
            </div>

            {{-- Mobile Auto-save indicator --}}
            <div class="sm:hidden flex items-center">
                <div wire:loading class="flex items-center gap-1 text-[11px] text-amber-600 font-medium">
                    <flux:icon name="arrow-path" class="w-3 h-3 animate-spin" />
                    <span>Menyimpan...</span>
                </div>
                <div wire:loading.remove class="flex items-center gap-1 text-[11px] text-emerald-600 font-medium">
                    <flux:icon name="check-circle" class="w-3 h-3" />
                    <span>Tersimpan</span>
                </div>
            </div>
        </div>

        {{-- Segmented Controls Tab --}}
        <div>
            <x-tabs :active="$activeTab" @tab-change="$wire.set('activeTab', $event.detail)">
                <button type="button" class="x-tabs-btn" data-tab="rekap" @click="select('rekap', $el)">Semua Matrix</button>
                <button type="button" class="x-tabs-btn" data-tab="purchase" @click="select('purchase', $el)">Pembelian</button>
                <button type="button" class="x-tabs-btn" data-tab="usage" @click="select('usage', $el)">Pemakaian</button>
                <button type="button" class="x-tabs-btn" data-tab="sale" @click="select('sale', $el)">Penjualan</button>
                <button type="button" class="x-tabs-btn" data-tab="stock" @click="select('stock', $el)">Stok Buku</button>
                <button type="button" class="x-tabs-btn" data-tab="selisih" @click="select('selisih', $el)">Opname & Selisih</button>
            </x-tabs>
        </div>
    </div>

    @php
        $m = $this->monthlyData;
        $items = $this->rawItems;
        $days = $m['days'] ?? [];
        $initialStocks = $m['initial_stocks'] ?? [];
        $finalStocks = $m['final_stocks'] ?? [];
        $totals = $m['totals'] ?? [];
    @endphp

    {{-- Main Table --}}
    <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-white/10 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left whitespace-nowrap">
                <thead class="text-[11px] text-zinc-700 dark:text-zinc-300 uppercase bg-zinc-100/80 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-white/10">
                    @if ($activeTab === 'rekap')
                        {{-- 2-tier Multi Header untuk Matrix Lengkap --}}
                        <tr>
                            <th rowspan="2" class="px-3 py-3 font-bold border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-20 bg-zinc-100 dark:bg-zinc-800 w-14">
                                TGL
                            </th>
                            <th colspan="{{ $items->count() }}" class="px-3 py-2 text-center font-bold text-emerald-700 dark:text-emerald-400 border-r border-zinc-200 dark:border-white/10 bg-emerald-50/50 dark:bg-emerald-950/20">
                                PEMBELIAN
                            </th>
                            <th colspan="{{ $items->count() }}" class="px-3 py-2 text-center font-bold text-purple-700 dark:text-purple-400 border-r border-zinc-200 dark:border-white/10 bg-purple-50/50 dark:bg-purple-950/20">
                                PEMAKAIAN PRODUKSI
                            </th>
                            <th colspan="{{ $items->count() }}" class="px-3 py-2 text-center font-bold text-blue-700 dark:text-blue-400 border-r border-zinc-200 dark:border-white/10 bg-blue-50/50 dark:bg-blue-950/20">
                                PENJUALAN
                            </th>
                            <th colspan="{{ $items->count() }}" class="px-3 py-2 text-center font-bold text-teal-700 dark:text-teal-400 border-r border-zinc-200 dark:border-white/10 bg-teal-50/50 dark:bg-teal-950/20">
                                STOK BUKU
                            </th>
                            <th colspan="{{ $items->count() }}" class="px-3 py-2 text-center font-bold text-amber-700 dark:text-amber-400 border-r border-zinc-200 dark:border-white/10 bg-amber-50/50 dark:bg-amber-950/20">
                                STOK OPNAME (FISIK)
                            </th>
                            <th colspan="{{ $items->count() }}" class="px-3 py-2 text-center font-bold text-rose-700 dark:text-rose-400 bg-rose-50/50 dark:bg-rose-950/20">
                                SELISIH STOK
                            </th>
                        </tr>
                        <tr class="border-t border-zinc-200 dark:border-white/10 text-[10px]">
                            {{-- Pembelian --}}
                            @foreach ($items as $item)
                                <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-emerald-50/30 dark:bg-emerald-950/10">{{ $item->name }}</th>
                            @endforeach
                            {{-- Pemakaian --}}
                            @foreach ($items as $item)
                                <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-purple-50/30 dark:bg-purple-950/10">{{ $item->name }}</th>
                            @endforeach
                            {{-- Penjualan --}}
                            @foreach ($items as $item)
                                <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-blue-50/30 dark:bg-blue-950/10">{{ $item->name }}</th>
                            @endforeach
                            {{-- Stok Buku --}}
                            @foreach ($items as $item)
                                <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-teal-50/30 dark:bg-teal-950/10">{{ $item->name }}</th>
                            @endforeach
                            {{-- Opname --}}
                            @foreach ($items as $item)
                                <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-amber-50/30 dark:bg-amber-950/10">{{ $item->name }}</th>
                            @endforeach
                            {{-- Selisih --}}
                            @foreach ($items as $item)
                                <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-rose-50/30 dark:bg-rose-950/10">{{ $item->name }}</th>
                            @endforeach
                        </tr>
                    @else
                        {{-- Single Header untuk Tab Tertentu --}}
                        <tr>
                            <th class="px-3 py-3 font-bold border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-20 bg-zinc-100 dark:bg-zinc-800 w-16">
                                TGL
                            </th>
                            @foreach ($items as $item)
                                <th class="px-3 py-3 font-bold border-r border-zinc-200 dark:border-white/10 text-center min-w-[85px]">
                                    <div class="font-bold">{{ $item->name }}</div>
                                    <div class="text-[9px] text-zinc-400 font-normal">{{ $item->code }}</div>
                                </th>
                            @endforeach
                            <th class="px-3 py-3 text-center font-bold text-zinc-600 dark:text-zinc-300 min-w-[80px]">
                                TOTAL
                            </th>
                        </tr>
                    @endif
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-white/10 font-mono text-[11px]">
                    {{-- Row STOK AWAL --}}
                    @if (in_array($activeTab, ['rekap', 'stock', 'selisih']))
                        <tr class="bg-amber-500/10 dark:bg-amber-500/15 font-bold">
                            <td class="px-3 py-2 text-center sticky left-0 z-10 bg-amber-100 dark:bg-zinc-800 border-r border-zinc-200 dark:border-white/10 text-amber-900 dark:text-amber-300">
                                S AWAL
                            </td>
                            @if ($activeTab === 'rekap')
                                {{-- 3 section kosong untuk beli, pakai, jual --}}
                                @for ($s = 0; $s < 3; $s++)
                                    @foreach ($items as $item)
                                        <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400"></td>
                                    @endforeach
                                @endfor

                                {{-- Stok Buku Awal (Bisa diedit langsung!) --}}
                                @foreach ($items as $item)
                                    <td class="p-1 text-center border-r border-zinc-200 dark:border-white/10 bg-teal-50/30 dark:bg-teal-950/20">
                                        <input type="text" inputmode="decimal"
                                            wire:model.live.debounce.500ms="initial_stocks.{{ $item->id }}"
                                            class="w-full text-center bg-transparent border-transparent hover:border-teal-300 dark:hover:border-teal-700 focus:bg-white dark:focus:bg-zinc-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 rounded-md transition-all py-1 text-xs font-mono font-bold text-teal-800 dark:text-teal-300 placeholder-transparent"
                                            placeholder="" />
                                    </td>
                                @endforeach

                                {{-- Opname & Selisih Awal --}}
                                @for ($s = 0; $s < 2; $s++)
                                    @foreach ($items as $item)
                                        <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400"></td>
                                    @endforeach
                                @endfor
                            @else
                                {{-- Tab Stock atau Selisih --}}
                                @php $totalInitial = 0; @endphp
                                @foreach ($items as $item)
                                    @php $initVal = (float)($initial_stocks[$item->id] ?? 0); $totalInitial += $initVal; @endphp
                                    <td class="p-1 text-center border-r border-zinc-200 dark:border-white/10 bg-amber-50/50 dark:bg-amber-950/20">
                                        <input type="text" inputmode="decimal"
                                            wire:model.live.debounce.500ms="initial_stocks.{{ $item->id }}"
                                            class="w-full text-center bg-transparent border-transparent hover:border-amber-400 dark:hover:border-amber-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-md transition-all py-1 text-xs font-mono font-bold text-amber-900 dark:text-amber-300 placeholder-transparent"
                                            placeholder="" />
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-center font-bold text-amber-900 dark:text-amber-300">
                                    {{ $totalInitial > 0 ? number_format($totalInitial, 0, ',', '.') : '' }}
                                </td>
                            @endif
                        </tr>
                    @endif

                    {{-- Loop Hari 1 s/d Akhir Bulan --}}
                    @foreach ($days as $dayData)
                        @php
                            $rowBg = $dayData['is_sunday'] ? 'bg-rose-50/40 dark:bg-rose-950/15' : 'hover:bg-zinc-50 dark:hover:bg-white/5';
                        @endphp
                        <tr class="{{ $rowBg }}">
                            <td class="px-3 py-1.5 text-center sticky left-0 z-10 bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-white/10 font-medium">
                                <span class="{{ $dayData['is_sunday'] ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    {{ $dayData['day'] }}
                                </span>
                            </td>

                            @if ($activeTab === 'rekap')
                                {{-- Pembelian (Inline Input) --}}
                                @foreach ($items as $item)
                                    <td class="p-0.5 text-center border-r border-zinc-200 dark:border-white/10">
                                        <input type="text" inputmode="decimal"
                                            wire:model.live.debounce.500ms="grid.purchase.{{ $dayData['day'] }}.{{ $item->id }}"
                                            class="w-full text-center bg-transparent border-transparent hover:border-emerald-300 dark:hover:border-emerald-700 focus:bg-white dark:focus:bg-zinc-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-md transition-all py-1 text-xs font-mono {{ ($grid['purchase'][$dayData['day']][$item->id] ?? 0) > 0 ? 'text-emerald-700 dark:text-emerald-400 font-bold' : 'text-zinc-400' }} placeholder-transparent"
                                            placeholder="" />
                                    </td>
                                @endforeach

                                {{-- Pemakaian (Inline Input) --}}
                                @foreach ($items as $item)
                                    <td class="p-0.5 text-center border-r border-zinc-200 dark:border-white/10">
                                        <input type="text" inputmode="decimal"
                                            wire:model.live.debounce.500ms="grid.usage.{{ $dayData['day'] }}.{{ $item->id }}"
                                            class="w-full text-center bg-transparent border-transparent hover:border-purple-300 dark:hover:border-purple-700 focus:bg-white dark:focus:bg-zinc-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-md transition-all py-1 text-xs font-mono {{ ($grid['usage'][$dayData['day']][$item->id] ?? 0) > 0 ? 'text-purple-700 dark:text-purple-400 font-bold' : 'text-zinc-400' }} placeholder-transparent"
                                            placeholder="" />
                                    </td>
                                @endforeach

                                {{-- Penjualan (Inline Input) --}}
                                @foreach ($items as $item)
                                    <td class="p-0.5 text-center border-r border-zinc-200 dark:border-white/10">
                                        <input type="text" inputmode="decimal"
                                            wire:model.live.debounce.500ms="grid.sale.{{ $dayData['day'] }}.{{ $item->id }}"
                                            class="w-full text-center bg-transparent border-transparent hover:border-blue-300 dark:hover:border-blue-700 focus:bg-white dark:focus:bg-zinc-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-md transition-all py-1 text-xs font-mono {{ ($grid['sale'][$dayData['day']][$item->id] ?? 0) > 0 ? 'text-blue-700 dark:text-blue-400 font-bold' : 'text-zinc-400' }} placeholder-transparent"
                                            placeholder="" />
                                    </td>
                                @endforeach

                                {{-- Stok Buku (Kalkulasi Otomatis) --}}
                                @foreach ($items as $item)
                                    @php $val = $dayData['book_stock'][$item->id] ?? 0; @endphp
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 font-semibold text-zinc-900 dark:text-white">
                                        {{ $val != 0 ? number_format($val, 0, ',', '.') : '' }}
                                    </td>
                                @endforeach

                                {{-- Stok Opname (Inline Input) --}}
                                @foreach ($items as $item)
                                    <td class="p-0.5 text-center border-r border-zinc-200 dark:border-white/10">
                                        <input type="text" inputmode="decimal"
                                            wire:model.live.debounce.500ms="grid.opname.{{ $dayData['day'] }}.{{ $item->id }}"
                                            class="w-full text-center bg-transparent border-transparent hover:border-amber-300 dark:hover:border-amber-700 focus:bg-white dark:focus:bg-zinc-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-md transition-all py-1 text-xs font-mono {{ ($grid['opname'][$dayData['day']][$item->id] ?? '') !== '' ? 'text-amber-700 dark:text-amber-400 font-bold' : 'text-zinc-400' }} placeholder-transparent"
                                            placeholder="" />
                                    </td>
                                @endforeach

                                {{-- Selisih Stok (Kalkulasi Otomatis) --}}
                                @foreach ($items as $item)
                                    @php $val = $dayData['diff'][$item->id]; @endphp
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $val !== null && $val != 0 ? 'text-rose-600 dark:text-rose-400 font-bold bg-rose-50/30' : 'text-zinc-400' }}">
                                        {{ $val !== null && $val != 0 ? number_format($val, 0, ',', '.') : '' }}
                                    </td>
                                @endforeach

                            @elseif ($activeTab === 'purchase')
                                @php $dayTotal = 0; @endphp
                                @foreach ($items as $item)
                                    @php $val = (float)($grid['purchase'][$dayData['day']][$item->id] ?? 0); $dayTotal += $val; @endphp
                                    <td class="p-1 text-center border-r border-zinc-200 dark:border-white/10">
                                        <input type="text" inputmode="decimal"
                                            wire:model.live.debounce.500ms="grid.purchase.{{ $dayData['day'] }}.{{ $item->id }}"
                                            class="w-full text-center bg-transparent border-transparent hover:border-emerald-300 dark:hover:border-emerald-700 focus:bg-white dark:focus:bg-zinc-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-md transition-all py-1.5 text-xs font-mono font-medium {{ $val > 0 ? 'text-emerald-700 dark:text-emerald-400 font-bold' : 'text-zinc-600 dark:text-zinc-400' }} placeholder-transparent"
                                            placeholder="" />
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-center font-bold text-emerald-800 dark:text-emerald-400">
                                    {{ $dayTotal > 0 ? number_format($dayTotal, 0, ',', '.') : '' }}
                                </td>

                            @elseif ($activeTab === 'usage')
                                @php $dayTotal = 0; @endphp
                                @foreach ($items as $item)
                                    @php $val = (float)($grid['usage'][$dayData['day']][$item->id] ?? 0); $dayTotal += $val; @endphp
                                    <td class="p-1 text-center border-r border-zinc-200 dark:border-white/10">
                                        <input type="text" inputmode="decimal"
                                            wire:model.live.debounce.500ms="grid.usage.{{ $dayData['day'] }}.{{ $item->id }}"
                                            class="w-full text-center bg-transparent border-transparent hover:border-purple-300 dark:hover:border-purple-700 focus:bg-white dark:focus:bg-zinc-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-md transition-all py-1.5 text-xs font-mono font-medium {{ $val > 0 ? 'text-purple-700 dark:text-purple-400 font-bold' : 'text-zinc-600 dark:text-zinc-400' }} placeholder-transparent"
                                            placeholder="" />
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-center font-bold text-purple-800 dark:text-purple-400">
                                    {{ $dayTotal > 0 ? number_format($dayTotal, 0, ',', '.') : '' }}
                                </td>

                            @elseif ($activeTab === 'sale')
                                @php $dayTotal = 0; @endphp
                                @foreach ($items as $item)
                                    @php $val = (float)($grid['sale'][$dayData['day']][$item->id] ?? 0); $dayTotal += $val; @endphp
                                    <td class="p-1 text-center border-r border-zinc-200 dark:border-white/10">
                                        <input type="text" inputmode="decimal"
                                            wire:model.live.debounce.500ms="grid.sale.{{ $dayData['day'] }}.{{ $item->id }}"
                                            class="w-full text-center bg-transparent border-transparent hover:border-blue-300 dark:hover:border-blue-700 focus:bg-white dark:focus:bg-zinc-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-md transition-all py-1.5 text-xs font-mono font-medium {{ $val > 0 ? 'text-blue-700 dark:text-blue-400 font-bold' : 'text-zinc-600 dark:text-zinc-400' }} placeholder-transparent"
                                            placeholder="" />
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-center font-bold text-blue-800 dark:text-blue-400">
                                    {{ $dayTotal > 0 ? number_format($dayTotal, 0, ',', '.') : '' }}
                                </td>

                            @elseif ($activeTab === 'stock')
                                @php $dayTotal = 0; @endphp
                                @foreach ($items as $item)
                                    @php $val = $dayData['book_stock'][$item->id] ?? 0; $dayTotal += $val; @endphp
                                    <td class="px-3 py-2 text-center border-r border-zinc-200 dark:border-white/10 font-semibold text-zinc-900 dark:text-white">
                                        {{ $val != 0 ? number_format($val, 0, ',', '.') : '' }}
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-center font-bold text-zinc-900 dark:text-white">
                                    {{ $dayTotal != 0 ? number_format($dayTotal, 0, ',', '.') : '' }}
                                </td>

                            @elseif ($activeTab === 'selisih')
                                @php $dayTotalDiff = 0; $hasAnyDiff = false; @endphp
                                @foreach ($items as $item)
                                    @php
                                        $diff = $dayData['diff'][$item->id];
                                        if ($diff !== null) { $dayTotalDiff += $diff; $hasAnyDiff = true; }
                                    @endphp
                                    <td class="p-1 text-center border-r border-zinc-200 dark:border-white/10">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <input type="text" inputmode="decimal"
                                                wire:model.live.debounce.500ms="grid.opname.{{ $dayData['day'] }}.{{ $item->id }}"
                                                class="w-full text-center bg-transparent border-transparent hover:border-amber-300 dark:hover:border-amber-700 focus:bg-white dark:focus:bg-zinc-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-md transition-all py-1 text-xs font-mono font-bold {{ ($grid['opname'][$dayData['day']][$item->id] ?? '') !== '' ? 'text-amber-700 dark:text-amber-400' : 'text-zinc-400' }} placeholder-transparent"
                                                placeholder="" />
                                            @if ($diff !== null && $diff != 0)
                                                <span class="text-[10px] font-mono text-rose-600 dark:text-rose-400 font-bold">
                                                    Sel: {{ number_format($diff, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                                <td class="px-3 py-2 text-center font-bold {{ $hasAnyDiff && $dayTotalDiff != 0 ? 'text-rose-600' : 'text-zinc-400' }}">
                                    {{ $hasAnyDiff && $dayTotalDiff != 0 ? number_format($dayTotalDiff, 0, ',', '.') : '' }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>

                {{-- Footer Summary Total & Kali Input --}}
                <tfoot class="bg-zinc-100 dark:bg-zinc-800 text-[11px] font-bold font-mono border-t-2 border-zinc-300 dark:border-zinc-700">
                    @if ($activeTab === 'rekap')
                        {{-- Baris TOTAL --}}
                        <tr class="bg-zinc-200/70 dark:bg-zinc-800">
                            <td class="px-3 py-2 text-center sticky left-0 z-10 bg-zinc-200 dark:bg-zinc-800 border-r border-zinc-300 dark:border-zinc-700">
                                TOTAL
                            </td>
                            {{-- Pembelian --}}
                            @foreach ($items as $item)
                                <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-emerald-800 dark:text-emerald-400">
                                    {{ number_format($totals['purchase'][$item->id] ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach
                            {{-- Pemakaian --}}
                            @foreach ($items as $item)
                                <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-purple-800 dark:text-purple-400">
                                    {{ number_format($totals['usage'][$item->id] ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach
                            {{-- Penjualan --}}
                            @foreach ($items as $item)
                                <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-blue-800 dark:text-blue-400">
                                    {{ number_format($totals['sale'][$item->id] ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach
                            {{-- S AKHIR (Stok Buku Akhir) --}}
                            @foreach ($items as $item)
                                <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-teal-800 dark:text-teal-400">
                                    {{ number_format($finalStocks[$item->id] ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach
                            {{-- Opname & Selisih Kosong di baris total --}}
                            @for ($s = 0; $s < 2; $s++)
                                @foreach ($items as $item)
                                    <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10"></td>
                                @endforeach
                            @endfor
                        </tr>

                        {{-- Baris KALI INPUT --}}
                        <tr class="bg-zinc-100 dark:bg-zinc-800/80 text-[10px] text-zinc-600 dark:text-zinc-400">
                            <td class="px-3 py-1.5 text-center sticky left-0 z-10 bg-zinc-100 dark:bg-zinc-800 border-r border-zinc-300 dark:border-zinc-700">
                                KALI
                            </td>
                            @foreach ($items as $item)
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10">
                                    {{ ($totals['count_purchase'][$item->id] ?? 0) > 0 ? ($totals['count_purchase'][$item->id] . 'x') : '' }}
                                </td>
                            @endforeach
                            @foreach ($items as $item)
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10">
                                    {{ ($totals['count_usage'][$item->id] ?? 0) > 0 ? ($totals['count_usage'][$item->id] . 'x') : '' }}
                                </td>
                            @endforeach
                            @foreach ($items as $item)
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10">
                                    {{ ($totals['count_sale'][$item->id] ?? 0) > 0 ? ($totals['count_sale'][$item->id] . 'x') : '' }}
                                </td>
                            @endforeach
                            @for ($s = 0; $s < 3; $s++)
                                @foreach ($items as $item)
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10"></td>
                                @endforeach
                            @endfor
                        </tr>
                    @else
                        {{-- Tab Tertentu (purchase, usage, sale, stock, selisih) --}}
                        <tr class="bg-zinc-200/70 dark:bg-zinc-800">
                            <td class="px-3 py-2 text-center sticky left-0 z-10 bg-zinc-200 dark:bg-zinc-800 border-r border-zinc-300 dark:border-zinc-700">
                                {{ $activeTab === 'stock' ? 'S AKHIR' : 'TOTAL' }}
                            </td>
                            @php $grandTotal = 0; @endphp
                            @foreach ($items as $item)
                                @php
                                    $colVal = match ($activeTab) {
                                        'purchase' => $totals['purchase'][$item->id] ?? 0,
                                        'usage'    => $totals['usage'][$item->id] ?? 0,
                                        'sale'     => $totals['sale'][$item->id] ?? 0,
                                        'stock'    => $finalStocks[$item->id] ?? 0,
                                        'selisih'  => collect($days)->sum(fn($d) => $d['diff'][$item->id] ?? 0),
                                        default    => 0,
                                    };
                                    $grandTotal += $colVal;
                                @endphp
                                <td class="px-3 py-2 text-center border-r border-zinc-200 dark:border-white/10">
                                    {{ number_format($colVal, 0, ',', '.') }}
                                </td>
                            @endforeach
                            <td class="px-3 py-2 text-center">
                                {{ number_format($grandTotal, 0, ',', '.') }}
                            </td>
                        </tr>

                        @if (in_array($activeTab, ['purchase', 'usage', 'sale']))
                            <tr class="bg-zinc-100 dark:bg-zinc-800/80 text-[10px] text-zinc-600 dark:text-zinc-400">
                                <td class="px-3 py-1.5 text-center sticky left-0 z-10 bg-zinc-100 dark:bg-zinc-800 border-r border-zinc-300 dark:border-zinc-700">
                                    KALI
                                </td>
                                @php $grandCount = 0; @endphp
                                @foreach ($items as $item)
                                    @php
                                        $cCount = match ($activeTab) {
                                            'purchase' => $totals['count_purchase'][$item->id] ?? 0,
                                            'usage'    => $totals['count_usage'][$item->id] ?? 0,
                                            'sale'     => $totals['count_sale'][$item->id] ?? 0,
                                            default    => 0,
                                        };
                                        $grandCount += $cCount;
                                    @endphp
                                    <td class="px-3 py-1.5 text-center border-r border-zinc-200 dark:border-white/10">
                                        {{ $cCount }}x
                                    </td>
                                @endforeach
                                <td class="px-3 py-1.5 text-center">
                                    {{ $grandCount }}x
                                </td>
                            </tr>
                        @endif
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
</div>
