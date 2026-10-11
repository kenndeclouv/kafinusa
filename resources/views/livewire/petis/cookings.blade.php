<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <flux:badge color="amber" size="sm" icon="fire">Produksi Petis</flux:badge>
                <flux:heading size="xl" class="font-bold">Proses Masakan Petis</flux:heading>
            </div>
            <flux:subheading size="sm" class="mt-1">
                Input langsung sesi juru masak dan hasil produksi per hari. Otomatis tersimpan saat berpindah kolom.
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <div wire:loading wire:target="grid" class="flex items-center gap-2 text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-3 py-1.5 rounded-full border border-amber-200 dark:border-amber-800">
                <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span>Menyimpan otomatis...</span>
            </div>

            <flux:button variant="ghost" icon="printer"
                :href="route('petis.cookings.print', ['warehouse' => $warehouse_id, 'month' => $month])"
                target="_blank">
                Cetak Laporan
            </flux:button>

            @can('warehouses:read')
                <flux:button variant="subtle" icon="users" :href="route('teams.index')" wire:navigate>
                    Master Tim & Koki
                </flux:button>
            @endcan
        </div>
    </div>

    @php
        $cookers = $this->cookers;
        
        // Kalkulasi real-time ringkasan bulanan dari state $grid
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

    {{-- Stat Cards --}}
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
        </div>

        <div class="flex items-center gap-2 text-xs text-zinc-500">
            <flux:icon.check-circle class="w-4 h-4 text-emerald-500" />
            <span>Ketik angka di kolom mana saja & tekan <kbd class="px-1 py-0.5 rounded bg-zinc-200 dark:bg-zinc-800 font-mono text-[10px]">Tab</kbd> untuk berpindah. Otomatis tersimpan.</span>
        </div>
    </div>

    {{-- Main Inline Spreadsheet Table --}}
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
                                <span class="{{ $row['is_sunday'] ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    {{ $day }}
                                </span>
                            </td>

                            {{-- Input Sesi Koki --}}
                            @foreach ($cookers as $c)
                                <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                    <input type="number" step="0.5" min="0"
                                        wire:key="sess-{{ $day }}-{{ $c->name }}"
                                        wire:model.live.debounce.500ms="grid.{{ $day }}.sessions.{{ $c->name }}"
                                        class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-amber-900 dark:text-amber-200 placeholder-zinc-300"
                                        placeholder="-" />
                                </td>
                            @endforeach

                            {{-- Total Sesi (Computed Live) --}}
                            <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 font-bold text-amber-900 dark:text-amber-400 bg-amber-50/40 dark:bg-amber-950/20">
                                {{ $rowDaySessions > 0 ? number_format($rowDaySessions, 0, ',', '.') : '-' }}
                            </td>

                            {{-- Input Hasil Jadi: Kaleng --}}
                            <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                <input type="number" min="0"
                                    wire:key="res-kl-{{ $day }}"
                                    wire:model.live.debounce.500ms="grid.{{ $day }}.result_kaleng"
                                    class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-bold text-blue-700 dark:text-blue-300 placeholder-zinc-300"
                                    placeholder="-" />
                            </td>

                            {{-- Input Hasil Jadi: Refill --}}
                            <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                <input type="number" min="0"
                                    wire:key="res-rf-{{ $day }}"
                                    wire:model.live.debounce.500ms="grid.{{ $day }}.result_refill"
                                    class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-bold text-blue-700 dark:text-blue-300 placeholder-zinc-300"
                                    placeholder="-" />
                            </td>

                            {{-- Input Hasil Jadi: Sisa Kg --}}
                            <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                <input type="number" step="0.1" min="0"
                                    wire:key="res-sisa-{{ $day }}"
                                    wire:model.live.debounce.500ms="grid.{{ $day }}.result_sisa_kg"
                                    class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 rounded-md transition-all py-1 px-1 text-xs font-mono text-zinc-700 dark:text-zinc-300 placeholder-zinc-300"
                                    placeholder="-" />
                            </td>

                            {{-- Konversi (Computed Live) --}}
                            <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-600 dark:text-zinc-400">
                                {{ $rowKgKl > 0 ? number_format($rowKgKl, 1, ',', '.') : '-' }}
                            </td>
                            <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-600 dark:text-zinc-400">
                                {{ $rowKgRf > 0 ? number_format($rowKgRf, 1, ',', '.') : '-' }}
                            </td>
                            <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-50/20">
                                {{ $rowTotalKg > 0 ? number_format($rowTotalKg, 1, ',', '.') : '-' }}
                            </td>
                            <td class="px-2 py-1 text-center border-r border-zinc-200 dark:border-white/10 {{ $rowAvg > 0 ? 'text-purple-700 dark:text-purple-400 font-bold' : 'text-zinc-400' }}">
                                {{ $rowAvg > 0 ? number_format($rowAvg, 1, ',', '.') : '-' }}
                            </td>

                            {{-- Input Opname Fisik: Kaleng --}}
                            <td class="p-0.5 border-r border-zinc-200 dark:border-white/10">
                                <input type="number" step="0.5" min="0"
                                    wire:key="op-kl-{{ $day }}"
                                    wire:model.live.debounce.500ms="grid.{{ $day }}.opname_kaleng"
                                    class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-purple-700 dark:text-purple-300 placeholder-zinc-300"
                                    placeholder="-" />
                            </td>

                            {{-- Input Opname Fisik: Refill --}}
                            <td class="p-0.5">
                                <input type="number" step="0.5" min="0"
                                    wire:key="op-rf-{{ $day }}"
                                    wire:model.live.debounce.500ms="grid.{{ $day }}.opname_refill"
                                    class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 rounded-md transition-all py-1 px-1 text-xs font-mono font-medium text-purple-700 dark:text-purple-300 placeholder-zinc-300"
                                    placeholder="-" />
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

                        <td class="px-1 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                        <td class="px-1 py-2 text-center text-zinc-400">-</td>
                    </tr>

                    <tr class="text-[10px] text-zinc-500 bg-zinc-100 dark:bg-zinc-900 font-sans">
                        <td class="px-3 py-1.5 text-center sticky left-0 z-10 bg-zinc-100 dark:bg-zinc-900 border-r border-zinc-200 dark:border-white/10">
                            HARI
                        </td>
                        @foreach ($cookers as $c)
                            <td class="px-1 py-1.5 text-center border-r border-zinc-200 dark:border-white/10">
                                {{ $totalCookerDays[$c->name] ?? 0 }} Hari
                            </td>
                        @endforeach
                        <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 font-bold">
                            {{ $totalDaysCooking }} Hari
                        </td>
                        @for ($s = 0; $s < 9; $s++)
                            <td class="px-1 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                        @endfor
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
