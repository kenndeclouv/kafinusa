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
                    linkDl.download = 'sirkulasi-stok-{{ $month }}-{{ $year }}.png';
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
                href="{{ route('reports.stock-circulation', ['month' => $month, 'year' => $year, 'warehouse_id' => $warehouse_id, 'item_category_id' => $item_category_id]) }}"
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
                    SIRKULASI
                    {{ $this->item_category_id ? strtoupper($this->categories[$this->item_category_id] ?? 'STOK') : 'STOK' }}
                    HARIAN
                </h1>
                <div class="text-sm font-semibold">
                    Bulan : {{ \Carbon\Carbon::create()->month((int) $month)->translatedFormat('F') }}
                    {{ $year }}
                </div>
            </div>

            @if (!$this->circulationData || $this->items->isEmpty())
                <div
                    class="py-16 flex flex-col items-center justify-center text-center border border-zinc-200 rounded-2xl">
                    <div class="rounded-full bg-zinc-100 p-4 mb-4">
                        <flux:icon.clipboard-document-list class="w-8 h-8 text-zinc-500" />
                    </div>
                    <h3 class="text-lg font-semibold">Tidak Ada Data Sirkulasi</h3>
                    <p class="mt-2 max-w-sm text-zinc-500">
                        Silakan pastikan filter Kategori Barang dan Gudang telah terisi.
                    </p>
                </div>
            @else
                <table class="w-full border-collapse text-[10px]" style="border: 2px solid #000;">
                    <thead>
                        <tr style="background: #e5e7eb;">
                            <th rowspan="2"
                                style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold; width: 40px;">
                                TGL
                            </th>
                            <th colspan="{{ $this->items->count() }}"
                                style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold; color: #047857; background: #ecfdf5;">
                                PEMBELIAN (IN)
                            </th>
                            <th colspan="{{ $this->items->count() }}"
                                style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold; color: #be123c; background: #fff1f2;">
                                PENJUALAN (OUT)
                            </th>
                            <th colspan="{{ $this->items->count() }}"
                                style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold; color: #1d4ed8; background: #eff6ff;">
                                STOC GUDANG
                            </th>
                            <th rowspan="2"
                                style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold; width: 120px;">
                                KETERANGAN
                            </th>
                        </tr>
                        <tr style="background: #e5e7eb;">
                            {{-- IN HEADERS --}}
                            @foreach ($this->items as $item)
                                <th
                                    style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 9px; width: 60px;">
                                    <div class="flex flex-col items-center leading-tight">
                                        <span>{{ $item->name }}</span>
                                    </div>
                                </th>
                            @endforeach
                            {{-- OUT HEADERS --}}
                            @foreach ($this->items as $item)
                                <th
                                    style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 9px; width: 60px;">
                                    <div class="flex flex-col items-center leading-tight">
                                        <span>{{ $item->name }}</span>
                                    </div>
                                </th>
                            @endforeach
                            {{-- BAL HEADERS --}}
                            @foreach ($this->items as $item)
                                <th
                                    style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 9px; width: 60px;">
                                    <div class="flex flex-col items-center leading-tight">
                                        <span>{{ $item->name }}</span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <!-- SALDO AWAL -->
                        <tr style="background-color: #f3f4f6;">
                            <td
                                style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                                STOC
                            </td>
                            {{-- Empty IN & OUT --}}
                            @for ($i = 0; $i < $this->items->count() * 2; $i++)
                                <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                            @endfor
                            {{-- Initial Stock Values --}}
                            @foreach ($this->items as $item)
                                @php $initial = $this->circulationData['initial'][$item->id] ?? 0; @endphp
                                <td
                                    style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 11px; {{ $initial < 0 ? 'color: #dc2626;' : 'color: #1d4ed8;' }}">
                                    {{ $initial != 0 ? number_format($initial) : '0' }}
                                </td>
                            @endforeach
                            <td style="border: 1px solid #000; padding: 1px 4px;"></td>
                        </tr>

                        <!-- DAILY ROWS -->
                        @php
                            $totalIn = array_fill_keys($this->items->pluck('id')->toArray(), 0);
                            $totalOut = array_fill_keys($this->items->pluck('id')->toArray(), 0);
                        @endphp

                        @foreach ($this->circulationData['daily'] as $day => $data)
                            <tr>
                                <td
                                    style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                                    {{ $day }}
                                </td>

                                {{-- IN --}}
                                @foreach ($this->items as $item)
                                    @php
                                        $in = $data['in'][$item->id] ?? 0;
                                        $totalIn[$item->id] += $in;
                                    @endphp
                                    <td
                                        style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-size: 11px; {{ $in > 0 ? 'font-weight: bold; color: #047857;' : 'color: transparent;' }}">
                                        {{ $in > 0 ? number_format($in) : '0' }}
                                    </td>
                                @endforeach

                                {{-- OUT --}}
                                @foreach ($this->items as $item)
                                    @php
                                        $out = $data['out'][$item->id] ?? 0;
                                        $totalOut[$item->id] += $out;
                                    @endphp
                                    <td
                                        style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-size: 11px; {{ $out > 0 ? 'font-weight: bold; color: #be123c;' : 'color: transparent;' }}">
                                        {{ $out > 0 ? number_format($out) : '0' }}
                                    </td>
                                @endforeach

                                {{-- BALANCE --}}
                                @foreach ($this->items as $item)
                                    @php $bal = $data['bal'][$item->id] ?? 0; @endphp
                                    <td
                                        style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-size: 11px; font-weight: bold; {{ $bal < 0 ? 'color: #dc2626;' : 'color: #2563eb;' }}">
                                        {{ $bal != 0 ? number_format($bal) : '0' }}
                                    </td>
                                @endforeach
                                <td style="border: 1px solid #000; padding: 1px 4px;"></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background: #e5e7eb;">
                            <td
                                style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                                TTL
                            </td>
                            {{-- TOTAL IN --}}
                            @foreach ($this->items as $item)
                                <td
                                    style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 11px; color: #047857;">
                                    {{ $totalIn[$item->id] > 0 ? number_format($totalIn[$item->id]) : '-' }}
                                </td>
                            @endforeach
                            {{-- TOTAL OUT --}}
                            @foreach ($this->items as $item)
                                <td
                                    style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 11px; color: #be123c;">
                                    {{ $totalOut[$item->id] > 0 ? number_format($totalOut[$item->id]) : '-' }}
                                </td>
                            @endforeach
                            {{-- FINAL BALANCE (last day) --}}
                            @foreach ($this->items as $item)
                                @php
                                    $lastDay = count($this->circulationData['daily']);
                                    $finalBal = $this->circulationData['daily'][$lastDay]['bal'][$item->id] ?? 0;
                                @endphp
                                <td
                                    style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 11px; color: #1d4ed8; background: #eff6ff;">
                                    {{ number_format($finalBal) }}
                                </td>
                            @endforeach
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
