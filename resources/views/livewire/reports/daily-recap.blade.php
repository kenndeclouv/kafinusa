<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 no-print">
        <div>
            <flux:heading size="xl" level="1">Rekapitulasi Penjualan Harian</flux:heading>
            <flux:description>Rangkuman total penjualan per barang oleh masing-masing karyawan.</flux:description>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <label class="flex flex-row items-center px-4 py-1.5 relative group focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text bg-white dark:bg-white/5 rounded-2xl border border-zinc-200 dark:border-white/10 shadow-xs w-full lg:w-64">
                <span class="text-[14px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 select-none">Tanggal</span>
                <div class="flex-1">
                    <x-date-picker wire:model.live="date" type="date" variant="ios" placeholder="Pilih tanggal..." />
                </div>
            </label>

            <flux:button href="{{ route('reports.print-daily-recap', ['date' => $date]) }}" target="_blank" variant="primary" icon="printer" class="w-full sm:w-auto">
                Cetak
            </flux:button>
        </div>
    </div>

    <!-- Print Header (Only visible when printing) -->
    <div class="hidden print:block mb-6 text-center">
        <h1 class="text-2xl font-bold text-black uppercase">REKAPITULASI PENJUALAN HARIAN</h1>
        <h2 class="text-lg font-bold text-black uppercase mt-1">UD. CITRA MAJU BERSAMA</h2>
        <div class="mt-4 text-right text-black font-medium">
            Hari/tgl : {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/5 shadow-xs print:shadow-none print:border-none overflow-hidden print:overflow-visible">
        @if($this->categories->isEmpty())
            <div class="py-16 flex flex-col items-center justify-center text-center">
                <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-4 mb-4">
                    <flux:icon.document-text class="w-8 h-8 text-zinc-500 dark:text-zinc-400" />
                </div>
                <flux:heading size="lg">Tidak Ada Data</flux:heading>
                <flux:text class="mt-2 max-w-sm text-zinc-500 dark:text-zinc-400">
                    Tidak ditemukan data transaksi penjualan (OrderBook) pada tanggal {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}.
                </flux:text>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-800/50 dark:text-zinc-300 border-b border-zinc-200 dark:border-white/10">
                        <tr>
                            <th rowspan="2" class="px-4 py-3 font-semibold border-r border-zinc-200 dark:border-white/10 align-middle">
                                Karyawan
                            </th>
                            @foreach($this->categories as $category)
                                <th colspan="{{ $category->items->count() }}" class="px-4 py-2 font-semibold text-center border-r border-zinc-200 dark:border-white/10 last:border-r-0">
                                    {{ $category->name }}
                                </th>
                            @endforeach
                        </tr>
                        <tr class="border-t border-zinc-200 dark:border-white/10">
                            @foreach($this->categories as $category)
                                @foreach($category->items as $item)
                                    <th class="px-3 py-2 font-medium border-r border-zinc-200 dark:border-white/10 last:border-r-0 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="text-zinc-900 dark:text-white">{{ $item->name }}</span>
                                            <span class="text-[10px] text-zinc-500 font-normal">{{ $item->weight >= 1000 ? ($item->weight/1000).'kg' : $item->weight.'gr' }}</span>
                                        </div>
                                    </th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                        @php
                            // Array to keep track of column totals
                            $columnTotals = [];
                            foreach($this->categories as $category) {
                                foreach($category->items as $item) {
                                    $columnTotals[$item->id] = 0;
                                }
                            }
                        @endphp
                        
                        @foreach($this->reportData as $row)
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white border-r border-zinc-200 dark:border-white/10 bg-white dark:bg-zinc-900 group-hover:bg-zinc-50 dark:group-hover:bg-zinc-800/50">
                                    <div class="flex items-center gap-2">
                                        <flux:avatar :src="$row['employee']->avatarUrl()" :name="$row['employee']->name" :initials="$row['employee']->initials()" size="xs" />
                                        {{ $row['employee']->name }}
                                    </div>
                                </td>
                                
                                @foreach($this->categories as $category)
                                    @foreach($category->items as $item)
                                        @php
                                            $qty = $row['items'][$item->id] ?? 0;
                                            $columnTotals[$item->id] += $qty;
                                        @endphp
                                        <td class="px-3 py-3 text-center border-r border-zinc-200 dark:border-white/10 last:border-r-0 {{ $qty > 0 ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-300 dark:text-zinc-600' }}">
                                            {{ $qty > 0 ? number_format($qty) : '-' }}
                                        </td>
                                    @endforeach
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-white/10">
                        <tr>
                            <td class="px-4 py-3 font-bold text-zinc-900 dark:text-white border-r border-zinc-200 dark:border-white/10 text-right bg-zinc-50 dark:bg-zinc-800/50">
                                TOTAL
                            </td>
                            @foreach($this->categories as $category)
                                @foreach($category->items as $item)
                                    @php
                                        $totalQty = $columnTotals[$item->id] ?? 0;
                                    @endphp
                                    <td class="px-3 py-3 text-center font-bold text-red-600 dark:text-red-400 border-r border-zinc-200 dark:border-white/10 last:border-r-0 text-[15px]">
                                        {{ $totalQty > 0 ? number_format($totalQty) : '-' }}
                                    </td>
                                @endforeach
                            @endforeach
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
