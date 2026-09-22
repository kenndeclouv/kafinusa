<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 no-print">
        <div>
            <flux:heading size="xl" level="1">Sirkulasi Stok Bulanan</flux:heading>
            <flux:description>Rangkuman mutasi barang masuk, keluar, dan sisa saldo harian.</flux:description>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="w-full sm:w-48">
                <x-searchable-select wire:model.live="warehouse_id" :options="$this->warehouses" variant="ios" placeholder="Pilih Gudang" />
            </div>

            <div class="w-full sm:w-48">
                <x-searchable-select wire:model.live="item_category_id" :options="$this->categories" variant="ios" placeholder="Pilih Kategori" />
            </div>

            <div class="w-full sm:w-32">
                <x-searchable-select wire:model.live="month" :options="$this->months" variant="ios" :searchable="false" />
            </div>

            <div class="w-full sm:w-28">
                <x-searchable-select wire:model.live="year" :options="$this->years" variant="ios" :searchable="false" />
            </div>

            <flux:button href="{{ route('reports.print-stock-circulation', ['month' => $month, 'year' => $year, 'warehouse_id' => $warehouse_id, 'item_category_id' => $item_category_id]) }}" target="_blank" variant="primary" icon="printer" class="w-full sm:w-auto">
                Cetak
            </flux:button>
        </div>
    </div>

    <!-- Print Header (Only visible when printing) -->
    <div class="hidden print:block mb-6 text-center">
        <h1 class="text-2xl font-bold text-black uppercase">SIRKULASI {{ $this->item_category_id ? strtoupper($this->categories[$this->item_category_id] ?? 'STOK') : 'STOK' }} HARIAN</h1>
        <h2 class="text-lg font-bold text-black uppercase mt-1">UD. CITRA MAJU BERSAMA</h2>
        <div class="mt-4 text-right text-black font-medium">
            BULAN : {{ \Carbon\Carbon::create()->month((int)$month)->translatedFormat('F') }} {{ $year }}
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/5 shadow-xs print:shadow-none print:border-none overflow-hidden print:overflow-visible">
        @if(!$this->circulationData || $this->items->isEmpty())
            <div class="py-16 flex flex-col items-center justify-center text-center">
                <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-4 mb-4">
                    <flux:icon.clipboard-document-list class="w-8 h-8 text-zinc-500 dark:text-zinc-400" />
                </div>
                <flux:heading size="lg">Tidak Ada Data</flux:heading>
                <flux:text class="mt-2 max-w-sm text-zinc-500 dark:text-zinc-400">
                    Silakan pilih Gudang dan Kategori Barang yang memiliki data item.
                </flux:text>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-800/50 dark:text-zinc-300 border-b border-zinc-200 dark:border-white/10">
                        <tr>
                            <th rowspan="2" class="px-3 py-3 font-semibold border-r border-zinc-200 dark:border-white/10 align-middle text-center sticky left-0 z-10 bg-zinc-50 dark:bg-zinc-800/50">
                                TGL
                            </th>
                            <th colspan="{{ $this->items->count() }}" class="px-4 py-2 font-bold text-center border-r border-zinc-200 dark:border-white/10 text-emerald-700 dark:text-emerald-400">
                                PEMBELIAN (IN)
                            </th>
                            <th colspan="{{ $this->items->count() }}" class="px-4 py-2 font-bold text-center border-r border-zinc-200 dark:border-white/10 text-red-700 dark:text-red-400">
                                PENJUALAN (OUT)
                            </th>
                            <th colspan="{{ $this->items->count() }}" class="px-4 py-2 font-bold text-center text-blue-700 dark:text-blue-400 border-r border-zinc-200 dark:border-white/10">
                                STOC GUDANG (BAL)
                            </th>
                            <th rowspan="2" class="px-4 py-3 font-semibold align-middle text-center">
                                KETERANGAN
                            </th>
                        </tr>
                        <tr class="border-t border-zinc-200 dark:border-white/10">
                            <!-- IN Items -->
                            @foreach($this->items as $item)
                                <th class="px-2 py-2 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[60px]">
                                    {{ $item->name }}
                                </th>
                            @endforeach
                            <!-- OUT Items -->
                            @foreach($this->items as $item)
                                <th class="px-2 py-2 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[60px]">
                                    {{ $item->name }}
                                </th>
                            @endforeach
                            <!-- BAL Items -->
                            @foreach($this->items as $item)
                                <th class="px-2 py-2 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[60px]">
                                    {{ $item->name }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                        <!-- INITIAL STOCK ROW (STOC) -->
                        <tr class="bg-zinc-50/50 dark:bg-white/[0.02]">
                            <td class="px-3 py-2 font-bold text-zinc-900 dark:text-white border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-10 bg-zinc-50 dark:bg-zinc-800/80">
                                STOC
                            </td>
                            <!-- Empty for IN -->
                            @foreach($this->items as $item)
                                <td class="px-2 py-2 border-r border-zinc-200 dark:border-white/10"></td>
                            @endforeach
                            <!-- Empty for OUT -->
                            @foreach($this->items as $item)
                                <td class="px-2 py-2 border-r border-zinc-200 dark:border-white/10"></td>
                            @endforeach
                            <!-- BAL: Initial Stock -->
                            @foreach($this->items as $item)
                                <td class="px-2 py-2 text-center font-bold text-red-600 dark:text-red-400 border-r border-zinc-200 dark:border-white/10 bg-red-50/30 dark:bg-red-900/10">
                                    {{ number_format($this->circulationData['initial'][$item->id]) }}
                                </td>
                            @endforeach
                            <td class="px-4 py-2 text-zinc-500 text-xs italic">
                                Saldo Awal Bulan
                            </td>
                        </tr>

                        <!-- DAILY ROWS -->
                        @php
                            $totalIn = array_fill_keys($this->items->pluck('id')->toArray(), 0);
                            $totalOut = array_fill_keys($this->items->pluck('id')->toArray(), 0);
                        @endphp

                        @foreach($this->circulationData['daily'] as $day => $data)
                            <tr class="hover:bg-zinc-50/80 dark:hover:bg-white/[0.04]">
                                <td class="px-3 py-2 font-medium text-zinc-900 dark:text-white border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-10 bg-white dark:bg-zinc-900">
                                    {{ $day }}
                                </td>
                                
                                <!-- IN -->
                                @foreach($this->items as $item)
                                    @php 
                                        $val = $data['in'][$item->id]; 
                                        $totalIn[$item->id] += $val;
                                    @endphp
                                    <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 {{ $val > 0 ? 'text-zinc-900 dark:text-zinc-100 font-medium' : 'text-zinc-300 dark:text-zinc-700' }}">
                                        {{ $val > 0 ? number_format($val) : '' }}
                                    </td>
                                @endforeach

                                <!-- OUT -->
                                @foreach($this->items as $item)
                                    @php 
                                        $val = $data['out'][$item->id]; 
                                        $totalOut[$item->id] += $val;
                                    @endphp
                                    <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 {{ $val > 0 ? 'text-zinc-900 dark:text-zinc-100 font-medium' : 'text-zinc-300 dark:text-zinc-700' }}">
                                        {{ $val > 0 ? number_format($val) : '' }}
                                    </td>
                                @endforeach

                                <!-- BAL -->
                                @foreach($this->items as $item)
                                    <td class="px-2 py-2 text-center border-r border-zinc-200 dark:border-white/10 text-blue-800 dark:text-blue-300 font-medium bg-blue-50/10 dark:bg-blue-900/5">
                                        {{ number_format($data['bal'][$item->id]) }}
                                    </td>
                                @endforeach
                                
                                <td class="px-4 py-2 border-l border-zinc-200 dark:border-white/10"></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-white/10">
                        <tr>
                            <td class="px-3 py-3 font-bold text-zinc-900 dark:text-white border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-10 bg-zinc-50 dark:bg-zinc-800/80">
                                TTL
                            </td>
                            <!-- Total IN -->
                            @foreach($this->items as $item)
                                <td class="px-2 py-3 text-center font-bold text-emerald-700 dark:text-emerald-400 border-r border-zinc-200 dark:border-white/10 bg-emerald-50/50 dark:bg-emerald-900/10">
                                    {{ $totalIn[$item->id] > 0 ? number_format($totalIn[$item->id]) : '-' }}
                                </td>
                            @endforeach
                            <!-- Total OUT -->
                            @foreach($this->items as $item)
                                <td class="px-2 py-3 text-center font-bold text-red-700 dark:text-red-400 border-r border-zinc-200 dark:border-white/10 bg-red-50/30 dark:bg-red-900/10">
                                    {{ $totalOut[$item->id] > 0 ? number_format($totalOut[$item->id]) : '-' }}
                                </td>
                            @endforeach
                            <!-- Empty for BAL (or Final Balance) -->
                            @foreach($this->items as $item)
                                @php
                                    $finalBal = $this->circulationData['daily'][Carbon\Carbon::create($year, $month, 1)->daysInMonth]['bal'][$item->id];
                                @endphp
                                <td class="px-2 py-3 text-center font-bold text-blue-700 dark:text-blue-400 border-r border-zinc-200 dark:border-white/10 bg-blue-50/30 dark:bg-blue-900/10">
                                    {{ number_format($finalBal) }}
                                </td>
                            @endforeach
                            <td class="px-4 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
