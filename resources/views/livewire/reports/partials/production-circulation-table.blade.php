<div class="relative overflow-x-auto shadow-sm sm:rounded-2xl border border-zinc-200 dark:border-white/10">
    <table class="w-full text-sm text-left whitespace-nowrap">
        <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-800/50 dark:text-zinc-300 border-b border-zinc-200 dark:border-white/10">
            <tr>
                <th rowspan="2" class="px-3 py-3 font-semibold border-r border-zinc-200 dark:border-white/10 align-middle text-center sticky left-0 z-20 bg-zinc-100 dark:bg-zinc-800">
                    TGL
                </th>
                <th colspan="{{ $this->rawItems->count() + $this->resultItems->count() }}" class="px-4 py-2 font-bold text-center border-r border-zinc-200 dark:border-white/10 text-emerald-700 dark:text-emerald-400">
                    MASUK (IN)
                </th>
                <th colspan="{{ $this->rawItems->count() + $this->resultItems->count() }}" class="px-4 py-2 font-bold text-center border-r border-zinc-200 dark:border-white/10 text-blue-700 dark:text-blue-400">
                    PENJUALAN
                </th>
                <th colspan="{{ $this->rawItems->count() }}" class="px-4 py-2 font-bold text-center border-r border-zinc-200 dark:border-white/10 text-purple-700 dark:text-purple-400">
                    PEMAKAIAN u/ PRODUKSI
                </th>
                <th colspan="{{ $this->rawItems->count() + $this->resultItems->count() }}" class="px-4 py-2 font-bold text-center text-teal-700 dark:text-teal-400 border-r border-zinc-200 dark:border-white/10">
                    STOK GUDANG
                </th>
            </tr>
            <tr class="border-t border-zinc-200 dark:border-white/10">
                <!-- PEMBELIAN (Raw Items) -->
                @foreach($this->rawItems as $item)
                    <th class="px-2 py-2 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[50px] text-[10px]">
                        {{ $item->name }}
                    </th>
                @endforeach
                <!-- HASIL PRODUKSI (Result Items) -->
                @foreach($this->resultItems as $item)
                    <th class="px-2 py-2 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[50px] text-[10px]">
                        {{ $item->name }}
                    </th>
                @endforeach
                <!-- PENJUALAN (Raw Items) -->
                @foreach($this->rawItems as $item)
                    <th class="px-2 py-2 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[50px] text-[10px]">
                        {{ $item->name }}
                    </th>
                @endforeach
                <!-- PENJUALAN (Result Items) -->
                @foreach($this->resultItems as $item)
                    <th class="px-2 py-2 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[50px] text-[10px]">
                        {{ $item->name }}
                    </th>
                @endforeach
                <!-- PEMAKAIAN (Raw Items) -->
                @foreach($this->rawItems as $item)
                    <th class="px-2 py-2 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[50px] text-[10px]">
                        {{ $item->name }}
                    </th>
                @endforeach
                <!-- STOK (Raw Items) -->
                @foreach($this->rawItems as $item)
                    <th class="px-2 py-2 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[50px] text-[10px]">
                        {{ $item->name }}
                    </th>
                @endforeach
                <!-- STOK (Result Items) -->
                @foreach($this->resultItems as $item)
                    <th class="px-2 py-2 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[50px] text-[10px]">
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
                
                <!-- MASUK (IN): Initial Stock -->
                @foreach($this->rawItems as $item)
                    <td class="px-2 py-2 text-center text-xs font-bold text-emerald-700 dark:text-emerald-400 border-r border-zinc-200 dark:border-white/10 bg-emerald-50/30 dark:bg-emerald-900/10">
                        {{ formatQuantity($this->circulationData['initial'][$item->id] ?? 0) }}
                    </td>
                @endforeach
                @foreach($this->resultItems as $item)
                    <td class="px-2 py-2 text-center text-xs font-bold text-emerald-700 dark:text-emerald-400 border-r border-zinc-200 dark:border-white/10 bg-emerald-50/30 dark:bg-emerald-900/10">
                        {{ formatQuantity($this->circulationData['initial'][$item->id] ?? 0) }}
                    </td>
                @endforeach

                <!-- PENJUALAN & PEMAKAIAN: Empty -->
                @for ($i = 0; $i < ($this->rawItems->count() * 2 + $this->resultItems->count()); $i++)
                    <td class="px-2 py-2 border-r border-zinc-200 dark:border-white/10"></td>
                @endfor
                
                <!-- BAL: Initial Stock -->
                @foreach($this->rawItems as $item)
                    <td class="px-2 py-2 text-center text-xs font-bold text-teal-700 dark:text-teal-400 border-r border-zinc-200 dark:border-white/10 bg-teal-50/30 dark:bg-teal-900/10">
                        {{ formatQuantity($this->circulationData['initial'][$item->id] ?? 0) }}
                    </td>
                @endforeach
                @foreach($this->resultItems as $item)
                    <td class="px-2 py-2 text-center text-xs font-bold text-teal-700 dark:text-teal-400 border-r border-zinc-200 dark:border-white/10 bg-teal-50/30 dark:bg-teal-900/10">
                        {{ formatQuantity($this->circulationData['initial'][$item->id] ?? 0) }}
                    </td>
                @endforeach
            </tr>

            <!-- DAILY ROWS -->
            @php
                $totPembelian = [];
                $totHasil = [];
                $totPenjualanRaw = [];
                $totPenjualanRes = [];
                $totPemakaian = [];
            @endphp

            @foreach($this->circulationData['daily'] as $day => $data)
                <tr class="hover:bg-zinc-50/50 dark:hover:bg-white/[0.02]">
                    <td class="px-3 py-1 font-medium text-zinc-900 dark:text-white border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-20 bg-white dark:bg-zinc-900 group-hover:bg-zinc-50 dark:group-hover:bg-zinc-800">
                        {{ $day }}
                    </td>
                    
                    <!-- PEMBELIAN (Raw Items) -->
                    @foreach($this->rawItems as $item)
                        @php 
                            $val = $data['in'][$item->id]['pembelian'] ?? 0;
                            $totPembelian[$item->id] = ($totPembelian[$item->id] ?? 0) + $val;
                        @endphp
                        <td class="px-1 py-1 text-xs text-center border-r border-zinc-200 dark:border-white/10 {{ $val > 0 ? 'text-red-700 dark:text-red-400 font-medium' : 'text-zinc-300 dark:text-zinc-700' }}">
                            {{ $val > 0 ? formatQuantity($val) : '' }}
                        </td>
                    @endforeach

                    <!-- HASIL PRODUKSI (Result Items) -->
                    @foreach($this->resultItems as $item)
                        @php 
                            $val = $data['in'][$item->id]['hasil_produksi'] ?? 0;
                            $totHasil[$item->id] = ($totHasil[$item->id] ?? 0) + $val;
                        @endphp
                        <td class="px-1 py-1 text-xs text-center border-r border-zinc-200 dark:border-white/10 {{ $val > 0 ? 'text-emerald-700 dark:text-emerald-400 font-medium' : 'text-zinc-300 dark:text-zinc-700' }}">
                            {{ $val > 0 ? formatQuantity($val) : '' }}
                        </td>
                    @endforeach

                    <!-- PENJUALAN (Raw Items) -->
                    @foreach($this->rawItems as $item)
                        @php 
                            $val = $data['out'][$item->id]['penjualan'] ?? 0;
                            $totPenjualanRaw[$item->id] = ($totPenjualanRaw[$item->id] ?? 0) + $val;
                        @endphp
                        <td class="px-1 py-1 text-xs text-center border-r border-zinc-200 dark:border-white/10 {{ $val > 0 ? 'text-blue-700 dark:text-blue-400 font-medium' : 'text-zinc-300 dark:text-zinc-700' }}">
                            {{ $val > 0 ? formatQuantity($val) : '' }}
                        </td>
                    @endforeach
                    <!-- PENJUALAN (Result Items) -->
                    @foreach($this->resultItems as $item)
                        @php 
                            $val = $data['out'][$item->id]['penjualan'] ?? 0;
                            $totPenjualanRes[$item->id] = ($totPenjualanRes[$item->id] ?? 0) + $val;
                        @endphp
                        <td class="px-1 py-1 text-xs text-center border-r border-zinc-200 dark:border-white/10 {{ $val > 0 ? 'text-blue-700 dark:text-blue-400 font-medium' : 'text-zinc-300 dark:text-zinc-700' }}">
                            {{ $val > 0 ? formatQuantity($val) : '' }}
                        </td>
                    @endforeach
                    
                    <!-- PEMAKAIAN (Raw Items) -->
                    @foreach($this->rawItems as $item)
                        @php 
                            $val = $data['out'][$item->id]['pemakaian'] ?? 0;
                            $totPemakaian[$item->id] = ($totPemakaian[$item->id] ?? 0) + $val;
                        @endphp
                        <td class="px-1 py-1 text-xs text-center border-r border-zinc-200 dark:border-white/10 {{ $val > 0 ? 'text-purple-700 dark:text-purple-400 font-medium' : 'text-zinc-300 dark:text-zinc-700' }}">
                            {{ $val > 0 ? formatQuantity($val) : '' }}
                        </td>
                    @endforeach

                    <!-- STOK BAL (Raw Items) -->
                    @foreach($this->rawItems as $item)
                        <td class="px-1 py-1 text-xs text-center font-bold text-teal-700 dark:text-teal-400 border-r border-zinc-200 dark:border-white/10 bg-teal-50/10 dark:bg-teal-900/10">
                            {{ formatQuantity($data['bal'][$item->id] ?? 0) }}
                        </td>
                    @endforeach
                    <!-- STOK BAL (Result Items) -->
                    @foreach($this->resultItems as $item)
                        <td class="px-1 py-1 text-xs text-center font-bold text-teal-700 dark:text-teal-400 border-r border-zinc-200 dark:border-white/10 bg-teal-50/10 dark:bg-teal-900/10">
                            {{ formatQuantity($data['bal'][$item->id] ?? 0) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-white/10">
            <tr>
                <td class="px-3 py-3 font-bold text-zinc-900 dark:text-white border-r border-zinc-200 dark:border-white/10 text-center sticky left-0 z-20 bg-zinc-100 dark:bg-zinc-800">
                    TTL
                </td>
                
                <!-- Total Pembelian -->
                @foreach($this->rawItems as $item)
                    <td class="px-1 py-2 text-xs text-center font-bold text-red-700 dark:text-red-400 border-r border-zinc-200 dark:border-white/10 bg-red-50/50 dark:bg-red-900/10">
                        {{ ($totPembelian[$item->id] ?? 0) > 0 ? formatQuantity($totPembelian[$item->id]) : '-' }}
                    </td>
                @endforeach
                
                <!-- Total Hasil -->
                @foreach($this->resultItems as $item)
                    <td class="px-1 py-2 text-xs text-center font-bold text-emerald-700 dark:text-emerald-400 border-r border-zinc-200 dark:border-white/10 bg-emerald-50/50 dark:bg-emerald-900/10">
                        {{ ($totHasil[$item->id] ?? 0) > 0 ? formatQuantity($totHasil[$item->id]) : '-' }}
                    </td>
                @endforeach
                
                <!-- Total Penjualan -->
                @foreach($this->rawItems as $item)
                    <td class="px-1 py-2 text-xs text-center font-bold text-blue-700 dark:text-blue-400 border-r border-zinc-200 dark:border-white/10 bg-blue-50/50 dark:bg-blue-900/10">
                        {{ ($totPenjualanRaw[$item->id] ?? 0) > 0 ? formatQuantity($totPenjualanRaw[$item->id]) : '-' }}
                    </td>
                @endforeach
                @foreach($this->resultItems as $item)
                    <td class="px-1 py-2 text-xs text-center font-bold text-blue-700 dark:text-blue-400 border-r border-zinc-200 dark:border-white/10 bg-blue-50/50 dark:bg-blue-900/10">
                        {{ ($totPenjualanRes[$item->id] ?? 0) > 0 ? formatQuantity($totPenjualanRes[$item->id]) : '-' }}
                    </td>
                @endforeach

                <!-- Total Pemakaian -->
                @foreach($this->rawItems as $item)
                    <td class="px-1 py-2 text-xs text-center font-bold text-purple-700 dark:text-purple-400 border-r border-zinc-200 dark:border-white/10 bg-purple-50/50 dark:bg-purple-900/10">
                        {{ ($totPemakaian[$item->id] ?? 0) > 0 ? formatQuantity($totPemakaian[$item->id]) : '-' }}
                    </td>
                @endforeach

                <!-- Final Bal -->
                @foreach($this->rawItems as $item)
                    @php 
                        $daily = $this->circulationData['daily'] ?? [];
                        $lastDay = end($daily);
                        $f = $lastDay['bal'][$item->id] ?? 0; 
                    @endphp
                    <td class="px-1 py-2 text-xs text-center font-bold text-teal-700 dark:text-teal-400 border-r border-zinc-200 dark:border-white/10 bg-teal-50/30 dark:bg-teal-900/20">
                        {{ formatQuantity($f) }}
                    </td>
                @endforeach
                @foreach($this->resultItems as $item)
                    @php 
                        $daily = $this->circulationData['daily'] ?? [];
                        $lastDay = end($daily);
                        $f = $lastDay['bal'][$item->id] ?? 0; 
                    @endphp
                    <td class="px-1 py-2 text-xs text-center font-bold text-teal-700 dark:text-teal-400 border-r border-zinc-200 dark:border-white/10 bg-teal-50/30 dark:bg-teal-900/20">
                        {{ formatQuantity($f) }}
                    </td>
                @endforeach
            </tr>
        </tfoot>
    </table>
</div>
