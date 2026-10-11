<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <flux:badge color="amber" size="sm" icon="fire">Produksi Petis</flux:badge>
                <flux:heading size="xl" class="font-bold">Sirkulasi & Rekap Petis</flux:heading>
            </div>
            <flux:subheading size="sm" class="mt-1">
                Rekap sirkulasi stok petis jadi (Kaleng & Refill), integrasi hasil masakan, muatan/penjualan sales, dan audit stok opname.
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <flux:button variant="ghost" icon="printer"
                :href="route('petis.circulation.print', ['warehouse' => $warehouse_id, 'month' => $month])"
                target="_blank">
                Cetak Laporan
            </flux:button>

            @can('petis_productions:create')
                <flux:button variant="subtle" icon="adjustments-horizontal" wire:click="openInitialStockModal">
                    Stok Awal
                </flux:button>

                <flux:button variant="subtle" icon="clipboard-document-check" wire:click="openOpnameModal">
                    Stok Opname
                </flux:button>

                <flux:button variant="primary" icon="truck" wire:click="openSalesModal">
                    Muatan / Retur Sales
                </flux:button>
            @endcan
        </div>
    </div>

    @php
        $c = $this->circulationData;
        $days = $c['days'] ?? [];
        $salesmen = $this->salesmen;
    @endphp

    {{-- Filter & Tab Toolbar --}}
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

        <div>
            <x-tabs :active="$activeTab" @tab-change="$wire.set('activeTab', $event.detail)">
                <button type="button" class="x-tabs-btn" data-tab="rekap" @click="select('rekap', $el)">Sirkulasi & Rekap</button>
                <button type="button" class="x-tabs-btn" data-tab="sales" @click="select('sales', $el)">Muatan & Retur Sales</button>
            </x-tabs>
        </div>
    </div>

    @if ($activeTab === 'rekap')
        {{-- TAB 1: REKAP SIRKULASI STOK --}}
        <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-white/10 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left whitespace-nowrap">
                    <thead class="text-[11px] text-zinc-700 dark:text-zinc-300 uppercase bg-zinc-100/80 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-white/10">
                        <tr>
                            <th rowspan="2" class="px-3 py-3 font-bold border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-20 bg-zinc-100 dark:bg-zinc-800 w-16">
                                TGL
                            </th>
                            <th colspan="2" class="px-3 py-2 text-center font-bold text-emerald-700 dark:text-emerald-400 border-r border-zinc-200 dark:border-white/10 bg-emerald-50/50 dark:bg-emerald-950/20">
                                HASIL MASAKAN (IN)
                            </th>
                            <th colspan="2" class="px-3 py-2 text-center font-bold text-blue-700 dark:text-blue-400 border-r border-zinc-200 dark:border-white/10 bg-blue-50/50 dark:bg-blue-950/20">
                                PENJUALAN BERSIH (OUT)
                            </th>
                            <th colspan="2" class="px-3 py-2 text-center font-bold text-teal-700 dark:text-teal-400 border-r border-zinc-200 dark:border-white/10 bg-teal-50/50 dark:bg-teal-950/20">
                                STOK BUKU
                            </th>
                            <th colspan="2" class="px-3 py-2 text-center font-bold text-purple-700 dark:text-purple-400 border-r border-zinc-200 dark:border-white/10 bg-purple-50/50 dark:bg-purple-950/20">
                                STOK OPNAME (FISIK)
                            </th>
                            <th colspan="5" class="px-3 py-2 text-center font-bold text-rose-700 dark:text-rose-400 bg-rose-50/50 dark:bg-rose-950/20">
                                SELISIH STOK (OPNAME - BUKU)
                            </th>
                        </tr>
                        <tr class="border-t border-zinc-200 dark:border-white/10 text-[10px]">
                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[60px] bg-emerald-50/30 dark:bg-emerald-950/10">KALENG</th>
                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[60px] bg-emerald-50/30 dark:bg-emerald-950/10">REFILL</th>

                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[60px] bg-blue-50/30 dark:bg-blue-950/10">KALENG</th>
                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[60px] bg-blue-50/30 dark:bg-blue-950/10">REFILL</th>

                            <th class="px-2 py-1.5 font-bold border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-teal-50/30 dark:bg-teal-950/10">KALENG</th>
                            <th class="px-2 py-1.5 font-bold border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-teal-50/30 dark:bg-teal-950/10">REFILL</th>

                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-purple-50/30 dark:bg-purple-950/10">KALENG</th>
                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-purple-50/30 dark:bg-purple-950/10">REFILL</th>

                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-rose-50/30 dark:bg-rose-950/10">KL (PCS)</th>
                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-rose-50/30 dark:bg-rose-950/10">RF (BAL)</th>
                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-rose-50/30 dark:bg-rose-950/10">KL (KG)</th>
                            <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[65px] bg-rose-50/30 dark:bg-rose-950/10">RF (KG)</th>
                            <th class="px-2 py-1.5 font-bold text-center min-w-[75px] bg-rose-100/60 dark:bg-rose-900/30 text-rose-900 dark:text-rose-300">TOTAL KG</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-white/10 font-mono text-[11px]">
                        {{-- Row STOK AWAL --}}
                        <tr class="bg-amber-500/10 dark:bg-amber-500/15 font-bold">
                            <td class="px-3 py-2 text-center sticky left-0 z-10 bg-amber-100 dark:bg-zinc-800 border-r border-zinc-200 dark:border-white/10 text-amber-900 dark:text-amber-300">
                                S AWAL
                            </td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>

                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-teal-800 dark:text-teal-300 font-bold">
                                {{ number_format($c['initial_kaleng'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-teal-800 dark:text-teal-300 font-bold">
                                {{ number_format($c['initial_refill'] ?? 0, 0, ',', '.') }}
                            </td>

                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>

                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400">-</td>
                            <td class="px-2 py-2 text-center text-zinc-400">-</td>
                        </tr>

                        {{-- Loop Hari --}}
                        @foreach ($days as $d)
                            @php
                                $rowBg = $d['is_sunday'] ? 'bg-rose-50/40 dark:bg-rose-950/15' : 'hover:bg-zinc-50 dark:hover:bg-white/5';
                            @endphp
                            <tr class="{{ $rowBg }}">
                                <td class="px-3 py-1.5 text-center sticky left-0 z-10 bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-white/10 font-medium">
                                    <span class="{{ $d['is_sunday'] ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-zinc-700 dark:text-zinc-300' }}">
                                        {{ $d['day'] }}
                                    </span>
                                </td>

                                {{-- Hasil Masakan (IN) --}}
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['in_kaleng'] > 0 ? 'text-emerald-700 dark:text-emerald-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                    {{ $d['in_kaleng'] > 0 ? number_format($d['in_kaleng'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['in_refill'] > 0 ? 'text-emerald-700 dark:text-emerald-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                    {{ $d['in_refill'] > 0 ? number_format($d['in_refill'], 0, ',', '.') : '-' }}
                                </td>

                                {{-- Penjualan Bersih (OUT) --}}
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['out_kaleng'] > 0 ? 'text-blue-700 dark:text-blue-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                    {{ $d['out_kaleng'] > 0 ? number_format($d['out_kaleng'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['out_refill'] > 0 ? 'text-blue-700 dark:text-blue-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                    {{ $d['out_refill'] > 0 ? number_format($d['out_refill'], 0, ',', '.') : '-' }}
                                </td>

                                {{-- Stok Buku --}}
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 font-semibold text-zinc-900 dark:text-white">
                                    {{ number_format($d['book_kaleng'], 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 font-semibold text-zinc-900 dark:text-white">
                                    {{ number_format($d['book_refill'], 0, ',', '.') }}
                                </td>

                                {{-- Stok Opname --}}
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['opname_kaleng'] !== null ? 'text-purple-700 dark:text-purple-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                    {{ $d['opname_kaleng'] !== null ? number_format($d['opname_kaleng'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['opname_refill'] !== null ? 'text-purple-700 dark:text-purple-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                    {{ $d['opname_refill'] !== null ? number_format($d['opname_refill'], 0, ',', '.') : '-' }}
                                </td>

                                {{-- Selisih --}}
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['diff_kaleng'] !== null && $d['diff_kaleng'] != 0 ? 'text-rose-600 font-bold bg-rose-50/20' : 'text-zinc-400' }}">
                                    {{ $d['diff_kaleng'] !== null ? number_format($d['diff_kaleng'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['diff_refill'] !== null && $d['diff_refill'] != 0 ? 'text-rose-600 font-bold bg-rose-50/20' : 'text-zinc-400' }}">
                                    {{ $d['diff_refill'] !== null ? number_format($d['diff_refill'], 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-600 dark:text-zinc-400">
                                    {{ $d['diff_kaleng_kg'] !== null ? number_format($d['diff_kaleng_kg'], 1, ',', '.') : '-' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-600 dark:text-zinc-400">
                                    {{ $d['diff_refill_kg'] !== null ? number_format($d['diff_refill_kg'], 1, ',', '.') : '-' }}
                                </td>
                                <td class="px-2 py-1.5 text-center {{ $d['total_diff_kg'] !== null && $d['total_diff_kg'] != 0 ? 'text-rose-600 dark:text-rose-400 font-bold bg-rose-50/30' : 'text-zinc-400' }}">
                                    {{ $d['total_diff_kg'] !== null ? number_format($d['total_diff_kg'], 1, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Footer Summary --}}
                    <tfoot class="bg-zinc-200/70 dark:bg-zinc-800 text-[11px] font-bold font-mono border-t-2 border-zinc-300 dark:border-zinc-700">
                        <tr>
                            <td class="px-3 py-2 text-center sticky left-0 z-10 bg-zinc-200 dark:bg-zinc-800 border-r border-zinc-300 dark:border-zinc-700">
                                TOTAL
                            </td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-emerald-800 dark:text-emerald-400">
                                {{ number_format($c['total_prod_kaleng'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-emerald-800 dark:text-emerald-400">
                                {{ number_format($c['total_prod_refill'] ?? 0, 0, ',', '.') }}
                            </td>

                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-blue-800 dark:text-blue-400">
                                {{ number_format($c['total_sold_kaleng'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-blue-800 dark:text-blue-400">
                                {{ number_format($c['total_sold_refill'] ?? 0, 0, ',', '.') }}
                            </td>

                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-teal-900 dark:text-teal-300 bg-teal-100 dark:bg-teal-900/40">
                                {{ number_format($c['final_kaleng'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-teal-900 dark:text-teal-300 bg-teal-100 dark:bg-teal-900/40">
                                {{ number_format($c['final_refill'] ?? 0, 0, ',', '.') }}
                            </td>

                            <td colspan="7" class="px-2 py-2 text-center text-zinc-400 font-sans text-[10px]">
                                *Stok Akhir Buku: S Awal + Hasil Masakan - Penjualan Bersih
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    @else
        {{-- TAB 2: MUATAN & RETUR SALES --}}
        @php
            $startOfMonth = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
            $daysInMonth = $startOfMonth->daysInMonth;
            $allSales = \App\Models\PetisSale::where('warehouse_id', $warehouse_id)
                ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $startOfMonth->copy()->endOfMonth()->format('Y-m-d')])
                ->get();
        @endphp

        <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-white/10 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left whitespace-nowrap">
                    <thead class="text-[11px] text-zinc-700 dark:text-zinc-300 uppercase bg-zinc-100/80 dark:bg-zinc-800/80 border-b border-zinc-200 dark:border-white/10">
                        <tr>
                            <th rowspan="2" class="px-3 py-3 font-bold border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-20 bg-zinc-100 dark:bg-zinc-800 w-16">
                                TGL
                            </th>
                            @foreach ($salesmen as $sm)
                                <th colspan="2" class="px-3 py-2 text-center font-bold border-r border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/5">
                                    {{ $sm }}
                                </th>
                            @endforeach
                            <th colspan="2" class="px-3 py-2 text-center font-bold text-blue-700 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-950/20 border-r border-zinc-200 dark:border-white/10">
                                TOTAL PENJUALAN
                            </th>
                            <th rowspan="2" class="px-3 py-3 text-center font-bold text-zinc-500 w-16">
                                AKSI
                            </th>
                        </tr>
                        <tr class="border-t border-zinc-200 dark:border-white/10 text-[10px]">
                            @foreach ($salesmen as $sm)
                                <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[50px]">KL</th>
                                <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[50px]">RF</th>
                            @endforeach
                            <th class="px-2 py-1.5 font-bold border-r border-zinc-200 dark:border-white/10 text-center min-w-[60px] text-blue-800 dark:text-blue-300">KL</th>
                            <th class="px-2 py-1.5 font-bold border-r border-zinc-200 dark:border-white/10 text-center min-w-[60px] text-blue-800 dark:text-blue-300">RF</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-200 dark:divide-white/10 font-mono text-[11px]">
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $cDate = $startOfMonth->copy()->day($day)->format('Y-m-d');
                                $isSun = $startOfMonth->copy()->day($day)->isSunday();
                                $daySales = $allSales->where('date', $cDate);
                                $daySoldKl = 0;
                                $daySoldRf = 0;
                            @endphp
                            <tr class="{{ $isSun ? 'bg-rose-50/40 dark:bg-rose-950/15' : 'hover:bg-zinc-50 dark:hover:bg-white/5' }}">
                                <td class="px-3 py-1.5 text-center sticky left-0 z-10 bg-white dark:bg-zinc-900 border-r border-zinc-200 dark:border-white/10 font-medium">
                                    <span class="{{ $isSun ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-zinc-700 dark:text-zinc-300' }}">
                                        {{ $day }}
                                    </span>
                                </td>

                                @foreach ($salesmen as $sm)
                                    @php
                                        $saleObj = $daySales->firstWhere('salesman_name', $sm);
                                        $soldKl = $saleObj ? $saleObj->sold_kaleng : 0;
                                        $soldRf = $saleObj ? $saleObj->sold_refill : 0;
                                        $daySoldKl += $soldKl;
                                        $daySoldRf += $soldRf;
                                    @endphp
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $soldKl > 0 ? 'text-blue-700 dark:text-blue-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                        {{ $soldKl > 0 ? $soldKl : '-' }}
                                    </td>
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $soldRf > 0 ? 'text-blue-700 dark:text-blue-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                        {{ $soldRf > 0 ? $soldRf : '-' }}
                                    </td>
                                @endforeach

                                {{-- Total Penjualan Hari Ini --}}
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 font-bold text-blue-900 dark:text-blue-300 bg-blue-50/30">
                                    {{ $daySoldKl > 0 ? $daySoldKl : '-' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 font-bold text-blue-900 dark:text-blue-300 bg-blue-50/30">
                                    {{ $daySoldRf > 0 ? $daySoldRf : '-' }}
                                </td>

                                <td class="px-2 py-1.5 text-center">
                                    @can('petis_productions:create')
                                        <flux:button size="xs" variant="ghost" icon="pencil" wire:click="openSalesModal('{{ $cDate }}')" />
                                    @endcan
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Modal Input Muatan & Retur Sales --}}
    <flux:modal :closable="false" scroll="body" name="sales-modal" class="md:w-[720px] !rounded-3xl">
        <form wire:submit="saveSales" class="space-y-5">
            <div>
                <flux:heading size="lg">Input Muatan & Retur Salesman</flux:heading>
                <flux:subheading size="sm">
                    Penjualan bersih dihitung otomatis: (Muatan Kaleng - Retur Kaleng) dan (Muatan Refill - Retur Refill).
                </flux:subheading>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="w-full sm:w-1/2">
                    <flux:input type="date" wire:model.live="sales_date" label="Tanggal Penjualan" required />
                </div>

                {{-- Tambah salesman custom --}}
                <div class="flex items-end gap-2 w-full sm:w-auto">
                    <flux:input wire:model="new_salesman_name" placeholder="Tambah nama sales..." size="sm" />
                    <flux:button type="button" size="sm" variant="subtle" wire:click="addSalesman">
                        + Tambah
                    </flux:button>
                </div>
            </div>

            {{-- Table Input per Salesman --}}
            <div class="space-y-2">
                <div class="max-h-[420px] overflow-y-auto rounded-2xl border border-zinc-200 dark:border-white/10 divide-y divide-zinc-200 dark:divide-white/10">
                    <div class="grid grid-cols-5 gap-2 px-3 py-2 bg-zinc-100 dark:bg-zinc-800 text-[11px] font-bold text-zinc-600 dark:text-zinc-400 uppercase sticky top-0 z-10">
                        <div>Salesman</div>
                        <div class="text-center">Muatan Kl</div>
                        <div class="text-center">Muatan Rf</div>
                        <div class="text-center">Retur Kl</div>
                        <div class="text-center">Retur Rf</div>
                    </div>

                    @foreach ($sales_data as $sName => $sVal)
                        <div class="grid grid-cols-5 gap-2 items-center px-3 py-2 bg-white dark:bg-zinc-900">
                            <span class="text-xs font-semibold text-zinc-900 dark:text-white truncate">
                                {{ $sName }}
                            </span>
                            <flux:input type="number" wire:model="sales_data.{{ $sName }}.muatan_kaleng" placeholder="0" size="sm" class="text-center" />
                            <flux:input type="number" wire:model="sales_data.{{ $sName }}.muatan_refill" placeholder="0" size="sm" class="text-center" />
                            <flux:input type="number" wire:model="sales_data.{{ $sName }}.retur_kaleng" placeholder="0" size="sm" class="text-center" />
                            <flux:input type="number" wire:model="sales_data.{{ $sName }}.retur_refill" placeholder="0" size="sm" class="text-center" />
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Simpan Penjualan
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal Stok Opname Kaleng & Refill --}}
    <flux:modal :closable="false" scroll="body" name="opname-modal" class="md:w-96 !rounded-3xl">
        <form wire:submit="saveOpname" class="space-y-5">
            <div>
                <flux:heading size="lg">Stok Opname Petis Jadi</flux:heading>
                <flux:subheading size="sm">
                    Masukkan hasil hitungan fisik petis di gudang.
                </flux:subheading>
            </div>

            <flux:input type="date" wire:model="opname_date" label="Tanggal Opname" required />

            <div class="space-y-3 bg-zinc-50 dark:bg-white/5 p-3.5 rounded-2xl border border-zinc-200 dark:border-white/10">
                <flux:input type="number" step="0.5" wire:model="actual_kaleng" label="Stok Fisik Kaleng (Kl)" placeholder="0" />
                <flux:input type="number" step="0.5" wire:model="actual_refill" label="Stok Fisik Refill (Bal)" placeholder="0" />
            </div>

            <flux:input wire:model="opname_notes" label="Catatan Opname (Opsional)" placeholder="Misal: Opname mingguan" />

            <div class="flex items-center justify-end gap-3 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Simpan Opname
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Modal Stok Awal --}}
    <flux:modal :closable="false" scroll="body" name="initial-stock-modal" class="md:w-96 !rounded-3xl">
        <form wire:submit="saveInitialStock" class="space-y-5">
            <div>
                <flux:heading size="lg">Atur Stok Awal Bulan</flux:heading>
                <flux:subheading size="sm">
                    Stok awal per tanggal 1 bulan {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}.
                </flux:subheading>
            </div>

            <div class="space-y-3 bg-zinc-50 dark:bg-white/5 p-3.5 rounded-2xl border border-zinc-200 dark:border-white/10">
                <flux:input type="number" step="0.5" wire:model="initial_kaleng" label="Stok Awal Kaleng (Kl)" placeholder="0" />
                <flux:input type="number" step="0.5" wire:model="initial_refill" label="Stok Awal Refill (Bal)" placeholder="0" />
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    Simpan Stok Awal
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
