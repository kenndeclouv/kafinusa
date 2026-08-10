<div class="p-2 sm:p-4 print:p-0 max-w-[1400px] mx-auto">
    {{-- Print Action Bar (hidden on print) --}}
    <div
        class="no-print flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 p-4 bg-zinc-50 dark:bg-white/5 rounded-2xl border border-zinc-200 dark:border-white/10">

        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <flux:button href="{{ route('order-books.shipments', $orderBook) }}" wire:navigate variant="outline"
                icon="arrow-left" class="w-full sm:w-auto">
                Kembali & Edit
            </flux:button>
            <flux:button onclick="window.print()" variant="primary" icon="printer" class="w-full sm:w-auto">
                Cetak
            </flux:button>
        </div>

        <span class="text-sm text-center sm:text-right text-zinc-500 dark:text-zinc-400 w-full sm:w-auto">
            Atau tekan <kbd class="bg-zinc-200 dark:bg-zinc-700 px-1.5 py-0.5 rounded text-xs">Ctrl+P</kbd> untuk cetak
            / simpan PDF
        </span>
    </div>

    {{-- The "Paper" Container for printing & mobile scrolling --}}
    <div class="w-full overflow-x-auto bg-white print:overflow-visible flex flex-col gap-8 print:block print:gap-0">
        @if ($plan)
            @php
                $items = $this->itemRows;
                $half = ceil($items->count() / 2);
                $leftItems = $items->take($half)->values();
                $rightItems = $items->skip($half)->values();
            @endphp

            @for ($currentBatch = 1; $currentBatch <= $this->totalBatches; $currentBatch++)
                @php
                    $batchTonase = 0;
                    foreach ($this->plan->items as $planItem) {
                        if ($planItem->batch_number == $currentBatch && $planItem->quantity > 0) {
                            $batchTonase += $planItem->quantity * $planItem->orderItem->item->weight;
                        }
                    }
                @endphp
                <div
                    class="min-w-[950px] pt-4 print:pt-0 text-black @if ($currentBatch > 1) print:break-before-page @endif">
                    {{-- Document Header --}}
                    <div
                        class="flex items-center justify-between mb-4 border-b-2 border-black pb-2 bg-white text-black print:mb-2 print:pb-1">
                        <div>
                            <div class="text-sm font-semibold tracking-widest uppercase">PASAR :
                                {{ $orderBook->market->name }}</div>
                        </div>
                        <h1 class="text-xl font-bold uppercase tracking-widest italic text-center flex-1">
                            Daftar Pengambilan Barang Pasar
                            {{ $this->totalBatches > 1 ? "(Muatan $currentBatch)" : '' }}
                        </h1>
                        <div class="text-sm font-semibold">
                            Hari / Tgl : {{ $orderBook->book_date->translatedFormat('l / d-m-Y') }}
                        </div>
                    </div>

                    {{-- Main Layout --}}
                    <div class="flex flex-row gap-4 w-full text-black bg-white">
                        {{-- Left Column --}}
                        <div class="flex-1">
                            <table class="w-full border-collapse text-[10px]" style="border: 2px solid #000;">
                                <thead>
                                    <tr style="background: #e5e7eb;">
                                        <th colspan="3"
                                            style="border: 1px solid #000; padding: 2px; font-weight: bold;">CEK</th>
                                        <th rowspan="2"
                                            style="border: 1px solid #000; padding: 4px; font-weight: bold; font-style: italic;">
                                            NAMA ITEM</th>
                                        <th rowspan="2"
                                            style="border: 1px solid #000; padding: 2px; text-align: center; font-weight: bold; width: 60px;">
                                            MUATAN</th>
                                        <th rowspan="2"
                                            style="border: 1px solid #000; padding: 2px; text-align: center; font-weight: bold; width: 60px;">
                                            RETUR</th>
                                        <th rowspan="2"
                                            style="border: 1px solid #000; padding: 2px; width: 30px; text-align: center; font-weight: bold;">
                                            G.T</th>
                                    </tr>
                                    <tr style="background: #e5e7eb;">
                                        <th
                                            style="border: 1px solid #000; padding: 1px; text-align: center; width: 22px; font-size: 8px;">
                                            G</th>
                                        <th
                                            style="border: 1px solid #000; padding: 1px; text-align: center; width: 22px; font-size: 8px;">
                                            T</th>
                                        <th
                                            style="border: 1px solid #000; padding: 1px; text-align: center; width: 22px; font-size: 8px;">
                                            S</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($leftItems as $item)
                                        <tr>
                                            <td style="border: 1px solid #000; padding: 2px; text-align: center;"><input
                                                    type="checkbox" class="print:appearance-auto w-3 h-3"></td>
                                            <td style="border: 1px solid #000; padding: 2px; text-align: center;"><input
                                                    type="checkbox" class="print:appearance-auto w-3 h-3"></td>
                                            <td style="border: 1px solid #000; padding: 2px; text-align: center;"><input
                                                    type="checkbox" class="print:appearance-auto w-3 h-3"></td>
                                            <td style="border: 1px solid #000; padding: 3px; font-weight: 500;">
                                                {{ $item['name'] }}</td>
                                            <td
                                                style="border: 1px solid #000; padding: 3px; text-align: center; font-weight: bold; font-size: 11px;">
                                                {{ !empty($item['batches'][$currentBatch]) ? $item['batches'][$currentBatch] : '' }}
                                            </td>
                                            <td style="border: 1px solid #000; padding: 3px;"></td>
                                            <td style="border: 1px solid #000; padding: 3px;"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Right Column --}}
                        <div class="flex-1">
                            <table class="w-full border-collapse text-[10px]" style="border: 2px solid #000;">
                                <thead>
                                    <tr style="background: #e5e7eb;">
                                        <th colspan="3"
                                            style="border: 1px solid #000; padding: 2px; font-weight: bold;">CEK</th>
                                        <th rowspan="2"
                                            style="border: 1px solid #000; padding: 4px; font-weight: bold; font-style: italic;">
                                            NAMA ITEM</th>
                                        <th rowspan="2"
                                            style="border: 1px solid #000; padding: 2px; text-align: center; font-weight: bold; width: 60px;">
                                            MUATAN</th>
                                        <th rowspan="2"
                                            style="border: 1px solid #000; padding: 2px; text-align: center; font-weight: bold; width: 60px;">
                                            RETUR</th>
                                        <th rowspan="2"
                                            style="border: 1px solid #000; padding: 2px; width: 30px; text-align: center; font-weight: bold;">
                                            G.T</th>
                                    </tr>
                                    <tr style="background: #e5e7eb;">
                                        <th
                                            style="border: 1px solid #000; padding: 1px; text-align: center; width: 22px; font-size: 8px;">
                                            G</th>
                                        <th
                                            style="border: 1px solid #000; padding: 1px; text-align: center; width: 22px; font-size: 8px;">
                                            T</th>
                                        <th
                                            style="border: 1px solid #000; padding: 1px; text-align: center; width: 22px; font-size: 8px;">
                                            S</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rightItems as $item)
                                        <tr>
                                            <td style="border: 1px solid #000; padding: 2px; text-align: center;"><input
                                                    type="checkbox" class="print:appearance-auto w-3 h-3"></td>
                                            <td style="border: 1px solid #000; padding: 2px; text-align: center;"><input
                                                    type="checkbox" class="print:appearance-auto w-3 h-3"></td>
                                            <td style="border: 1px solid #000; padding: 2px; text-align: center;"><input
                                                    type="checkbox" class="print:appearance-auto w-3 h-3"></td>
                                            <td style="border: 1px solid #000; padding: 3px; font-weight: 500;">
                                                {{ $item['name'] }}</td>
                                            <td
                                                style="border: 1px solid #000; padding: 3px; text-align: center; font-weight: bold; font-size: 11px;">
                                                {{ !empty($item['batches'][$currentBatch]) ? $item['batches'][$currentBatch] : '' }}
                                            </td>
                                            <td style="border: 1px solid #000; padding: 3px;"></td>
                                            <td style="border: 1px solid #000; padding: 3px;"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Footer Signatures --}}
                    <div class="mt-4 grid grid-cols-2 gap-4 text-xs bg-white text-black">
                        {{-- Left Signatures --}}
                        <div class="flex flex-col" style="border: 1px solid #000;">
                            <div class="flex" style="border-bottom: 1px solid #000;">
                                <div class="w-[120px] p-1 font-semibold italic text-center"
                                    style="border-right: 1px solid #000;">Checker Utama</div>
                                <div class="flex-1 p-1"></div>
                            </div>
                            <div class="flex" style="border-bottom: 1px solid #000;">
                                <div class="w-[120px] p-1 font-semibold italic text-center"
                                    style="border-right: 1px solid #000;">Checker Cabang</div>
                                <div class="flex-1 p-1"></div>
                            </div>
                            <div class="flex">
                                <div class="w-[120px] p-1 font-semibold italic text-center"
                                    style="border-right: 1px solid #000;">Admin </div>
                                <div class="flex-1 p-1"></div>
                            </div>
                        </div>

                        {{-- Right Signatures --}}
                        <div class="flex flex-col" style="border: 1px solid #000;">
                            <div class="flex" style="border-bottom: 1px solid #000;">
                                <div class="w-[120px] p-1 font-bold italic text-center uppercase tracking-widest"
                                    style="border-right: 1px solid #000;">SALES</div>
                                <div class="flex-1 p-1 font-semibold text-center uppercase">
                                    {{ $orderBook->employee->name }}</div>
                            </div>
                            <div class="flex bg-[#3f3f46] text-white" style="border-bottom: 1px solid #000;">
                                <div class="w-[120px] p-1 font-bold italic text-center uppercase tracking-widest"
                                    style="border-right: 1px solid #fff;">TONASE</div>
                                <div class="flex-1 p-1 bg-white text-black font-semibold text-center">
                                    {{ number_format($batchTonase / 1000, 2, ',', '.') }} Kg</div>
                            </div>
                            <div class="flex">
                                <div class="w-[120px] p-1 font-bold italic text-center uppercase tracking-widest"
                                    style="border-right: 1px solid #000;">DRIVER</div>
                                <div class="flex-1 p-1 font-semibold text-center uppercase"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Print Footer --}}
                    <div class="mt-4 pt-2" style="border-top: 1px solid #d1d5db; font-size: 11px; color: #9ca3af;">
                        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }}
                    </div>
                </div>
            @endfor
        @else
            <div class="text-center py-12 text-zinc-500 w-full min-w-[950px]">
                Belum ada rencana muatan. <a href="{{ route('order-books.shipments', $orderBook) }}"
                    class="text-emerald-600 underline">Buat sekarang</a>.
            </div>
        @endif
    </div>
</div>

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            font-size: 11px;
        }

        @page {
            size: landscape;
            margin: 8mm;
        }

        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
        }
    }
</style>
