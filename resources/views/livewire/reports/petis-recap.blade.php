<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 no-print">
        <div>
            <flux:heading size="xl" level="1">Rekap Sirkulasi Petis</flux:heading>
            <flux:description>Rangkuman hasil masakan, penjualan sales, stok buku, dan audit stok opname petis.</flux:description>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="w-full sm:w-48">
                <x-searchable-select wire:model.live="warehouse_id" :options="$this->warehouses" variant="ios" placeholder="Pilih Gudang" />
            </div>

            <div class="w-full sm:w-36">
                <x-searchable-select wire:model.live="month" :options="$this->months" variant="ios" :searchable="false" />
            </div>

            <div class="w-full sm:w-28">
                <x-searchable-select wire:model.live="year" :options="$this->years" variant="ios" :searchable="false" />
            </div>

            <flux:button href="{{ route('reports.print-petis-recap', ['warehouse' => $warehouse_id, 'month' => $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT)]) }}" target="_blank" variant="primary" icon="printer" class="w-full sm:w-auto">
                Cetak
            </flux:button>
        </div>
    </div>

    {{-- Tabs Segmented Control --}}
    <div class="mb-4 no-print">
        <x-tabs :active="$activeTab" @tab-change="$wire.set('activeTab', $event.detail)">
            <button type="button" class="x-tabs-btn" data-tab="circulation" @click="select('circulation', $el)">Sirkulasi & Rekap</button>
            <button type="button" class="x-tabs-btn" data-tab="sales" @click="select('sales', $el)">Muatan & Retur Sales</button>
        </x-tabs>
    </div>

    @php
        $c = $this->circulationData;
        $days = $c['days'] ?? [];
        $salesmen = $this->salesmen;
    @endphp

    <div class="rounded-2xl bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/5 shadow-xs overflow-hidden">
        @if (empty($days))
            <div class="py-16 flex flex-col items-center justify-center text-center">
                <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-4 mb-4">
                    <flux:icon.clipboard-document-list class="w-8 h-8 text-zinc-500 dark:text-zinc-400" />
                </div>
                <flux:heading size="lg">Tidak Ada Data</flux:heading>
                <flux:text class="mt-2 max-w-sm text-zinc-500 dark:text-zinc-400">
                    Pilih Gudang dan periode untuk memuat sirkulasi petis.
                </flux:text>
            </div>
        @elseif ($activeTab === 'circulation')
            {{-- TAB 1: SIRKULASI & REKAP STOK --}}
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
                        {{-- Row S AWAL --}}
                        <tr class="bg-amber-500/10 dark:bg-amber-500/15 font-bold">
                            <td class="px-3 py-2 text-center sticky left-0 z-10 bg-amber-100 dark:bg-zinc-800 border-r border-zinc-200 dark:border-white/10 text-amber-900 dark:text-amber-300">
                                S AWAL
                            </td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400"></td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400"></td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400"></td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400"></td>

                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-teal-800 dark:text-teal-300 font-bold">
                                {{ number_format($c['initial_kaleng'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-teal-800 dark:text-teal-300 font-bold">
                                {{ number_format($c['initial_refill'] ?? 0, 0, ',', '.') }}
                            </td>

                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400"></td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400"></td>

                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400"></td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400"></td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400"></td>
                            <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-400"></td>
                            <td class="px-2 py-2 text-center text-zinc-400"></td>
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

                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['in_kaleng'] > 0 ? 'text-emerald-700 dark:text-emerald-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                    {{ $d['in_kaleng'] > 0 ? number_format($d['in_kaleng'], 0, ',', '.') : '' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['in_refill'] > 0 ? 'text-emerald-700 dark:text-emerald-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                    {{ $d['in_refill'] > 0 ? number_format($d['in_refill'], 0, ',', '.') : '' }}
                                </td>

                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['out_kaleng'] > 0 ? 'text-blue-700 dark:text-blue-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                    {{ $d['out_kaleng'] > 0 ? number_format($d['out_kaleng'], 0, ',', '.') : '' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['out_refill'] > 0 ? 'text-blue-700 dark:text-blue-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                    {{ $d['out_refill'] > 0 ? number_format($d['out_refill'], 0, ',', '.') : '' }}
                                </td>

                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 font-semibold text-zinc-900 dark:text-white">
                                    {{ number_format($d['book_kaleng'], 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 font-semibold text-zinc-900 dark:text-white">
                                    {{ number_format($d['book_refill'], 0, ',', '.') }}
                                </td>

                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['opname_kaleng'] !== null ? 'text-purple-700 dark:text-purple-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                    {{ $d['opname_kaleng'] !== null ? number_format($d['opname_kaleng'], 0, ',', '.') : '' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['opname_refill'] !== null ? 'text-purple-700 dark:text-purple-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                    {{ $d['opname_refill'] !== null ? number_format($d['opname_refill'], 0, ',', '.') : '' }}
                                </td>

                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['diff_kaleng'] !== null && $d['diff_kaleng'] != 0 ? 'text-rose-600 font-bold bg-rose-50/20' : 'text-zinc-400' }}">
                                    {{ $d['diff_kaleng'] !== null && $d['diff_kaleng'] != 0 ? number_format($d['diff_kaleng'], 0, ',', '.') : '' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $d['diff_refill'] !== null && $d['diff_refill'] != 0 ? 'text-rose-600 font-bold bg-rose-50/20' : 'text-zinc-400' }}">
                                    {{ $d['diff_refill'] !== null && $d['diff_refill'] != 0 ? number_format($d['diff_refill'], 0, ',', '.') : '' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-600 dark:text-zinc-400">
                                    {{ $d['diff_kaleng_kg'] !== null && $d['diff_kaleng_kg'] != 0 ? number_format($d['diff_kaleng_kg'], 1, ',', '.') : '' }}
                                </td>
                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 text-zinc-600 dark:text-zinc-400">
                                    {{ $d['diff_refill_kg'] !== null && $d['diff_refill_kg'] != 0 ? number_format($d['diff_refill_kg'], 1, ',', '.') : '' }}
                                </td>
                                <td class="px-2 py-1.5 text-center {{ $d['total_diff_kg'] !== null && $d['total_diff_kg'] != 0 ? 'text-rose-600 dark:text-rose-400 font-bold bg-rose-50/30' : 'text-zinc-400' }}">
                                    {{ $d['total_diff_kg'] !== null && $d['total_diff_kg'] != 0 ? number_format($d['total_diff_kg'], 1, ',', '.') : '' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Footer TOTAL --}}
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

                            <td colspan="7" class="px-2 py-2 text-center text-zinc-500 font-sans text-[10px]">
                                *Stok Akhir Buku: S Awal + Hasil Masakan - Penjualan Bersih
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            {{-- TAB 2: MUATAN & RETUR SALES --}}
            @php
                $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
                $startOfMonth = \Carbon\Carbon::parse("{$year}-{$monthStr}-01")->startOfMonth();
                $daysInMonth = $startOfMonth->daysInMonth;
                $allSales = \App\Models\PetisSale::where('warehouse_id', $warehouse_id)
                    ->whereBetween('date', [$startOfMonth->format('Y-m-d'), $startOfMonth->copy()->endOfMonth()->format('Y-m-d')])
                    ->get();
            @endphp

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
                            <th colspan="2" class="px-3 py-2 text-center font-bold text-blue-700 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-950/20">
                                TOTAL PENJUALAN
                            </th>
                        </tr>
                        <tr class="border-t border-zinc-200 dark:border-white/10 text-[10px]">
                            @foreach ($salesmen as $sm)
                                <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[50px]">KL</th>
                                <th class="px-2 py-1.5 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[50px]">RF</th>
                            @endforeach
                            <th class="px-2 py-1.5 font-bold border-r border-zinc-200 dark:border-white/10 text-center min-w-[60px] text-blue-800 dark:text-blue-300">KL</th>
                            <th class="px-2 py-1.5 font-bold text-center min-w-[60px] text-blue-800 dark:text-blue-300">RF</th>
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
                                        {{ $soldKl > 0 ? $soldKl : '' }}
                                    </td>
                                    <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 {{ $soldRf > 0 ? 'text-blue-700 dark:text-blue-400 font-bold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                        {{ $soldRf > 0 ? $soldRf : '' }}
                                    </td>
                                @endforeach

                                <td class="px-2 py-1.5 text-center border-r border-zinc-200 dark:border-white/10 font-bold text-blue-900 dark:text-blue-300 bg-blue-50/30">
                                    {{ $daySoldKl > 0 ? $daySoldKl : '' }}
                                </td>
                                <td class="px-2 py-1.5 text-center font-bold text-blue-900 dark:text-blue-300 bg-blue-50/30">
                                    {{ $daySoldRf > 0 ? $daySoldRf : '' }}
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
