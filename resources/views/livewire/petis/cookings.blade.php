<div class="space-y-6 pb-28 sm:pb-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                {{-- <flux:badge color="amber" size="sm" icon="fire">Produksi Petis</flux:badge> --}}
                <flux:heading size="xl" class="font-bold">Proses Masakan Petis</flux:heading>
            </div>
            <flux:subheading size="sm" class="mt-1">
                Catat sesi juru masak, hasil produksi kemasan, dan opname stok gudang petis secara real-time.
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <div wire:loading wire:target="grid,saveDay,adjustCookerSession,adjustResult,copyYesterdayCookers" class="flex items-center gap-2 text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-3 py-1.5 rounded-full border border-amber-200 dark:border-amber-800">
                <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span>Menyimpan otomatis...</span>
            </div>

            {{-- <flux:button variant="ghost" icon="printer"
                :href="route('petis.cookings.print', ['warehouse' => $warehouse_id, 'month' => $month])"
                target="_blank">
                Cetak Laporan
            </flux:button> --}}

            {{-- @can('warehouses:read')
                <flux:button variant="subtle" icon="users" :href="route('teams.index')" wire:navigate>
                    Master Tim & Koki
                </flux:button>
            @endcan --}}
        </div>
    </div>

    {{-- Segmented View Mode Tabs & Lokasi --}}
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

        {{-- <div class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-900/60 px-3 py-1.5 rounded-full border border-zinc-200 dark:border-white/10 w-fit">
            <flux:icon.building-office-2 class="w-4 h-4 text-amber-500" />
            <span>Lokasi: <strong class="text-zinc-800 dark:text-zinc-200">Gudang Petis (Sentral)</strong></span>
        </div> --}}
    </div>

    @php
        // Kalkulasi ringkasan bulanan dari state $grid
        $grandTotalSessions = 0;
        $grandTotalKaleng = 0;
        $grandTotalRefill = 0;
        $grandTotalSisaKg = 0;
        $grandTotalKg = 0;
        $totalDaysCooking = 0;

        $totalCookerSessions = array_fill_keys($cookers->pluck('name')->toArray(), 0);
        $totalCookerDays = array_fill_keys($cookers->pluck('name')->toArray(), 0);

        foreach ($grid as $day => $row) {
            $daySessions = 0;
            foreach ($cookers as $c) {
                $s = (float)($row['sessions'][$c->name] ?? 0);
                $totalCookerSessions[$c->name] += $s;
                if ($s > 0) $totalCookerDays[$c->name]++;
                $daySessions += $s;
            }

            $kl = (int)($row['result_kaleng'] ?: 0);
            $rf = (int)($row['result_refill'] ?: 0);
            $sisa = (float)($row['result_sisa_kg'] ?: 0);
            $kgKl = $kl * \App\Models\PetisProduction::WEIGHT_KALENG_KG;
            $kgRf = $rf * \App\Models\PetisProduction::WEIGHT_REFILL_KG;
            $dayKg = $kgKl + $kgRf + $sisa;

            if ($daySessions > 0 || $kl > 0 || $rf > 0) {
                $totalDaysCooking++;
            }

            $grandTotalSessions += $daySessions;
            $grandTotalKaleng += $kl;
            $grandTotalRefill += $rf;
            $grandTotalSisaKg += $sisa;
            $grandTotalKg += $dayKg;
        }

        $overallAvg = $grandTotalSessions > 0 ? round($grandTotalKg / $grandTotalSessions, 2) : 0;
    @endphp

    {{-- ============================================================== --}}
    {{-- MODE 1: WORKSTATION INPUT HARIAN                               --}}
    {{-- ============================================================== --}}
    @if ($viewMode === 'daily')
        @php
            $isToday = $selectedDate === \Carbon\Carbon::today()->format('Y-m-d');
            $isSunday = \Carbon\Carbon::parse($selectedDate)->isSunday();
        @endphp

        {{-- Date Navigator & Day Strip --}}
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

        {{-- 3 KARTU UTAMA: SESI KOKI, HASIL MASAKAN, OPNAME FISIK --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

            {{-- CARD 1: SESI JURU MASAK (Kiri / 7 Kolom) --}}
            <div class="lg:col-span-7 bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-white/10 p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-white/5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <flux:icon.fire class="w-4 h-4" />
                        </div>
                        <div class="min-w-0">
                            <flux:heading size="base" class="font-bold truncate">1. Sesi Juru Masak</flux:heading>
                            <flux:subheading size="xs" class="hidden sm:block">Atur sesi juru masak menggunakan stepper</flux:subheading>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" wire:click="copyYesterdayCookers" title="Salin daftar koki hari kemarin"
                            class="px-2.5 py-1 text-xs font-medium rounded-xl border border-zinc-200 dark:border-white/10 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-600 dark:text-zinc-300 flex items-center gap-1.5 transition-colors active:scale-95 cursor-pointer">
                            <flux:icon.document-duplicate class="w-3.5 h-3.5 text-zinc-400" />
                            <span class="hidden sm:inline">Salin Kemarin</span>
                        </button>

                        <flux:badge color="amber" size="sm" class="font-mono font-bold shrink-0">
                            Total: {{ $metrics['total_sessions'] }} Sesi
                        </flux:badge>
                    </div>
                </div>

                {{-- Grid Cookers --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ($cookers as $c)
                        @php
                            $sessVal = $grid[$selectedDay]['sessions'][$c->name] ?? '';
                            $isActive = (float) $sessVal > 0;
                        @endphp
                        <div @class([
                            'p-3.5 rounded-2xl border transition-all flex flex-col justify-between gap-3',
                            'bg-amber-50/60 dark:bg-amber-950/20 border-amber-300 dark:border-amber-500/40 ring-1 ring-amber-500/20 shadow-xs' => $isActive,
                            'bg-zinc-50/60 dark:bg-zinc-800/40 border-zinc-200 dark:border-white/5 hover:border-zinc-300 dark:hover:border-zinc-700' => !$isActive,
                        ])>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 truncate">
                                    <div @class([
                                        'w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0',
                                        'bg-amber-500 text-white shadow-2xs' => $isActive,
                                        'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300' => !$isActive,
                                    ])>
                                        {{ strtoupper(substr($c->name, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-xs text-zinc-900 dark:text-white truncate" title="{{ $c->name }}">
                                        {{ $c->name }}
                                    </span>
                                </div>

                                @if ($isActive)
                                    <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 font-mono shrink-0">
                                        {{ $sessVal }} Sesi
                                    </span>
                                @endif
                            </div>

                            {{-- Stepper Component --}}
                            <div class="bg-white dark:bg-zinc-800/90 rounded-xl px-2 py-0.5 border border-zinc-200/80 dark:border-white/10 shadow-2xs">
                                <x-stepper
                                    step="0.5"
                                    min="0"
                                    placeholder="0"
                                    variant="ios"
                                    wire:key="daily-sess-{{ $selectedDay }}-{{ $c->name }}"
                                    wire:model.live.debounce.500ms="grid.{{ $selectedDay }}.sessions.{{ $c->name }}"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- CARD 2 & 3: HASIL PRODUKSI & OPNAME FISIK (Kanan / 5 Kolom) --}}
            <div class="lg:col-span-5 space-y-5">

                {{-- CARD 2: HASIL MASAKAN --}}
                <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-white/10 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                <flux:icon.cube class="w-4 h-4" />
                            </div>
                            <div class="min-w-0">
                                <flux:heading size="base" class="font-bold truncate">2. Hasil Masakan Jadi</flux:heading>
                                <flux:subheading size="xs" class="hidden sm:block">Input kemasan jadi dan sisa wajan</flux:subheading>
                            </div>
                        </div>
                    </div>

                    {{-- Inputs Hasil --}}
                    <div class="space-y-3">
                        {{-- 1. Petis Kaleng (KI) --}}
                        <div class="p-3.5 rounded-2xl bg-blue-50/40 dark:bg-blue-950/20 border border-blue-200/80 dark:border-blue-900/40 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-blue-900 dark:text-blue-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    Petis Kaleng (KI)
                                </span>
                                <span class="font-mono text-blue-700 dark:text-blue-400 font-semibold">
                                    = {{ number_format($metrics['kg_kaleng'], 1, ',', '.') }} Kg
                                    <span class="text-[10px] text-zinc-400 font-normal">(@ 9.3 kg)</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-white dark:bg-zinc-800/90 rounded-xl px-2.5 py-0.5 border border-blue-200/80 dark:border-blue-900/50 shadow-2xs">
                                    <x-stepper
                                        step="1"
                                        min="0"
                                        placeholder="0"
                                        variant="ios"
                                        wire:key="daily-res-kl-{{ $selectedDay }}"
                                        wire:model.live.debounce.500ms="grid.{{ $selectedDay }}.result_kaleng"
                                    />
                                </div>
                                <button type="button" wire:click="adjustResult('result_kaleng', 5)" title="Tambah +5 Kaleng"
                                    class="px-3 h-10 rounded-xl bg-blue-500 hover:bg-blue-600 text-white flex items-center justify-center text-xs font-bold active:scale-95 shadow-2xs shadow-blue-500/20 cursor-pointer shrink-0">
                                    +5
                                </button>
                            </div>
                        </div>

                        {{-- 2. Petis Refill (Rf) --}}
                        <div class="p-3.5 rounded-2xl bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-900/40 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-emerald-900 dark:text-emerald-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Petis Refill (Rf) / Bal
                                </span>
                                <span class="font-mono text-emerald-700 dark:text-emerald-400 font-semibold">
                                    = {{ number_format($metrics['kg_refill'], 1, ',', '.') }} Kg
                                    <span class="text-[10px] text-zinc-400 font-normal">(@ 5.0 kg)</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-white dark:bg-zinc-800/90 rounded-xl px-2.5 py-0.5 border border-emerald-200/80 dark:border-emerald-900/50 shadow-2xs">
                                    <x-stepper
                                        step="1"
                                        min="0"
                                        placeholder="0"
                                        variant="ios"
                                        wire:key="daily-res-rf-{{ $selectedDay }}"
                                        wire:model.live.debounce.500ms="grid.{{ $selectedDay }}.result_refill"
                                    />
                                </div>
                                <button type="button" wire:click="adjustResult('result_refill', 5)" title="Tambah +5 Bal"
                                    class="px-3 h-10 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center text-xs font-bold active:scale-95 shadow-2xs shadow-emerald-500/20 cursor-pointer shrink-0">
                                    +5
                                </button>
                            </div>
                        </div>

                        {{-- 3. Sisa Wajan (Kg) --}}
                        <div class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3">
                            <div>
                                <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300 block">Sisa Adonan Wajan</span>
                                <span class="text-[11px] text-zinc-400">Di dasar wajan (Kg)</span>
                            </div>
                            <div class="w-full sm:w-40 bg-white dark:bg-zinc-800/90 rounded-xl px-2.5 py-0.5 border border-zinc-200 dark:border-white/10 shadow-2xs">
                                <x-stepper
                                    step="0.1"
                                    min="0"
                                    placeholder="0.0"
                                    variant="ios"
                                    wire:key="daily-res-sisa-{{ $selectedDay }}"
                                    wire:model.live.debounce.500ms="grid.{{ $selectedDay }}.result_sisa_kg"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- LIVE METRICS & CONVERSION SUMMARY --}}
                    <div class="p-4 rounded-2xl bg-linear-to-br from-zinc-900 to-zinc-800 text-white dark:from-zinc-800 dark:to-zinc-900 border border-zinc-800 shadow-xs space-y-3">
                        <div class="flex items-center justify-between text-xs text-zinc-400">
                            <span class="uppercase tracking-wider font-semibold text-[10px]">Performa Produksi Hari Ini</span>
                            <span class="font-mono text-[11px]">Kl: 9.3kg | Rf: 5.0kg</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 pt-1 border-t border-white/10">
                            <div>
                                <span class="text-[11px] text-zinc-400 block">Total Berat Jadi</span>
                                <div class="text-xl font-bold font-mono text-emerald-400">
                                    {{ number_format($metrics['total_kg'], 1, ',', '.') }}
                                    <span class="text-xs font-sans text-zinc-300 font-normal">Kg</span>
                                </div>
                            </div>

                            <div>
                                <span class="text-[11px] text-zinc-400 block">Rata-rata / Sesi</span>
                                <div class="text-xl font-bold font-mono text-purple-300">
                                    {{ number_format($metrics['avg_kg_per_session'], 1, ',', '.') }}
                                    <span class="text-xs font-sans text-zinc-300 font-normal">Kg/Sesi</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CARD 3: OPNAME FISIK GUDANG --}}
                <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-white/10 p-5 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-white/5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-2xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                                <flux:icon.clipboard-document-check class="w-4 h-4" />
                            </div>
                            <div class="min-w-0">
                                <flux:heading size="base" class="font-bold truncate">3. Stock Nyata (Opname Gudang)</flux:heading>
                                <flux:subheading size="xs" class="hidden sm:block">Pengecekan fisik barang jadi di Gudang Petis</flux:subheading>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        {{-- Opname Kaleng --}}
                        <div class="p-3.5 rounded-2xl bg-purple-50/40 dark:bg-purple-950/20 border border-purple-200/80 dark:border-purple-900/40 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-purple-900 dark:text-purple-300">Fisik Kaleng (Kl)</span>
                                <span class="text-[11px] text-zinc-500">
                                    Sistem: <strong>{{ number_format($whStocks['kaleng'], 0, ',', '.') }}</strong>
                                </span>
                            </div>

                            <div class="bg-white dark:bg-zinc-800/90 rounded-xl px-2 py-0.5 border border-purple-200 dark:border-purple-800/60 shadow-2xs">
                                <x-stepper
                                    step="0.5"
                                    min="0"
                                    placeholder="0"
                                    variant="ios"
                                    wire:key="daily-op-kl-{{ $selectedDay }}"
                                    wire:model.live.debounce.500ms="grid.{{ $selectedDay }}.opname_kaleng"
                                />
                            </div>

                            @if ($metrics['opname_kaleng'] !== null)
                                @php $diffK = $metrics['opname_kaleng'] - $whStocks['kaleng']; @endphp
                                <div class="text-[11px] text-right font-mono">
                                    Selisih:
                                    <strong class="{{ $diffK == 0 ? 'text-emerald-600' : ($diffK > 0 ? 'text-blue-600' : 'text-rose-600') }}">
                                        {{ $diffK > 0 ? "+{$diffK}" : $diffK }} Kl
                                    </strong>
                                </div>
                            @endif
                        </div>

                        {{-- Opname Refill --}}
                        <div class="p-3.5 rounded-2xl bg-purple-50/40 dark:bg-purple-950/20 border border-purple-200/80 dark:border-purple-900/40 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-purple-900 dark:text-purple-300">Fisik Refill (Rf)</span>
                                <span class="text-[11px] text-zinc-500">
                                    Sistem: <strong>{{ number_format($whStocks['refill'], 0, ',', '.') }}</strong>
                                </span>
                            </div>

                            <div class="bg-white dark:bg-zinc-800/90 rounded-xl px-2 py-0.5 border border-purple-200 dark:border-purple-800/60 shadow-2xs">
                                <x-stepper
                                    step="0.5"
                                    min="0"
                                    placeholder="0"
                                    variant="ios"
                                    wire:key="daily-op-rf-{{ $selectedDay }}"
                                    wire:model.live.debounce.500ms="grid.{{ $selectedDay }}.opname_refill"
                                />
                            </div>

                            @if ($metrics['opname_refill'] !== null)
                                @php $diffR = $metrics['opname_refill'] - $whStocks['refill']; @endphp
                                <div class="text-[11px] text-right font-mono">
                                    Selisih:
                                    <strong class="{{ $diffR == 0 ? 'text-emerald-600' : ($diffR > 0 ? 'text-blue-600' : 'text-rose-600') }}">
                                        {{ $diffR > 0 ? "+{$diffR}" : $diffR }} Bal
                                    </strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    {{-- ============================================================== --}}
    {{-- MODE 2: TABEL 1 BULAN PENUH (MATRIKS SPREADSHEET EXCEL)        --}}
    {{-- ============================================================== --}}
    @else

        {{-- Stat Cards Ringkasan Bulan Ini --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
            <div class="bg-white dark:bg-zinc-900 p-4 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Total Masakan</span>
                    <div class="w-7 h-7 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <flux:icon.fire class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold font-mono text-zinc-900 dark:text-white">
                    {{ number_format($grandTotalSessions, 0, ',', '.') }}
                    <span class="text-xs font-sans font-normal text-zinc-500">Mskn</span>
                </div>
                <div class="mt-1 text-[11px] text-zinc-500">
                    Dalam {{ $totalDaysCooking }} hari aktif produksi
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-4 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Hasil Jadi (Pcs)</span>
                    <div class="w-7 h-7 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <flux:icon.cube class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2 font-mono">
                    <div class="text-xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($grandTotalKaleng, 0, ',', '.') }} <span class="text-xs font-sans font-normal text-zinc-500">Kl</span>
                    </div>
                    <span class="text-zinc-300 dark:text-zinc-700">|</span>
                    <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ number_format($grandTotalRefill, 0, ',', '.') }} <span class="text-xs font-sans font-normal text-zinc-500">Bal</span>
                    </div>
                </div>
                <div class="mt-1 text-[11px] text-zinc-500">
                    Sisa adonan: {{ number_format($grandTotalSisaKg, 1, ',', '.') }} Kg
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-4 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Total Hasil (Kg)</span>
                    <div class="w-7 h-7 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <flux:icon.scale class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold font-mono text-emerald-700 dark:text-emerald-400">
                    {{ number_format($grandTotalKg, 1, ',', '.') }}
                    <span class="text-xs font-sans font-normal text-zinc-500">Kg</span>
                </div>
                <div class="mt-1 text-[11px] text-zinc-500">
                    Kl: 9.3 kg, Rf: 5.0 kg
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 p-4 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Rata-rata / Masakan</span>
                    <div class="w-7 h-7 rounded-full bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <flux:icon.chart-bar class="w-4 h-4" />
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold font-mono text-purple-700 dark:text-purple-400">
                    {{ number_format($overallAvg, 1, ',', '.') }}
                    <span class="text-xs font-sans font-normal text-zinc-500">Kg/Mskn</span>
                </div>
                <div class="mt-1 text-[11px] text-zinc-500">
                    Efisiensi hasil per sesi wajan
                </div>
            </div>
        </div>

        {{-- Filter Toolbar Periode Bulan --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-zinc-50 dark:bg-zinc-900/60 p-3.5 rounded-2xl border border-zinc-200 dark:border-white/10">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider shrink-0">Bulan:</span>
                <flux:input type="month" wire:model.live="month" size="sm" class="w-40" />
            </div>

            <div class="flex items-center gap-2 text-[11px] sm:text-xs text-zinc-500">
                <flux:icon.check-circle class="w-4 h-4 text-emerald-500 shrink-0" />
                <span>Klik angka tanggal untuk buka harian, atau ketik langsung di spreadsheet.</span>
            </div>
        </div>

        {{-- Main Inline Spreadsheet Table --}}
        <div class="space-y-2">
            <div class="flex items-center gap-1.5 text-[11px] text-zinc-500 bg-zinc-100/80 dark:bg-zinc-800/80 px-3 py-1.5 rounded-xl sm:hidden">
                <flux:icon.arrows-right-left class="w-3.5 h-3.5 text-amber-500 shrink-0" />
                <span>Geser tabel ke samping untuk melihat koki & hasil</span>
            </div>

            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-white/10 overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                <table class="w-full text-xs text-left whitespace-nowrap">
                    <thead class="text-[11px] text-zinc-700 dark:text-zinc-300 uppercase bg-zinc-100/80 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-white/10">
                        <tr>
                            <th rowspan="2" class="px-3 py-3 font-bold border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-20 bg-zinc-100 dark:bg-zinc-800 w-14">
                                TGL
                            </th>
                            {{-- PROSES MASAKAN --}}
                            <th colspan="{{ $cookers->count() + 1 }}" class="px-3 py-2 text-center font-bold text-amber-700 dark:text-amber-400 border-r border-zinc-200 dark:border-white/10 bg-amber-50/50 dark:bg-amber-950/20">
                                PROSES MASAKAN (SESI)
                            </th>
                            {{-- HASIL MASAKAN --}}
                            <th colspan="3" class="px-3 py-2 text-center font-bold text-blue-700 dark:text-blue-400 border-r border-zinc-200 dark:border-white/10 bg-blue-50/50 dark:bg-blue-950/20">
                                HASIL MASAKAN
                            </th>
                            {{-- KONVERSI (KG) --}}
                            <th colspan="4" class="px-3 py-2 text-center font-bold text-emerald-700 dark:text-emerald-400 border-r border-zinc-200 dark:border-white/10 bg-emerald-50/50 dark:bg-emerald-950/20">
                                KONVERSI HASIL (KG)
                            </th>
                            {{-- STOCK NYATA --}}
                            <th colspan="2" class="px-3 py-2 text-center font-bold text-purple-700 dark:text-purple-400 bg-purple-50/50 dark:bg-purple-950/20">
                                STOCK NYATA (OPNAME)
                            </th>
                        </tr>
                        <tr class="border-t border-zinc-200 dark:border-white/10 text-[10px]">
                            {{-- Kolom tiap Koki --}}
                            @foreach ($cookers as $c)
                                <th class="px-1 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-amber-50/30 dark:bg-amber-950/10">
                                    {{ $c->name }}
                                </th>
                            @endforeach
                            <th class="px-2 py-1.5 font-bold border-r border-zinc-200 dark:border-white/10 text-center min-w-[55px] bg-amber-100/60 dark:bg-amber-900/30 text-amber-900 dark:text-amber-300">
                                TOTAL
                            </th>

                            {{-- Hasil Masakan --}}
                            <th class="px-1 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-blue-50/30 dark:bg-blue-950/10">KALENG</th>
                            <th class="px-1 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-blue-50/30 dark:bg-blue-950/10">BAL (RF)</th>
                            <th class="px-1 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-blue-50/30 dark:bg-blue-950/10">SISA (KG)</th>

                            {{-- Konversi --}}
                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-emerald-50/30 dark:bg-emerald-950/10">KL (KG)</th>
                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-emerald-50/30 dark:bg-emerald-950/10">RF (KG)</th>
                            <th class="px-2 py-1.5 font-bold border-r border-zinc-200 dark:border-white/10 text-center min-w-[70px] bg-emerald-100/60 dark:bg-emerald-900/30 text-emerald-900 dark:text-emerald-300">TOTAL KG</th>
                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[70px] bg-emerald-50/30 dark:bg-emerald-950/10">RATA-RATA</th>

                            {{-- Opname --}}
                            <th class="px-1 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-purple-50/30 dark:bg-purple-950/10">KALENG</th>
                            <th class="px-1 py-1.5 font-medium text-center min-w-[65px] bg-purple-50/30 dark:bg-purple-950/10">REFILL</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-white/10 font-mono text-[11px]">
                        @foreach ($grid as $day => $row)
                            @php
                                $rowBg = $row['is_sunday'] ? 'bg-rose-50/30 dark:bg-rose-950/10' : 'hover:bg-zinc-50 dark:hover:bg-white/[0.02]';

                                // Real-time row calculations
                                $rowDaySessions = 0;
                                foreach ($cookers as $c) {
                                    $rowDaySessions += (float)($row['sessions'][$c->name] ?? 0);
                                }
                                $rowKl = (int)($row['result_kaleng'] ?: 0);
                                $rowRf = (int)($row['result_refill'] ?: 0);
                                $rowSisa = (float)($row['result_sisa_kg'] ?: 0);
                                $rowKgKl = $rowKl * \App\Models\PetisProduction::WEIGHT_KALENG_KG;
                                $rowKgRf = $rowRf * \App\Models\PetisProduction::WEIGHT_REFILL_KG;
                                $rowTotalKg = $rowKgKl + $rowKgRf + $rowSisa;
                                $rowAvg = $rowDaySessions > 0 ? round($rowTotalKg / $rowDaySessions, 2) : 0;
                            @endphp
                            <tr class="{{ $rowBg }} transition-colors group">
                                {{-- Kolom TGL Sticky --}}
                                <td class="px-3 py-1 text-center sticky left-0 z-10 bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-white/10 font-medium">
                                    <button type="button" wire:click="selectDay({{ $day }})" title="Buka Input Harian Tanggal {{ $day }}"
                                        class="cursor-pointer {{ $row['is_sunday'] ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-zinc-700 dark:text-zinc-300' }} hover:underline hover:text-amber-600 font-bold">
                                        {{ $day }}
                                    </button>
                                </td>

                                {{-- Input Sesi Koki --}}
                                @foreach ($cookers as $c)
                                    <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                        <input type="number" step="0.5" min="0"
                                            wire:key="tbl-sess-{{ $day }}-{{ $c->name }}"
                                            wire:model.live.debounce.500ms="grid.{{ $day }}.sessions.{{ $c->name }}"
                                            class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-amber-900 dark:text-amber-200 placeholder-transparent"
                                            placeholder="" />
                                    </td>
                                @endforeach

                                {{-- Total Sesi (Computed Live) --}}
                                <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 font-bold text-amber-900 dark:text-amber-400 bg-amber-50/40 dark:bg-amber-950/20">
                                    {{ $rowDaySessions > 0 ? number_format($rowDaySessions, 0, ',', '.') : '' }}
                                </td>

                                {{-- Input Hasil Jadi: Kaleng --}}
                                <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                    <input type="number" min="0"
                                        wire:key="tbl-res-kl-{{ $day }}"
                                        wire:model.live.debounce.500ms="grid.{{ $day }}.result_kaleng"
                                        class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-bold text-blue-700 dark:text-blue-300 placeholder-transparent"
                                        placeholder="" />
                                </td>

                                {{-- Input Hasil Jadi: Refill --}}
                                <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                    <input type="number" min="0"
                                        wire:key="tbl-res-rf-{{ $day }}"
                                        wire:model.live.debounce.500ms="grid.{{ $day }}.result_refill"
                                        class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-bold text-blue-700 dark:text-blue-300 placeholder-transparent"
                                        placeholder="" />
                                </td>

                                {{-- Input Hasil Jadi: Sisa Kg --}}
                                <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                    <input type="number" step="0.1" min="0"
                                        wire:key="tbl-res-sisa-{{ $day }}"
                                        wire:model.live.debounce.500ms="grid.{{ $day }}.result_sisa_kg"
                                        class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-md transition-all py-1 px-1 text-xs font-mono text-zinc-700 dark:text-zinc-300 placeholder-transparent"
                                        placeholder="" />
                                </td>

                                {{-- Konversi (Computed Live) --}}
                                <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-600 dark:text-zinc-400">
                                    {{ $rowKgKl > 0 ? number_format($rowKgKl, 1, ',', '.') : '' }}
                                </td>
                                <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-600 dark:text-zinc-400">
                                    {{ $rowKgRf > 0 ? number_format($rowKgRf, 1, ',', '.') : '' }}
                                </td>
                                <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-50/20">
                                    {{ $rowTotalKg > 0 ? number_format($rowTotalKg, 1, ',', '.') : '' }}
                                </td>
                                <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 {{ $rowAvg > 0 ? 'text-purple-700 dark:text-purple-400 font-bold' : 'text-zinc-400' }}">
                                    {{ $rowAvg > 0 ? number_format($rowAvg, 1, ',', '.') : '' }}
                                </td>

                                {{-- Input Opname Fisik: Kaleng --}}
                                <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                    <input type="number" step="0.5" min="0"
                                        wire:key="tbl-op-kl-{{ $day }}"
                                        wire:model.live.debounce.500ms="grid.{{ $day }}.opname_kaleng"
                                        class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-purple-700 dark:text-purple-300 placeholder-transparent"
                                        placeholder="" />
                                </td>

                                {{-- Input Opname Fisik: Refill --}}
                                <td class="p-0.5">
                                    <input type="number" step="0.5" min="0"
                                        wire:key="tbl-op-rf-{{ $day }}"
                                        wire:model.live.debounce.500ms="grid.{{ $day }}.opname_refill"
                                        class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-purple-700 dark:text-purple-300 placeholder-transparent"
                                        placeholder="" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Footer TOTAL & HARI --}}
                    <tfoot class="bg-zinc-100 dark:bg-zinc-800 text-[11px] font-bold font-mono border-t-2 border-zinc-300 dark:border-zinc-700">
                        <tr class="bg-zinc-200/70 dark:bg-zinc-800">
                            <td class="px-3 py-2 text-center sticky left-0 z-10 bg-zinc-200 dark:bg-zinc-800 border-r border-zinc-300 dark:border-zinc-700">
                                TOTAL
                            </td>

                            @foreach ($cookers as $c)
                                <td class="px-1 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-amber-900 dark:text-amber-400">
                                    {{ number_format($totalCookerSessions[$c->name] ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach

                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-amber-900 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/40">
                                {{ number_format($grandTotalSessions, 0, ',', '.') }}
                            </td>

                            <td class="px-1 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-blue-800 dark:text-blue-400">
                                {{ number_format($grandTotalKaleng, 0, ',', '.') }}
                            </td>
                            <td class="px-1 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-blue-800 dark:text-blue-400">
                                {{ number_format($grandTotalRefill, 0, ',', '.') }}
                            </td>
                            <td class="px-1 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-700 dark:text-zinc-300">
                                {{ number_format($grandTotalSisaKg, 1, ',', '.') }}
                            </td>

                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-emerald-800 dark:text-emerald-400">
                                {{ number_format($grandTotalKaleng * \App\Models\PetisProduction::WEIGHT_KALENG_KG, 1, ',', '.') }}
                            </td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-emerald-800 dark:text-emerald-400">
                                {{ number_format($grandTotalRefill * \App\Models\PetisProduction::WEIGHT_REFILL_KG, 1, ',', '.') }}
                            </td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-emerald-900 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/40 font-bold">
                                {{ number_format($grandTotalKg, 1, ',', '.') }}
                            </td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-purple-900 dark:text-purple-300 bg-purple-100/50 dark:bg-purple-900/30">
                                {{ number_format($overallAvg, 1, ',', '.') }}
                            </td>

                            <td class="px-1 py-2 text-center border-r border-zinc-200 dark:border-white/10"></td>
                            <td class="px-1 py-2 text-center"></td>
                        </tr>

                        <tr class="text-[10px] text-zinc-500 bg-zinc-100 dark:bg-zinc-900 font-sans">
                            <td class="px-3 py-1.5 text-center sticky left-0 z-10 bg-zinc-100 dark:bg-zinc-900 border-r border-zinc-200 dark:border-white/10">
                                HARI
                            </td>
                            @foreach ($cookers as $c)
                                <td class="px-1 py-1.5 text-center border-r border-zinc-200 dark:border-white/10">
                                    {{ ($totalCookerDays[$c->name] ?? 0) > 0 ? ($totalCookerDays[$c->name] . ' Hari') : '' }}
                                </td>
                            @endforeach
                            <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 font-bold">
                                {{ $totalDaysCooking > 0 ? ($totalDaysCooking . ' Hari') : '' }}
                            </td>
                            @for ($s = 0; $s < 9; $s++)
                                <td class="px-1 py-1.5 text-center border-r border-zinc-200 dark:border-white/10"></td>
                            @endfor
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        </div>
    @endif
</div>
