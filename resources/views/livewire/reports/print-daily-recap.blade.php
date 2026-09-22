<div class="p-2 sm:p-4 print:p-0 max-w-[1400px] mx-auto">
    {{-- Print Action Bar (hidden on print) --}}
    <div
        class="no-print flex flex-col lg:flex-row items-center justify-between gap-4 mb-6 p-4 bg-zinc-50 dark:bg-white/5 rounded-2xl border border-zinc-200 dark:border-white/10">

        <div class="flex flex-col lg:flex-row gap-2 w-full lg:w-auto" x-data="{
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
                    linkDl.download = 'rekap-penjualan-{{ $date }}.png';
                    linkDl.href = canvas.toDataURL('image/png');
                    linkDl.click();
                    this.isDownloading = false;
                }).catch(err => {
                    console.error('Error generating image', err);
                    this.isDownloading = false;
                });
            }
        }">

            <flux:button href="{{ route('reports.daily-recap', ['date' => $date]) }}" wire:navigate variant="outline"
                icon="arrow-left" class="w-full lg:w-auto">
                Kembali
            </flux:button>
            <flux:button onclick="window.print()" variant="primary" icon="printer"
                class="w-full lg:w-auto hidden lg:flex">
                Cetak
            </flux:button>

            <flux:dropdown>
                <flux:button variant="outline" icon="ellipsis-vertical" class="w-full lg:w-auto px-4" />
                <flux:menu>
                    <flux:menu.item icon="photo" x-on:click="downloadImage()">
                        <span x-show="!isDownloading">Download Image (High-Res)</span>
                        <span x-show="isDownloading">Memproses...</span>
                    </flux:menu.item>
                    <flux:menu.item icon="document-arrow-down" onclick="window.print()">Download PDF</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>

        <span class="text-sm text-center lg:text-right text-zinc-500 dark:text-zinc-400 w-full lg:w-auto">
            Atau tekan <kbd class="bg-zinc-200 dark:bg-zinc-700 px-1.5 py-0.5 rounded text-xs">Ctrl+P</kbd> untuk cetak
            / simpan PDF.<br>
            Gunakan Layout: <strong class="text-zinc-900 dark:text-zinc-200">Landscape</strong> pada pengaturan printer.
        </span>
    </div>

    {{-- Report Content --}}
    <div id="print-container" class="w-full overflow-x-auto bg-white print:overflow-visible">
        <div class="min-w-[950px] pt-4 print:pt-0 text-black">
            <!-- Print Header -->
            <div class="mb-4 text-black">
                <div class="text-center font-bold leading-tight">
                    <h1 class="text-xl uppercase">REKAPITULASI PENJUALAN HARIAN</h1>
                    <h2 class="text-lg uppercase">UD. CITRA MAJU BERSAMA</h2>
                </div>
                <div class="border-b-[2px] border-black mt-2 w-full flex justify-end pb-1">
                    <div class="text-sm font-semibold">
                        Hari/tgl : {{ \Carbon\Carbon::parse($date)->translatedFormat('l / d-m-Y') }}
                    </div>
                </div>
            </div>

            @if ($this->categories->isEmpty())
                <div
                    class="py-16 flex flex-col items-center justify-center text-center border border-zinc-200 rounded-2xl">
                    <div class="rounded-full bg-zinc-100 p-4 mb-4">
                        <flux:icon.document-text class="w-8 h-8 text-zinc-500" />
                    </div>
                    <h3 class="text-lg font-semibold">Tidak Ada Data</h3>
                    <p class="mt-2 max-w-sm text-zinc-500">
                        Tidak ditemukan data transaksi penjualan (OrderBook) pada tanggal
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}.
                    </p>
                </div>
            @else
                <table class="w-full border-collapse text-[10px]" style="border: 2px solid #000;">
                    <thead>
                        <tr style="background: #e5e7eb;">
                            <th rowspan="2"
                                style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold;">
                                Karyawan
                            </th>
                            @foreach ($this->categories as $category)
                                <th colspan="{{ $category->items->count() }}"
                                    style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                                    {{ $category->name }}
                                </th>
                            @endforeach
                        </tr>
                        <tr style="background: #e5e7eb;">
                            @foreach ($this->categories as $category)
                                @foreach ($category->items as $item)
                                    <th
                                        style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 9px; width: 35px;">
                                        <div class="flex flex-col items-center gap-0.5">
                                            <span>{{ $item->name }}</span>
                                            <span
                                                class="font-normal">{{ $item->weight >= 1000 ? $item->weight / 1000 . 'kg' : $item->weight . 'gr' }}</span>
                                        </div>
                                    </th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Array to keep track of column totals
                            $columnTotals = [];
                            foreach ($this->categories as $category) {
                                foreach ($category->items as $item) {
                                    $columnTotals[$item->id] = 0;
                                }
                            }
                        @endphp

                        @foreach ($this->reportData as $row)
                            <tr>
                                <td style="border: 1px solid #000; padding: 1px 4px; font-weight: 500;">
                                    {{ $row['employee']->name }}
                                </td>

                                @foreach ($this->categories as $category)
                                    @foreach ($category->items as $item)
                                        @php
                                            $qty = $row['items'][$item->id] ?? 0;
                                            $columnTotals[$item->id] += $qty;
                                        @endphp
                                        <td
                                            style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 11px;">
                                            {{ $qty > 0 ? number_format($qty) : '' }}
                                        </td>
                                    @endforeach
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #e5e7eb;">
                            <td
                                style="border: 1px solid #000; padding: 1px 4px; font-weight: bold; text-align: right; text-transform: uppercase;">
                                TOTAL
                            </td>
                            @foreach ($this->categories as $category)
                                @foreach ($category->items as $item)
                                    @php
                                        $totalQty = $columnTotals[$item->id] ?? 0;
                                    @endphp
                                    <td
                                        style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 11px;">
                                        {{ $totalQty > 0 ? number_format($totalQty) : '' }}
                                    </td>
                                @endforeach
                            @endforeach
                        </tr>
                    </tfoot>
                </table>
            @endif

            {{-- Print Footer --}}
            <div class="mt-8 pt-4" style="border-top: 1px solid #d1d5db; font-size: 11px; color: #9ca3af;">
                Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }}
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            font-size: 11px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
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
