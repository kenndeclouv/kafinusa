<div class="space-y-6 pb-28 sm:pb-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                {{-- <flux:badge color="amber" size="sm" icon="fire">Produksi Petis</flux:badge> --}}
                <flux:heading size="xl" class="font-bold">Sirkulasi Bahan Baku Petis</flux:heading>
            </div>
            <flux:subheading size="sm" class="mt-1">
                Catat pemakaian produksi harian, pembelian masuk, penjualan, dan opname stok 9 bahan baku petis.
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Auto-save status indicator --}}
            {{-- <div class="flex items-center px-3 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-white/10 shadow-2xs">
                <div wire:loading class="flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400 font-medium">
                    <flux:icon name="arrow-path" class="w-3.5 h-3.5 animate-spin" />
                    <span>Menyimpan otomatis...</span>
                </div>
                <div wire:loading.remove class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                    <flux:icon name="check-circle" class="w-3.5 h-3.5" />
                    <span>Tersimpan otomatis</span>
                </div>
            </div> --}}

            {{-- <flux:button variant="ghost" icon="printer"
                :href="route('petis.raw-materials.print', ['warehouse' => $warehouse_id, 'month' => $month])"
                target="_blank">
                Cetak Laporan
            </flux:button> --}}
        </div>
    </div>

    {{-- Mode Selector Tabs: Input Harian vs Tabel 1 Bulan --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <x-tabs :active="$viewMode" @tab-change="$wire.set('viewMode', $event.detail)">
            <button type="button" class="x-tabs-btn" data-tab="daily" @click="select('daily', $el)">
                <div class="flex items-center gap-2">
                    <flux:icon.calendar-days class="w-4 h-4 text-amber-500" />
                    <span class="font-semibold">Input Harian</span>
                </div>
            </button>
            <button type="button" class="x-tabs-btn" data-tab="monthly" @click="select('monthly', $el)">
                <div class="flex items-center gap-2">
                    <flux:icon.table-cells class="w-4 h-4 text-blue-500" />
                    <span class="font-semibold">Tabel 1 Bulan (Excel)</span>
                </div>
            </button>
        </x-tabs>
    </div>

    @php
        $m = $monthlyData;
        $items = $rawItems;
        $days = $m['days'] ?? [];
        $initialStocks = $m['initial_stocks'] ?? [];
        $finalStocks = $m['final_stocks'] ?? [];
        $totals = $m['totals'] ?? [];
    @endphp

    {{-- ============================================================== --}}
    {{-- MODE 1: WORKSTATION INPUT HARIAN                               --}}
    {{-- ============================================================== --}}
    @if ($viewMode === 'daily')
        @php
            $isToday = $selectedDate === \Carbon\Carbon::today()->format('Y-m-d');
            $isSunday = \Carbon\Carbon::parse($selectedDate)->isSunday();
            $dayData = $days[$selectedDay] ?? null;
        @endphp

        {{-- Date Navigator Bar --}}
        <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-white/10 p-3.5 sm:p-4 shadow-xs space-y-3 sm:space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="button" wire:click="prevDay" title="Hari Sebelumnya"
                        class="p-2.5 rounded-xl border border-zinc-200 dark:border-white/10 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors text-zinc-600 dark:text-zinc-300 active:scale-95 shadow-2xs cursor-pointer shrink-0">
                        <flux:icon.chevron-left class="w-4 h-4" />
                    </button>

                    <div class="flex-1 sm:w-60 sm:flex-initial min-w-0">
                        <x-date-picker wire:model.live="selectedDate" type="date" variant="ios" :clearable="false" />
                    </div>

                    <button type="button" wire:click="nextDay" title="Hari Berikutnya"
                        class="p-2.5 rounded-xl border border-zinc-200 dark:border-white/10 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors text-zinc-600 dark:text-zinc-300 active:scale-95 shadow-2xs cursor-pointer shrink-0">
                        <flux:icon.chevron-right class="w-4 h-4" />
                    </button>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-2 text-xs text-zinc-500 pt-2 border-t border-zinc-100 dark:border-white/5 sm:border-t-0 sm:pt-0">
                    <div class="flex items-center gap-1.5">
                        @if ($isSunday)
                            <flux:badge color="rose" size="sm">Minggu</flux:badge>
                        @endif
                        @if ($isToday)
                            <flux:badge color="amber" size="sm">Hari Ini</flux:badge>
                        @else
                            <flux:button size="sm" variant="subtle" wire:click="setToday" class="text-xs">
                                Ke Hari Ini
                            </flux:button>
                        @endif
                    </div>

                    <div class="flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300 font-medium">
                        <flux:icon.clock class="w-3.5 h-3.5 text-zinc-400" />
                        <span>{{ $selectedDateFormatted }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Banner Sesi Masak Hari Ini (Koneksi Dapur & Bahan Baku) --}}
        @if ($todayCookingSessions > 0)
            <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-300 dark:border-amber-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3 text-xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold shrink-0">
                        <flux:icon.fire class="w-4 h-4" />
                    </div>
                    <div>
                        <span class="font-bold text-amber-950 dark:text-amber-200">Aktivitas Dapur Masak Hari Ini:</span>
                        <span class="text-amber-900 dark:text-amber-300 ml-1">Tercatat <strong>{{ $todayCookingSessions }} Sesi Masakan</strong> petis pada tanggal ini.</span>
                    </div>
                </div>
                <a href="{{ route('petis.cookings.index') }}" wire:navigate class="text-xs font-semibold text-amber-700 dark:text-amber-300 hover:underline flex items-center gap-1 shrink-0 self-end sm:self-auto">
                    Lihat Masakan <flux:icon.arrow-right class="w-3.5 h-3.5" />
                </a>
            </div>
        @endif

        {{-- Sub-Tab Aksi Harian (Segmented Control Swipeable) --}}
        <div class="overflow-x-auto max-w-full pb-1 -mx-1 px-1 no-scrollbar">
            <x-tabs :active="$dailyTab" @tab-change="$wire.set('dailyTab', $event.detail)" class="min-w-max">
                <button type="button" class="x-tabs-btn" data-tab="usage" @click="select('usage', $el)">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm">
                        <flux:icon.fire class="w-3.5 h-3.5 text-purple-500" />
                        <span class="sm:hidden">Pemakaian</span>
                        <span class="hidden sm:inline">Pemakaian Masak</span>
                    </div>
                </button>
                <button type="button" class="x-tabs-btn" data-tab="purchase" @click="select('purchase', $el)">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm">
                        <flux:icon.truck class="w-3.5 h-3.5 text-emerald-500" />
                        <span class="sm:hidden">Pembelian</span>
                        <span class="hidden sm:inline">Pembelian Masuk</span>
                    </div>
                </button>
                <button type="button" class="x-tabs-btn" data-tab="sale" @click="select('sale', $el)">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm">
                        <flux:icon.arrow-up-right class="w-3.5 h-3.5 text-blue-500" />
                        <span class="sm:hidden">Penjualan</span>
                        <span class="hidden sm:inline">Penjualan Keluar</span>
                    </div>
                </button>
                <button type="button" class="x-tabs-btn" data-tab="opname" @click="select('opname', $el)">
                    <div class="flex items-center gap-1.5 text-xs sm:text-sm">
                        <flux:icon.clipboard-document-check class="w-3.5 h-3.5 text-amber-500" />
                        <span class="sm:hidden">Opname & Selisih</span>
                        <span class="hidden sm:inline">Opname Fisik & Selisih</span>
                    </div>
                </button>
            </x-tabs>
        </div>

        {{-- Grid Kartu 9 Bahan Baku --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($items as $item)
                @php
                    $itemId = $item->id;
                    $currentStock = (float)($currentWarehouseStocks[$itemId] ?? 0);
                    $bookStock = (float)($dayData['book_stock'][$itemId] ?? 0);
                    $opVal = $grid['opname'][$selectedDay][$itemId] ?? '';
                    $diff = ($opVal !== '' && $opVal !== null) ? ($bookStock - (float)$opVal) : null;
                @endphp
                <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-white/10 p-4 shadow-xs space-y-3 transition-all hover:border-zinc-300 dark:hover:border-zinc-700">
                    {{-- Header Bahan --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 flex items-center justify-center font-bold text-xs shadow-2xs border border-zinc-200/50 dark:border-white/5 shrink-0">
                                {{ strtoupper(substr($item->name, 0, 2)) }}
                            </div>
                            <div class="min-w-0">
                                <span class="font-bold text-sm text-zinc-900 dark:text-white block leading-tight truncate">{{ $item->name }}</span>
                                <span class="text-[11px] text-zinc-400 font-mono truncate block">Kode: {{ $item->code ?? '-' }} &bull; Satuan: {{ $item->unit }}</span>
                            </div>
                        </div>

                        {{-- Badge Stok --}}
                        <div class="text-right shrink-0">
                            <span class="text-[10px] text-zinc-400 uppercase tracking-wider block">S. Awal: {{ number_format($initialStocks[$itemId] ?? 0, 1, ',', '.') }} {{ $item->unit }}</span>
                            <span class="text-xs font-mono font-bold text-zinc-800 dark:text-zinc-200">
                                Gudang: {{ number_format($currentStock, 1, ',', '.') }} {{ $item->unit }}
                            </span>
                        </div>
                    </div>

                    {{-- Input Sesuai Tab Aksi Harian --}}
                    @if ($dailyTab === 'usage')
                        <div class="p-3 rounded-2xl bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200/70 dark:border-purple-900/40 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-purple-950 dark:text-purple-300">Pemakaian Masak</span>
                                <span class="text-[11px] text-zinc-400">Satuan: {{ $item->unit }}</span>
                            </div>
                            <div class="bg-white dark:bg-zinc-800/90 rounded-xl px-2 py-0.5 border border-purple-200/80 dark:border-purple-900/50 shadow-2xs">
                                <x-stepper
                                    step="1"
                                    min="0"
                                    placeholder="0"
                                    variant="ios"
                                    wire:key="daily-raw-use-{{ $selectedDay }}-{{ $itemId }}"
                                    wire:model.live.debounce.500ms="grid.usage.{{ $selectedDay }}.{{ $itemId }}"
                                />
                            </div>
                        </div>

                    @elseif ($dailyTab === 'purchase')
                        <div class="p-3 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/70 dark:border-emerald-900/40 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-emerald-950 dark:text-emerald-300">Pembelian / Kiriman Masuk</span>
                                <span class="text-[11px] text-zinc-400">Satuan: {{ $item->unit }}</span>
                            </div>
                            <div class="bg-white dark:bg-zinc-800/90 rounded-xl px-2 py-0.5 border border-emerald-200/80 dark:border-emerald-900/50 shadow-2xs">
                                <x-stepper
                                    step="1"
                                    min="0"
                                    placeholder="0"
                                    variant="ios"
                                    wire:key="daily-raw-pur-{{ $selectedDay }}-{{ $itemId }}"
                                    wire:model.live.debounce.500ms="grid.purchase.{{ $selectedDay }}.{{ $itemId }}"
                                />
                            </div>
                        </div>

                    @elseif ($dailyTab === 'sale')
                        <div class="p-3 rounded-2xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/70 dark:border-blue-900/40 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-blue-950 dark:text-blue-300">Penjualan Bahan Keluar</span>
                                <span class="text-[11px] text-zinc-400">Satuan: {{ $item->unit }}</span>
                            </div>
                            <div class="bg-white dark:bg-zinc-800/90 rounded-xl px-2 py-0.5 border border-blue-200/80 dark:border-blue-900/50 shadow-2xs">
                                <x-stepper
                                    step="1"
                                    min="0"
                                    placeholder="0"
                                    variant="ios"
                                    wire:key="daily-raw-sal-{{ $selectedDay }}-{{ $itemId }}"
                                    wire:model.live.debounce.500ms="grid.sale.{{ $selectedDay }}.{{ $itemId }}"
                                />
                            </div>
                        </div>

                    @elseif ($dailyTab === 'opname')
                        <div class="p-3 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/70 dark:border-amber-900/40 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-amber-950 dark:text-amber-300">Stok Opname (Fisik)</span>
                                <span class="text-[11px] text-zinc-500 font-mono">
                                    Stok Buku: <strong>{{ number_format($bookStock, 1, ',', '.') }}</strong>
                                </span>
                            </div>
                            <div class="bg-white dark:bg-zinc-800/90 rounded-xl px-2 py-0.5 border border-amber-200/80 dark:border-amber-900/50 shadow-2xs">
                                <x-stepper
                                    step="1"
                                    min="0"
                                    placeholder="Belum opname"
                                    variant="ios"
                                    wire:key="daily-raw-op-{{ $selectedDay }}-{{ $itemId }}"
                                    wire:model.live.debounce.500ms="grid.opname.{{ $selectedDay }}.{{ $itemId }}"
                                />
                            </div>
                            @if ($diff !== null)
                                <div class="flex items-center justify-between text-xs pt-1 border-t border-amber-200/60 dark:border-amber-900/30">
                                    <span class="text-zinc-500 text-[11px]">Selisih (Buku - Fisik):</span>
                                    <span @class([
                                        'font-mono font-bold text-xs',
                                        'text-emerald-600 dark:text-emerald-400' => $diff == 0,
                                        'text-rose-600 dark:text-rose-400' => $diff > 0,
                                        'text-blue-600 dark:text-blue-400' => $diff < 0,
                                    ])>
                                        {{ $diff > 0 ? "Kurang {$diff}" : ($diff < 0 ? "Surplus +" . abs($diff) : '0') }} {{ $item->unit }}
                                        ({{ $diff == 0 ? 'Sesuai' : ($diff > 0 ? 'Fisik Kurang' : 'Surplus Fisik') }})
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

    {{-- ============================================================== --}}
    {{-- MODE 2: TABEL 1 BULAN PENUH (MATRIKS SPREADSHEET EXCEL)        --}}
    {{-- ============================================================== --}}
    @else
        {{-- Filter Toolbar Periode Bulan --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-zinc-50 dark:bg-zinc-900/60 p-3.5 rounded-2xl border border-zinc-200 dark:border-white/10">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider shrink-0">Periode Bulan:</span>
                <flux:input type="month" wire:model.live="month" size="sm" class="w-40" />
            </div>

            <div class="flex items-center gap-2 text-[11px] sm:text-xs text-zinc-500">
                <flux:icon.check-circle class="w-4 h-4 text-emerald-500 shrink-0" />
                <span>Klik angka tanggal untuk buka harian, atau edit langsung pada spreadsheet.</span>
            </div>
        </div>

        {{-- Sub Tabs: Filter Matrix Spreadsheet --}}
        <div class="overflow-x-auto max-w-full pb-1 -mx-1 px-1 no-scrollbar">
            <x-tabs :active="$activeTab" @tab-change="$wire.set('activeTab', $event.detail)" class="min-w-max">
                <button type="button" class="x-tabs-btn" data-tab="rekap" @click="select('rekap', $el)">Semua Matrix</button>
                <button type="button" class="x-tabs-btn" data-tab="purchase" @click="select('purchase', $el)">Pembelian</button>
                <button type="button" class="x-tabs-btn" data-tab="usage" @click="select('usage', $el)">Pemakaian</button>
                <button type="button" class="x-tabs-btn" data-tab="sale" @click="select('sale', $el)">Penjualan</button>
                <button type="button" class="x-tabs-btn" data-tab="stock" @click="select('stock', $el)">Stok Buku</button>
                <button type="button" class="x-tabs-btn" data-tab="selisih" @click="select('selisih', $el)">Opname & Selisih</button>
            </x-tabs>
        </div>

        {{-- Main Spreadsheet Table --}}
        <div class="space-y-2">
            <div class="flex items-center gap-1.5 text-[11px] text-zinc-500 bg-zinc-100/80 dark:bg-zinc-800/80 px-3 py-1.5 rounded-xl sm:hidden">
                <flux:icon.arrows-right-left class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                <span>Geser tabel ke samping untuk melihat seluruh bahan baku</span>
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-white/10 overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                <table class="w-full text-xs text-left whitespace-nowrap">
                    <thead class="text-[11px] text-zinc-700 dark:text-zinc-300 uppercase bg-zinc-100/80 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-white/10">
                        @if ($activeTab === 'rekap')
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
                                @foreach ($items as $item)
                                    <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-emerald-50/30 dark:bg-emerald-950/10">{{ $item->name }}</th>
                                @endforeach
                                @foreach ($items as $item)
                                    <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-purple-50/30 dark:bg-purple-950/10">{{ $item->name }}</th>
                                @endforeach
                                @foreach ($items as $item)
                                    <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-blue-50/30 dark:bg-blue-950/10">{{ $item->name }}</th>
                                @endforeach
                                @foreach ($items as $item)
                                    <th class="px-2 py-1.5 font-bold border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-teal-50/40 dark:bg-teal-950/20 text-teal-800 dark:text-teal-300">{{ $item->name }}</th>
                                @endforeach
                                @foreach ($items as $item)
                                    <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-amber-50/30 dark:bg-amber-950/10">{{ $item->name }}</th>
                                @endforeach
                                @foreach ($items as $item)
                                    <th class="px-2 py-1.5 font-bold text-center min-w-[55px] bg-rose-50/30 dark:bg-rose-950/10 {{ !$loop->last ? 'border-r border-zinc-200 dark:border-white/10' : '' }}">{{ $item->name }}</th>
                                @endforeach
                            </tr>
                        @else
                            <tr>
                                <th class="px-3 py-3 font-bold border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-20 bg-zinc-100 dark:bg-zinc-800 w-14">
                                    TGL
                                </th>
                                @foreach ($items as $item)
                                    <th class="px-2 py-2 font-bold border-r border-zinc-200 dark:border-white/10 text-center min-w-[70px]">
                                        {{ $item->name }}
                                        <div class="text-[9px] font-normal text-zinc-400 lowercase">({{ $item->unit }})</div>
                                    </th>
                                @endforeach
                            </tr>
                        @endif
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-white/10 font-mono text-[11px]">
                        {{-- Baris S AWAL (Stok Awal Bulan) --}}
                        <tr class="bg-zinc-100/90 dark:bg-zinc-800/90 font-bold">
                            <td class="px-3 py-2 text-center sticky left-0 z-10 bg-zinc-100 dark:bg-zinc-800 border-r border-zinc-200 dark:border-white/10 text-zinc-700 dark:text-zinc-200">
                                S AWAL
                            </td>
                            @if ($activeTab === 'rekap')
                                {{-- 1. Pembelian (Dash) --}}
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400 bg-emerald-50/10">-</td>
                                @endforeach
                                {{-- 2. Pemakaian (Dash) --}}
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400 bg-purple-50/10">-</td>
                                @endforeach
                                {{-- 3. Penjualan (Dash) --}}
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400 bg-blue-50/10">-</td>
                                @endforeach
                                {{-- 4. STOK BUKU (S AWAL Input, Sesuai Baris 16 S AWAL di Excel Sheet BAHAN BAKU) --}}
                                @foreach ($items as $item)
                                    <td class="p-0.5 border-r border-zinc-200 dark:border-white/10 bg-teal-50/50 dark:bg-teal-950/30">
                                        <input type="number" step="0.5" min="0"
                                            wire:key="init-stock-rekap-{{ $item->id }}"
                                            wire:model.live.debounce.500ms="initial_stocks.{{ $item->id }}"
                                            placeholder="0"
                                            title="Saldo Awal Buku: {{ $item->name }}"
                                            class="w-full text-center bg-transparent border-transparent hover:border-teal-400 dark:hover:border-teal-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-bold text-teal-800 dark:text-teal-300" />
                                    </td>
                                @endforeach
                                {{-- 5. STOK OPNAME (Dash, Tidak ada S AWAL untuk opname di Excel) --}}
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400 bg-amber-50/10">-</td>
                                @endforeach
                                {{-- 6. SELISIH STOK (Dash) --}}
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center text-zinc-400 bg-rose-50/10 {{ !$loop->last ? 'border-r border-zinc-200 dark:border-white/10' : '' }}">-</td>
                                @endforeach
                            @elseif ($activeTab === 'stock')
                                {{-- Tab Stok Buku: Baris S AWAL berisi input saldo awal --}}
                                @foreach ($items as $item)
                                    <td class="p-0.5 border-r border-zinc-200 dark:border-white/10 bg-teal-50/50 dark:bg-teal-950/30">
                                        <input type="number" step="0.5" min="0"
                                            wire:key="init-stock-tab-{{ $item->id }}"
                                            wire:model.live.debounce.500ms="initial_stocks.{{ $item->id }}"
                                            placeholder="0"
                                            title="Saldo Awal Buku: {{ $item->name }}"
                                            class="w-full text-center bg-transparent border-transparent hover:border-teal-400 dark:hover:border-teal-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-bold text-teal-800 dark:text-teal-300" />
                                    </td>
                                @endforeach
                            @else
                                {{-- Tab Lainnya (Pembelian, Pemakaian, Penjualan, Opname & Selisih): S AWAL berupa strip --}}
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                                @endforeach
                            @endif
                        </tr>

                        {{-- Baris Tanggal 1 .. 31 --}}
                        @foreach ($days as $day => $dData)
                            @php
                                $rowBg = $dData['is_sunday'] ? 'bg-rose-50/30 dark:bg-rose-950/10' : 'hover:bg-zinc-50 dark:hover:bg-white/[0.02]';
                            @endphp
                            <tr class="{{ $rowBg }} transition-colors">
                                <td class="px-3 py-1 text-center sticky left-0 z-10 bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-white/10 font-medium">
                                    <button type="button" wire:click="selectDay({{ $day }})" title="Buka Input Harian Tanggal {{ $day }}"
                                        class="cursor-pointer {{ $dData['is_sunday'] ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-zinc-700 dark:text-zinc-300' }} hover:underline hover:text-amber-600 font-bold">
                                        {{ $day }}
                                    </button>
                                </td>

                                @if ($activeTab === 'rekap')
                                    {{-- Pembelian --}}
                                    @foreach ($items as $item)
                                        <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                            <input type="number" step="0.5" min="0"
                                                wire:key="cell-pur-{{ $day }}-{{ $item->id }}"
                                                wire:model.live.debounce.500ms="grid.purchase.{{ $day }}.{{ $item->id }}"
                                                class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-emerald-800 dark:text-emerald-300 placeholder-transparent"
                                                placeholder="" />
                                        </td>
                                    @endforeach

                                    {{-- Pemakaian --}}
                                    @foreach ($items as $item)
                                        <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                            <input type="number" step="0.5" min="0"
                                                wire:key="cell-use-{{ $day }}-{{ $item->id }}"
                                                wire:model.live.debounce.500ms="grid.usage.{{ $day }}.{{ $item->id }}"
                                                class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-purple-800 dark:text-purple-300 placeholder-transparent"
                                                placeholder="" />
                                        </td>
                                    @endforeach

                                    {{-- Penjualan --}}
                                    @foreach ($items as $item)
                                        <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                            <input type="number" step="0.5" min="0"
                                                wire:key="cell-sal-{{ $day }}-{{ $item->id }}"
                                                wire:model.live.debounce.500ms="grid.sale.{{ $day }}.{{ $item->id }}"
                                                class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-blue-800 dark:text-blue-300 placeholder-transparent"
                                                placeholder="" />
                                        </td>
                                    @endforeach

                                    {{-- Stok Buku --}}
                                    @foreach ($items as $item)
                                        <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-teal-800 dark:text-teal-300 bg-teal-50/20 font-medium">
                                            {{ ($dData['book_stock'][$item->id] ?? 0) > 0 ? number_format($dData['book_stock'][$item->id], 1, ',', '.') : '' }}
                                        </td>
                                    @endforeach

                                    {{-- Opname --}}
                                    @foreach ($items as $item)
                                        <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                            <input type="number" step="0.5" min="0"
                                                wire:key="cell-op-{{ $day }}-{{ $item->id }}"
                                                wire:model.live.debounce.500ms="grid.opname.{{ $day }}.{{ $item->id }}"
                                                class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-amber-800 dark:text-amber-300 placeholder-transparent"
                                                placeholder="" />
                                        </td>
                                    @endforeach

                                    {{-- Selisih --}}
                                    @foreach ($items as $item)
                                        @php $diff = $dData['diff'][$item->id]; @endphp
                                        <td class="px-2 py-1 text-center {{ !$loop->last ? 'border-r border-zinc-200 dark:border-white/10' : '' }} font-bold {{ $diff === null ? 'text-zinc-300' : ($diff == 0 ? 'text-emerald-600 dark:text-emerald-400' : ($diff > 0 ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400' : 'bg-blue-500/15 text-blue-600 dark:text-blue-400')) }}">
                                            {{ $diff !== null ? ($diff == 0 ? '0' : number_format($diff, 1, ',', '.')) : '-' }}
                                        </td>
                                    @endforeach

                                @elseif ($activeTab === 'purchase')
                                    @foreach ($items as $item)
                                        <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                            <input type="number" step="0.5" min="0"
                                                wire:key="tab-pur-{{ $day }}-{{ $item->id }}"
                                                wire:model.live.debounce.500ms="grid.purchase.{{ $day }}.{{ $item->id }}"
                                                class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-emerald-800 dark:text-emerald-300 placeholder-transparent"
                                                placeholder="" />
                                        </td>
                                    @endforeach

                                @elseif ($activeTab === 'usage')
                                    @foreach ($items as $item)
                                        <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                            <input type="number" step="0.5" min="0"
                                                wire:key="tab-use-{{ $day }}-{{ $item->id }}"
                                                wire:model.live.debounce.500ms="grid.usage.{{ $day }}.{{ $item->id }}"
                                                class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-purple-800 dark:text-purple-300 placeholder-transparent"
                                                placeholder="" />
                                        </td>
                                    @endforeach

                                @elseif ($activeTab === 'sale')
                                    @foreach ($items as $item)
                                        <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                            <input type="number" step="0.5" min="0"
                                                wire:key="tab-sal-{{ $day }}-{{ $item->id }}"
                                                wire:model.live.debounce.500ms="grid.sale.{{ $day }}.{{ $item->id }}"
                                                class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-blue-800 dark:text-blue-300 placeholder-transparent"
                                                placeholder="" />
                                        </td>
                                    @endforeach

                                @elseif ($activeTab === 'stock')
                                    @foreach ($items as $item)
                                        <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-teal-800 dark:text-teal-300 bg-teal-50/20 font-medium">
                                            {{ ($dData['book_stock'][$item->id] ?? 0) > 0 ? number_format($dData['book_stock'][$item->id], 1, ',', '.') : '' }}
                                        </td>
                                    @endforeach

                                @elseif ($activeTab === 'selisih')
                                    @foreach ($items as $item)
                                        @php $diff = $dData['diff'][$item->id]; @endphp
                                        <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 font-bold {{ $diff === null ? 'text-zinc-300' : ($diff == 0 ? 'text-emerald-600 dark:text-emerald-400' : ($diff > 0 ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400' : 'bg-blue-500/15 text-blue-600 dark:text-blue-400')) }}">
                                            {{ $diff !== null ? ($diff == 0 ? '0' : number_format($diff, 1, ',', '.')) : '-' }}
                                        </td>
                                    @endforeach
                                @endif
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Footer TOTAL, FREKUENSI, & S AKHIR (Persis Baris 12, 13, dan 25 di Excel) --}}
                    <tfoot class="bg-zinc-100 dark:bg-zinc-800 text-[11px] font-bold font-mono border-t-2 border-zinc-300 dark:border-zinc-700 divide-y divide-zinc-200 dark:divide-white/10">
                        {{-- 1. Baris TOTAL --}}
                        <tr class="bg-zinc-200/80 dark:bg-zinc-800">
                            <td class="px-3 py-1.5 text-center sticky left-0 z-10 bg-zinc-200 dark:bg-zinc-800 border-r border-zinc-300 dark:border-zinc-700 text-zinc-800 dark:text-zinc-200 font-extrabold">
                                TOTAL
                            </td>
                            @if ($activeTab === 'rekap')
                                @foreach ($items as $item)
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-emerald-800 dark:text-emerald-400 bg-emerald-50/40">
                                        {{ ($totals['purchase'][$item->id] ?? 0) > 0 ? number_format($totals['purchase'][$item->id], 1, ',', '.') : '-' }}
                                    </td>
                                @endforeach
                                @foreach ($items as $item)
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-purple-800 dark:text-purple-400 bg-purple-50/40">
                                        {{ ($totals['usage'][$item->id] ?? 0) > 0 ? number_format($totals['usage'][$item->id], 1, ',', '.') : '-' }}
                                    </td>
                                @endforeach
                                @foreach ($items as $item)
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-blue-800 dark:text-blue-400 bg-blue-50/40">
                                        {{ ($totals['sale'][$item->id] ?? 0) > 0 ? number_format($totals['sale'][$item->id], 1, ',', '.') : '-' }}
                                    </td>
                                @endforeach
                                @foreach ($items as $item)
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400 bg-teal-50/20">-</td>
                                @endforeach
                                @foreach ($items as $item)
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400 bg-amber-50/20">-</td>
                                @endforeach
                                @foreach ($items as $item)
                                    <td class="px-2 py-1.5 text-center text-zinc-400 bg-rose-50/20 {{ !$loop->last ? 'border-r border-zinc-200 dark:border-white/10' : '' }}">-</td>
                                @endforeach
                            @elseif ($activeTab === 'purchase')
                                @foreach ($items as $item)
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-emerald-800 dark:text-emerald-400 bg-emerald-50/40">
                                        {{ ($totals['purchase'][$item->id] ?? 0) > 0 ? number_format($totals['purchase'][$item->id], 1, ',', '.') : '-' }}
                                    </td>
                                @endforeach
                            @elseif ($activeTab === 'usage')
                                @foreach ($items as $item)
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-purple-800 dark:text-purple-400 bg-purple-50/40">
                                        {{ ($totals['usage'][$item->id] ?? 0) > 0 ? number_format($totals['usage'][$item->id], 1, ',', '.') : '-' }}
                                    </td>
                                @endforeach
                            @elseif ($activeTab === 'sale')
                                @foreach ($items as $item)
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-blue-800 dark:text-blue-400 bg-blue-50/40">
                                        {{ ($totals['sale'][$item->id] ?? 0) > 0 ? number_format($totals['sale'][$item->id], 1, ',', '.') : '-' }}
                                    </td>
                                @endforeach
                            @elseif ($activeTab === 'stock')
                                @foreach ($items as $item)
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                                @endforeach
                            @else
                                @foreach ($items as $item)
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                                @endforeach
                            @endif
                        </tr>

                        {{-- 2. Baris FREKUENSI (Jumlah Hari Transaksi / Kali) --}}
                        <tr class="bg-zinc-100 dark:bg-zinc-800/80 text-[10px]">
                            <td class="px-3 py-1 text-center sticky left-0 z-10 bg-zinc-100 dark:bg-zinc-800 border-r border-zinc-300 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 font-semibold">
                                FREKUENSI
                            </td>
                            @if ($activeTab === 'rekap')
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-emerald-700 dark:text-emerald-400 font-normal">
                                        {{ $totals['count_purchase'][$item->id] ?? 0 }} Kali
                                    </td>
                                @endforeach
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-purple-700 dark:text-purple-400 font-normal">
                                        {{ $totals['count_usage'][$item->id] ?? 0 }} Kali
                                    </td>
                                @endforeach
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-blue-700 dark:text-blue-400 font-normal">
                                        {{ $totals['count_sale'][$item->id] ?? 0 }} Kali
                                    </td>
                                @endforeach
                                @for ($s = 0; $s < 3; $s++)
                                    @foreach ($items as $item)
                                        <td class="px-2 py-1 text-center text-zinc-400 {{ ($s === 2 && $loop->last) ? '' : 'border-r border-zinc-200 dark:border-white/10' }}">-</td>
                                    @endforeach
                                @endfor
                            @elseif ($activeTab === 'purchase')
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-emerald-700 dark:text-emerald-400 font-normal">
                                        {{ $totals['count_purchase'][$item->id] ?? 0 }} Kali
                                    </td>
                                @endforeach
                            @elseif ($activeTab === 'usage')
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-purple-700 dark:text-purple-400 font-normal">
                                        {{ $totals['count_usage'][$item->id] ?? 0 }} Kali
                                    </td>
                                @endforeach
                            @elseif ($activeTab === 'sale')
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-blue-700 dark:text-blue-400 font-normal">
                                        {{ $totals['count_sale'][$item->id] ?? 0 }} Kali
                                    </td>
                                @endforeach
                            @else
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                                @endforeach
                            @endif
                        </tr>

                        {{-- 3. Baris S AKHIR (Saldo Akhir Stok Buku) --}}
                        <tr class="bg-teal-50/70 dark:bg-teal-950/40 text-[11px] font-bold">
                            <td class="px-3 py-2 text-center sticky left-0 z-10 bg-teal-100 dark:bg-teal-900 border-r border-zinc-300 dark:border-zinc-700 text-teal-950 dark:text-teal-200 font-extrabold">
                                S AKHIR
                            </td>
                            @if ($activeTab === 'rekap')
                                @for ($s = 0; $s < 3; $s++)
                                    @foreach ($items as $item)
                                        <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                                    @endforeach
                                @endfor
                                {{-- STOK BUKU AKHIR --}}
                                @foreach ($items as $item)
                                    <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-teal-950 dark:text-teal-200 bg-teal-200/60 dark:bg-teal-900/60 font-black text-xs">
                                        {{ ($finalStocks[$item->id] ?? 0) > 0 ? number_format($finalStocks[$item->id], 1, ',', '.') : '0' }}
                                    </td>
                                @endforeach
                                @for ($s = 0; $s < 2; $s++)
                                    @foreach ($items as $item)
                                        <td class="px-2 py-1 text-center text-zinc-400 {{ ($s === 1 && $loop->last) ? '' : 'border-r border-zinc-200 dark:border-white/10' }}">-</td>
                                    @endforeach
                                @endfor
                            @elseif ($activeTab === 'stock')
                                @foreach ($items as $item)
                                    <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-teal-950 dark:text-teal-200 bg-teal-200/60 dark:bg-teal-900/60 font-black text-xs">
                                        {{ ($finalStocks[$item->id] ?? 0) > 0 ? number_format($finalStocks[$item->id], 1, ',', '.') : '0' }}
                                    </td>
                                @endforeach
                            @else
                                @foreach ($items as $item)
                                    <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                                @endforeach
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        </div>
    @endif
</div>
