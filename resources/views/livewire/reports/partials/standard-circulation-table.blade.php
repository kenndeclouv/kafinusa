<div class="overflow-x-auto">
    <table class="w-full text-sm text-left whitespace-nowrap">
        <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-800/50 dark:text-zinc-300 border-b border-zinc-200 dark:border-white/10">
            <tr>
                <th rowspan="2" class="px-3 py-3 font-semibold border-r border-zinc-200 dark:border-white/10 align-middle text-center sticky left-0 z-20 bg-zinc-100 dark:bg-zinc-800">
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
                <td class="px-3 py-2 font-bold text-zinc-900 dark:text-white border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-20 bg-zinc-100 dark:bg-zinc-800">
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
                    <td class="px-3 py-2 font-medium text-zinc-900 dark:text-white border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-20 bg-white dark:bg-zinc-900">
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
                <td class="px-3 py-3 font-bold text-zinc-900 dark:text-white border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-20 bg-zinc-100 dark:bg-zinc-800">
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
