<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 no-print">
        <div>
            <flux:heading size="xl" level="1">Daftar Produksi Harian</flux:heading>
            <flux:description>Laporan matriks hasil pencatatan produksi barang per sesi.</flux:description>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="w-full sm:w-48">
                <x-searchable-select wire:model.live="warehouse_id" :options="$this->warehouses" variant="ios" placeholder="Pilih Gudang" />
            </div>

            <div class="w-full sm:w-48">
                <x-date-picker wire:model.live="date" type="date" variant="ios" placeholder="Pilih tanggal..." />
            </div>

            <flux:button href="{{ route('reports.print-daily-production', ['date' => $date, 'warehouse_id' => $warehouse_id]) }}" target="_blank" variant="primary" icon="printer" class="w-full sm:w-auto">
                Cetak
            </flux:button>
        </div>
    </div>

    <!-- Print Header (Only visible when printing) -->
    <div class="hidden print:block mb-6 text-center">
        <h1 class="text-2xl font-bold text-black uppercase">DAFTAR PRODUKSI HARIAN</h1>
        <h2 class="text-lg font-bold text-black uppercase mt-1">UD. CITRA MAJU BERSAMA</h2>
        <div class="mt-4 text-right text-black font-medium">
            HARI/TANGGAL : {{ \Carbon\Carbon::parse($date)->translatedFormat('l / d-m-Y') }}
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/5 shadow-xs print:shadow-none print:border-none overflow-hidden print:overflow-visible">
        @if($this->reportData->isEmpty())
            <div class="py-16 flex flex-col items-center justify-center text-center">
                <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-4 mb-4">
                    <flux:icon.clipboard-document-check class="w-8 h-8 text-zinc-500 dark:text-zinc-400" />
                </div>
                <flux:heading size="lg">Tidak Ada Data Produksi</flux:heading>
                <flux:text class="mt-2 max-w-sm text-zinc-500 dark:text-zinc-400">
                    Tidak ada pencatatan produksi di sistem pada tanggal {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }} untuk gudang tersebut.
                </flux:text>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead class="text-xs text-zinc-700 uppercase bg-zinc-50 dark:bg-zinc-800/50 dark:text-zinc-300 border-b border-zinc-200 dark:border-white/10">
                        <tr>
                            <th rowspan="2" class="px-4 py-3 font-semibold border-r border-zinc-200 dark:border-white/10 align-middle text-center sticky left-0 z-10 bg-zinc-50 dark:bg-zinc-800/50">
                                NO.
                            </th>
                            @foreach($this->categories as $category)
                                @if($category->items->count() > 0)
                                    <th colspan="{{ $category->items->count() }}" class="px-4 py-2 font-bold text-center border-r border-zinc-200 dark:border-white/10 bg-zinc-100/50 dark:bg-zinc-800/80">
                                        {{ $category->name }}
                                    </th>
                                @endif
                            @endforeach
                            <th rowspan="2" class="px-4 py-3 font-semibold border-r border-zinc-200 dark:border-white/10 align-middle text-center min-w-[120px]">
                                TTL BAHAN
                            </th>
                            <th rowspan="2" class="px-4 py-3 font-semibold align-middle text-center">
                                KETERANGAN
                            </th>
                        </tr>
                        <tr class="border-t border-zinc-200 dark:border-white/10">
                            @foreach($this->categories as $category)
                                @foreach($category->items as $item)
                                    <th class="px-3 py-2 font-medium border-r border-zinc-200 dark:border-white/10 text-center min-w-[60px]">
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
                            $totalBahan = 0;
                        @endphp
                        
                        @foreach($this->reportData as $row)
                            @php
                                $totalBahan += $row['raw_quantity'];
                            @endphp
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="px-4 py-2 text-zinc-900 dark:text-white border-r border-zinc-200 dark:border-white/10 sticky left-0 bg-white dark:bg-zinc-900 group-hover:bg-zinc-50 dark:group-hover:bg-zinc-800/50">
                                    <div class="flex items-center gap-3">
                                        <span class="font-bold w-4 text-center">{{ $row['no'] }}</span>
                                        <div class="flex items-center gap-2">
                                            <flux:avatar :src="$row['user']->avatarUrl()" :name="$row['user']->name" :initials="$row['user']->initials()" size="xs" />
                                            <span class="text-xs text-zinc-500">{{ $row['user']->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                
                                @foreach($this->categories as $category)
                                    @foreach($category->items as $item)
                                        @php
                                            $qty = $row['items'][$item->id] ?? 0;
                                            $columnTotals[$item->id] += $qty;
                                        @endphp
                                        <td class="px-3 py-3 text-center border-r border-zinc-200 dark:border-white/10 {{ $qty > 0 ? 'text-zinc-900 dark:text-zinc-100 font-semibold' : 'text-zinc-300 dark:text-zinc-700' }}">
                                            {{ $qty > 0 ? number_format($qty) : '' }}
                                        </td>
                                    @endforeach
                                @endforeach

                                <td class="px-4 py-3 text-center border-r border-zinc-200 dark:border-white/10 font-bold text-red-600 dark:text-red-400">
                                    {{ number_format($row['raw_quantity']) }}
                                </td>
                                
                                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400 text-sm max-w-[200px] truncate" title="{{ $row['notes'] }}">
                                    {{ $row['notes'] ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-white/10">
                        <tr>
                            <td class="px-4 py-4 font-bold text-zinc-900 dark:text-white border-r border-zinc-200 dark:border-white/10 text-right sticky left-0 bg-zinc-50 dark:bg-zinc-800/50">
                                TOTAL
                            </td>
                            @foreach($this->categories as $category)
                                @foreach($category->items as $item)
                                    @php
                                        $totalQty = $columnTotals[$item->id] ?? 0;
                                    @endphp
                                    <td class="px-3 py-4 text-center font-bold border-r border-zinc-200 dark:border-white/10 text-[15px] {{ $totalQty > 0 ? 'text-zinc-900 dark:text-white' : 'text-zinc-400 dark:text-zinc-600' }}">
                                        {{ $totalQty > 0 ? number_format($totalQty) : '-' }}
                                    </td>
                                @endforeach
                            @endforeach
                            <td class="px-4 py-4 text-center font-bold text-red-600 dark:text-red-400 border-r border-zinc-200 dark:border-white/10 text-[15px]">
                                {{ number_format($totalBahan) }}
                            </td>
                            <td class="px-4 py-4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
