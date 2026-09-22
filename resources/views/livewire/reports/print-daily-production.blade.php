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
                    linkDl.download = 'produksi-harian-{{ $date }}.png';
                    linkDl.href = canvas.toDataURL('image/png');
                    linkDl.click();
                    this.isDownloading = false;
                }).catch(err => {
                    console.error('Error generating image', err);
                    this.isDownloading = false;
                });
            }
        }">

            <flux:button
                href="{{ route('reports.daily-production', ['date' => $date, 'warehouse_id' => $warehouse_id]) }}"
                wire:navigate variant="outline" icon="arrow-left" class="w-full lg:w-auto">
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
            <div
                class="flex items-center justify-between mb-4 border-b-2 border-black pb-2 bg-white text-black print:mb-2 print:pb-1">
                <div>
                    <div class="text-sm font-semibold tracking-widest uppercase">GUDANG :
                        {{ $this->warehouse_id ? \App\Models\Warehouse::find($this->warehouse_id)?->name ?? 'SEMUA GUDANG' : 'SEMUA GUDANG' }}
                    </div>
                </div>
                <h1 class="text-xl font-bold uppercase tracking-widest italic text-center flex-1">
                    DAFTAR PRODUKSI HARIAN
                </h1>
                <div class="text-sm font-semibold">
                    Hari / Tgl : {{ \Carbon\Carbon::parse($date)->translatedFormat('l / d-m-Y') }}
                </div>
            </div>

            @if ($this->reportData->isEmpty())
                <div
                    class="py-16 flex flex-col items-center justify-center text-center border border-zinc-200 rounded-2xl">
                    <div class="rounded-full bg-zinc-100 p-4 mb-4">
                        <flux:icon.clipboard-document-check class="w-8 h-8 text-zinc-500" />
                    </div>
                    <h3 class="text-lg font-semibold">Tidak Ada Data Produksi</h3>
                    <p class="mt-2 max-w-sm text-zinc-500">
                        Tidak ada pencatatan produksi di sistem pada tanggal
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }} untuk gudang tersebut.
                    </p>
                </div>
            @else
                <table class="w-full border-collapse text-[10px]" style="border: 2px solid #000;">
                    <thead>
                        <tr style="background: #e5e7eb;">
                            <th rowspan="2"
                                style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold; width: 40px;">
                                NO.
                            </th>
                            @foreach ($this->categories as $category)
                                @if ($category->items->count() > 0)
                                    <th colspan="{{ $category->items->count() }}"
                                        style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                                        {{ $category->name }}
                                    </th>
                                @endif
                            @endforeach
                            <th rowspan="2"
                                style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold; min-width: 60px;">
                                TTL BAHAN
                            </th>
                            <th rowspan="2"
                                style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold;">
                                KETERANGAN
                            </th>
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
                            $totalBahan = 0;
                        @endphp

                        @foreach ($this->reportData as $row)
                            @php
                                $totalBahan += $row['raw_quantity'];
                            @endphp
                            <tr>
                                <td
                                    style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                                    {{ $row['no'] }}
                                    <div style="font-size: 9px; font-weight: normal; margin-top: 2px;">
                                        {{ $row['user']->name }}</div>
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

                                <td
                                    style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                                    {{ number_format($row['raw_quantity']) }}
                                </td>

                                <td style="border: 1px solid #000; padding: 1px 4px; max-width: 250px;">
                                    {{ $row['notes'] ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #e5e7eb;">
                            <td
                                style="border: 1px solid #000; padding: 1px 4px; text-align: right; font-weight: bold; text-transform: uppercase;">
                                TOTAL
                            </td>
                            @foreach ($this->categories as $category)
                                @foreach ($category->items as $item)
                                    @php
                                        $totalQty = $columnTotals[$item->id] ?? 0;
                                    @endphp
                                    <td
                                        style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 11px;">
                                        {{ $totalQty > 0 ? number_format($totalQty) : '-' }}
                                    </td>
                                @endforeach
                            @endforeach
                            <td
                                style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                                {{ number_format($totalBahan) }}
                            </td>
                            <td style="border: 1px solid #000; padding: 1px 4px;"></td>
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
