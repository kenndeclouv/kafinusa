<div class="p-2 sm:p-4 print:p-0 max-w-[1400px] mx-auto">
    {{-- Print Action Bar (hidden on print) --}}
    <div
        class="no-print flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4 mb-6 p-4 bg-zinc-50 dark:bg-white/5 rounded-2xl border border-zinc-200 dark:border-white/10 shadow-xs">

        {{-- Left: Navigation & Printing Actions --}}
        <div class="flex flex-wrap items-center gap-2" x-data="{
            isDownloading: false,
            downloadImage() {
                this.isDownloading = true;
                if (typeof html2canvas === 'undefined') {
                    let script = document.createElement('script');
                    script.src = '{{ asset("js/html2canvas-pro.min.js") }}';
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

        {{-- Center: Retur Input & Stock Sync Controls --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white dark:bg-zinc-800/80 px-3 py-1.5 rounded-xl border border-zinc-200 dark:border-white/10 shadow-xs">
                <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300 whitespace-nowrap">Tgl Retur Masuk:</span>
                <input type="date" wire:model.live="returnDate" class="text-xs bg-transparent border-none p-0 focus:ring-0 text-zinc-800 dark:text-white font-medium cursor-pointer" />
            </div>

            <flux:button wire:click="saveAllReturns" variant="primary" icon="check" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 border-emerald-600 text-white">
                Simpan Retur
            </flux:button>

            <div wire:loading wire:target="returns, saveAllReturns, returnDate" class="text-xs text-amber-600 dark:text-amber-400 font-medium flex items-center gap-1.5">
                <flux:icon.arrow-path class="w-3.5 h-3.5 animate-spin" />
                <span>Menyimpan retur & stok...</span>
            </div>
        </div>

        <span class="text-xs text-center xl:text-right text-zinc-500 dark:text-zinc-400 w-full xl:w-auto">
            Input retur otomatis menambah stok gudang & mutasi masuk.
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
                        if (!$planItem->orderItem || !$planItem->orderItem->item) continue;
                        if ($planItem->batch_number == $currentBatch && $planItem->quantity > 0) {
                            $batchTonase += ($planItem->quantity * ($planItem->orderItem->item->weight ?? 0));
                        }
                    }
                    $batchRecord = $this->plan->batches->where('batch_number', $currentBatch)->first();
                    $isBatchShipped = $batchRecord && $batchRecord->status === 'shipped';
                @endphp
                <div class="w-full pt-4 print:pt-0 text-black print-half-width @if ($currentBatch > 1) mt-8 print:mt-12 @endif"
                    style="page-break-inside: avoid; padding-right: 15px;">
                    {{-- Document Header --}}
                    <div class="mb-2 border-b-2 border-black pb-2 bg-white text-black print:mb-2 print:pb-1">
                        <div class="text-center mb-2">
                            <h1 class="text-base font-bold uppercase tracking-widest italic inline-block">
                                Daftar Pengambilan Barang Pasar
                                {{ $this->totalBatches > 1 ? "(Muatan $currentBatch)" : '' }}
                            </h1>
                            @if($isBatchShipped)
                                <span class="no-print ml-2 inline-flex items-center gap-1 text-[10px] font-sans not-italic font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    ✓ Terpotong Stok (Gudang: {{ $batchRecord->warehouse?->name ?? 'Pusat' }})
                                </span>
                            @else
                                <span class="no-print ml-2 inline-flex items-center gap-1 text-[10px] font-sans not-italic font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                                    Belum Dikirim (Draft)
                                </span>
                            @endif
                        </div>
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
                                    <th colspan="2"
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
                                        style="border: 1px solid #000; padding: 1px; text-align: center; width: 28px; font-size: 9px;">
                                        G</th>
                                    <th
                                        style="border: 1px solid #000; padding: 1px; text-align: center; width: 28px; font-size: 9px;">
                                        S</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $id => $item)
                                    @php
                                        $muatan = !empty($item['batches'][$currentBatch]) ? $item['batches'][$currentBatch] : 0;
                                        $ret = (int)($this->returns[$id][$currentBatch] ?? 0);
                                        $gt = $muatan - $ret;
                                    @endphp
                                    <tr>
                                        <td wire:click="toggleCheck({{ $id }}, {{ $currentBatch }}, 'G')" style="border: 1px solid #000; padding: 1px 2px; text-align: center; cursor: pointer; font-size: 13px; font-weight: 800; user-select: none;" class="hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                            {!! !empty($this->checkG[$id][$currentBatch]) ? 'X' : '&nbsp;' !!}
                                        </td>
                                        <td wire:click="toggleCheck({{ $id }}, {{ $currentBatch }}, 'S')" style="border: 1px solid #000; padding: 1px 2px; text-align: center; cursor: pointer; font-size: 13px; font-weight: 800; user-select: none;" class="hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                            {!! !empty($this->checkS[$id][$currentBatch]) ? 'X' : '&nbsp;' !!}
                                        </td>
                                        <td style="border: 1px solid #000; padding: 1px 4px; font-weight: 500;">
                                            {{ $item['category_name'] }} {{ $item['name'] }}</td>
                                        <td
                                            style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 11px;">
                                            {{ $muatan > 0 ? $muatan : '' }}
                                        </td>
                                        <td style="border: 1px solid #000; padding: 0; text-align: center;">
                                            @if($muatan > 0)
                                                <input type="number" wire:model.live.debounce.500ms="returns.{{ $id }}.{{ $currentBatch }}" class="w-full text-center border-none p-0 m-0 text-[11px] h-full focus:ring-0 bg-transparent print:bg-transparent print:appearance-auto" min="0" max="{{ $muatan }}" />
                                            @endif
                                        </td>
                                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 11px;">
                                            {{ $muatan > 0 ? $gt : '' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer Signatures --}}
                    <div class="mt-4 flex flex-col text-xs bg-white text-black" style="border: 1px solid #000;">
                        <div class="flex" style="border-bottom: 1px solid #000;">
                            <div class="w-[120px] p-1 font-bold italic text-center uppercase"
                                style="border-right: 1px solid #000;">Checker Utama</div>
                            <div class="flex-1 p-1"></div>
                        </div>
                        <div class="flex" style="border-bottom: 1px solid #000;">
                            <div class="w-[120px] p-1 font-bold italic text-center uppercase"
                                style="border-right: 1px solid #000;">Checker Cabang</div>
                            <div class="flex-1 p-1"></div>
                        </div>
                        <div class="flex" style="border-bottom: 1px solid #000;">
                            <div class="w-[120px] p-1 font-bold italic text-center uppercase"
                                style="border-right: 1px solid #000;">Admin </div>
                            <div class="flex-1 p-1"></div>
                        </div>
                        <div class="flex" style="border-bottom: 1px solid #000;">
                            <div class="w-[120px] p-1 font-bold italic text-center uppercase"
                                style="border-right: 1px solid #000;">SALES</div>
                            <div class="flex-1 p-1 font-semibold text-center uppercase">
                                {{ $orderBook->employee->name }}</div>
                        </div>
                        <div class="flex" style="border-bottom: 1px solid #000;">
                            <div class="w-[120px] p-1 font-bold italic text-center uppercase"
                                style="border-right: 1px solid #000;">TONASE</div>
                            <div class="flex-1 p-1 bg-white text-black font-semibold text-center">
                                {{ formatWeight($batchTonase) }}</div>
                        </div>
                        <div class="flex">
                            <div class="w-[120px] p-1 font-bold italic text-center uppercase"
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
