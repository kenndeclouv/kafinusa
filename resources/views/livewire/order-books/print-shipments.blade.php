<div class="p-2 sm:p-4 print:p-0 max-w-[1400px] mx-auto">
    {{-- Print Action Bar (hidden on print) --}}
    <div
        class="no-print flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 p-4 bg-zinc-50 dark:bg-white/5 rounded-2xl border border-zinc-200 dark:border-white/10">

        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto" x-data="{
            isDownloading: false,
            downloadImage() {
                this.isDownloading = true;
                if (typeof html2canvas === 'undefined') {
                    let script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/html2canvas-pro@2.3.9/dist/html2canvas-pro.min.js';
                    script.onload = () => this.capture();
                    document.head.appendChild(script);
                } else {
                    this.capture();
                }
            },
            capture() {
                const el = document.getElementById('print-container');
                const originalOverflow = el.style.overflow;
                el.style.overflow = 'visible';
        
                html2canvas(el, {
                    scale: 3,
                    backgroundColor: '#ffffff',
                    useCORS: true
                }).then(canvas => {
                    el.style.overflow = originalOverflow;
                    let linkDl = document.createElement('a');
                    linkDl.download = 'pengambilan-barang-{{ Str::slug($orderBook->market->name) }}-{{ $orderBook->book_date->format('dmY') }}.png';
                    linkDl.href = canvas.toDataURL('image/png');
                    linkDl.click();
                    this.isDownloading = false;
                }).catch(err => {
                    console.error('Error generating image', err);
                    this.isDownloading = false;
                });
            }
        }">

            <flux:button href="{{ route('order-books.shipments', $orderBook) }}" wire:navigate variant="outline"
                icon="arrow-left" class="w-full sm:w-auto">
                Kembali & Edit
            </flux:button>
            <flux:button onclick="window.print()" variant="primary" icon="printer"
                class="w-full sm:w-auto hidden sm:flex">
                Cetak
            </flux:button>

            <flux:dropdown>
                <flux:button variant="outline" icon="ellipsis-vertical" class="w-full sm:w-auto px-4" />
                <flux:menu>
                    <flux:menu.item icon="photo" x-on:click="downloadImage()">
                        <span x-show="!isDownloading">Download Image (High-Res)</span>
                        <span x-show="isDownloading">Memproses...</span>
                    </flux:menu.item>
                    <flux:menu.item icon="document-arrow-down" onclick="window.print()">Download PDF</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>

        <span class="text-sm text-center sm:text-right text-zinc-500 dark:text-zinc-400 w-full sm:w-auto">
            Atau tekan <kbd class="bg-zinc-200 dark:bg-zinc-700 px-1.5 py-0.5 rounded text-xs">Ctrl+P</kbd> untuk cetak
            / simpan PDF
        </span>
    </div>

    {{-- The "Paper" Container for printing & mobile scrolling --}}
    <div id="print-container"
        class="w-full overflow-x-auto bg-white print:overflow-visible flex flex-col gap-8 print:block print:gap-0">
        @if ($plan)
            @php
                $items = $this->itemRows;
            @endphp

            @for ($currentBatch = 1; $currentBatch <= $this->totalBatches; $currentBatch++)
                @php
                    $batchTonase = 0;
                    foreach ($this->plan->items as $planItem) {
                        if ($planItem->batch_number == $currentBatch && $planItem->quantity > 0) {
                            $batchTonase += $planItem->quantity * $planItem->orderItem?->item?->weight ?? 0;
                        }
                    }
                @endphp
                <div class="w-full pt-4 print:pt-0 text-black print-half-width @if ($currentBatch > 1) mt-8 print:mt-12 @endif"
                    style="page-break-inside: avoid; padding-right: 15px;">
                    {{-- Document Header --}}
                    <div class="mb-2 border-b-2 border-black pb-2 bg-white text-black print:mb-2 print:pb-1">
                        <h1 class="text-base font-bold uppercase tracking-widest italic text-center mb-2">
                            Daftar Pengambilan Barang Pasar
                            {{ $this->totalBatches > 1 ? "(Muatan $currentBatch)" : '' }}
                        </h1>
                        <div class="flex items-start justify-between">
                            <div class="text-xs font-semibold tracking-widest uppercase">
                                PASAR : {{ $orderBook->market->name }}
                            </div>
                            <div class="text-xs font-semibold text-right">
                                Hari / Tgl : {{ $orderBook->book_date->translatedFormat('l / d-m-Y') }}
                            </div>
                        </div>
                    </div>

                    {{-- Main Layout --}}
                    <div class="w-full text-black bg-white">
                        <table class="w-full border-collapse text-[10px]" style="border: 2px solid #000;">
                            <thead>
                                <tr style="background: #e5e7eb;">
                                    <th colspan="3"
                                        style="border: 1px solid #000; padding: 1px 2px; font-weight: bold;">CEK</th>
                                    <th rowspan="2"
                                        style="border: 1px solid #000; padding: 1px 4px; font-weight: bold; font-style: italic;">
                                        NAMA ITEM</th>
                                    <th rowspan="2"
                                        style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; width: 60px;">
                                        MUATAN</th>
                                    <th rowspan="2"
                                        style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; width: 60px;">
                                        RETUR</th>
                                    <th rowspan="2"
                                        style="border: 1px solid #000; padding: 1px 2px; width: 75px; text-align: center; font-weight: bold;">
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
                                @foreach ($items as $item)
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                            {{-- <input type="checkbox" class="print:appearance-auto w-3 h-3"> --}}
                                        </td>
                                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                            {{-- <input type="checkbox" class="print:appearance-auto w-3 h-3"> --}}
                                        </td>
                                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                            {{-- <input type="checkbox" class="print:appearance-auto w-3 h-3"> --}}
                                        </td>
                                        <td style="border: 1px solid #000; padding: 1px 2px; font-weight: 500;">
                                            {{ $item['category_name'] }} {{ $item['name'] }}</td>
                                        <td
                                            style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 11px;">
                                            {{ !empty($item['batches'][$currentBatch]) ? $item['batches'][$currentBatch] : '' }}
                                        </td>
                                        <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                                        <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer Signatures --}}
                    <div class="mt-4 flex flex-col text-xs bg-white text-black" style="border: 1px solid #000;">
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
                        <div class="flex" style="border-bottom: 1px solid #000;">
                            <div class="w-[120px] p-1 font-semibold italic text-center"
                                style="border-right: 1px solid #000;">Admin </div>
                            <div class="flex-1 p-1"></div>
                        </div>
                        <div class="flex" style="border-bottom: 1px solid #000;">
                            <div class="w-[120px] p-1 font-bold italic text-center uppercase tracking-widest"
                                style="border-right: 1px solid #000;">SALES</div>
                            <div class="flex-1 p-1 font-semibold text-center uppercase">
                                {{ $orderBook->employee->name }}</div>
                        </div>
                        <div class="flex" style="border-bottom: 1px solid #000;">
                            <div class="w-[120px] p-1 font-bold italic text-center uppercase tracking-widest"
                                style="border-right: 1px solid #000;">TONASE</div>
                            <div class="flex-1 p-1 bg-white text-black font-semibold text-center">
                                {{ number_format($batchTonase / 1000, 2, ',', '.') }} Kg</div>
                        </div>
                        <div class="flex">
                            <div class="w-[120px] p-1 font-bold italic text-center uppercase tracking-widest"
                                style="border-right: 1px solid #000;">DRIVER</div>
                            <div class="flex-1 p-1 font-semibold text-center uppercase"></div>
                        </div>
                    </div>

                    {{-- Print Footer --}}
                    {{-- <div class="mt-4 pt-2" style="border-top: 1px solid #d1d5db; font-size: 11px; color: #9ca3af;">
                        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }}
                    </div> --}}
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
            size: portrait;
            margin: 8mm;
        }

        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
        }

        .print-half-width {
            width: 50% !important;
        }
    }
</style>
