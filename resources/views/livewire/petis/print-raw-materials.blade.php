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
                    linkDl.download = 'sirkulasi-bahan-baku-petis-{{ Str::slug($this->currentWarehouse->name ?? "gudang") }}-{{ $month }}.png';
                    linkDl.href = canvas.toDataURL('image/png');
                    linkDl.click();
                    this.isDownloading = false;
                }).catch(err => {
                    console.error('Error generating image', err);
                    this.isDownloading = false;
                });
            }
        }">

            <flux:button href="{{ route('petis.raw-materials.index') }}" wire:navigate variant="outline"
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
            / simpan PDF
        </span>
    </div>

    @php
        $m = $this->monthlyData;
        $items = $this->rawItems;
        $days = $m['days'] ?? [];
        $initialStocks = $m['initial_stocks'] ?? [];
        $finalStocks = $m['final_stocks'] ?? [];
        $totals = $m['totals'] ?? [];
    @endphp

    {{-- The "Paper" Container for printing & mobile scrolling --}}
    <div id="print-container"
        class="w-full overflow-x-auto bg-white print:overflow-visible flex flex-col gap-6 print:block print:gap-0">
        <div class="w-full pt-4 print:pt-0 text-black">
            {{-- Document Header --}}
            <div class="mb-3 border-b-2 border-black pb-2 bg-white text-black print:mb-2 print:pb-1">
                <h1 class="text-base font-bold uppercase tracking-widest italic text-center mb-1">
                    Laporan Sirkulasi Bahan Baku Petis
                </h1>
                <div class="flex items-start justify-between text-xs font-semibold">
                    <div class="tracking-widest uppercase">
                        GUDANG : {{ $this->currentWarehouse->name ?? '-' }}
                    </div>
                    <div class="text-right">
                        Bulan / Periode : {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
                    </div>
                </div>
            </div>

            {{-- Main Layout / Table --}}
            <div class="w-full text-black bg-white">
                <table class="w-full border-collapse text-[9px]" style="border: 2px solid #000;">
                    <thead>
                        <tr style="background: #e5e7eb;">
                            <th rowspan="2" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; width: 30px; text-align: center;">TGL</th>
                            <th colspan="{{ $items->count() }}" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">PEMBELIAN</th>
                            <th colspan="{{ $items->count() }}" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">PEMAKAIAN</th>
                            <th colspan="{{ $items->count() }}" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">PENJUALAN</th>
                            <th colspan="{{ $items->count() }}" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">STOK BUKU</th>
                            <th colspan="{{ $items->count() }}" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">STOK OPNAME</th>
                            <th colspan="{{ $items->count() }}" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">SELISIH</th>
                        </tr>
                        <tr style="background: #f3f4f6; font-size: 8px; text-align: center;">
                            @for ($s = 0; $s < 6; $s++)
                                @foreach ($items as $item)
                                    <th style="border: 1px solid #000; padding: 1px 2px;" class="truncate max-w-[40px]">{{ $item->name }}</th>
                                @endforeach
                            @endfor
                        </tr>
                    </thead>

                    <tbody>
                        {{-- S AWAL --}}
                        <tr style="background: #f3f4f6; font-weight: bold;">
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">S AWAL</td>
                            @for ($s = 0; $s < 3; $s++)
                                @foreach ($items as $item)
                                    <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                                @endforeach
                            @endfor
                            @foreach ($items as $item)
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                                    {{ number_format($initialStocks[$item->id] ?? 0, 0, ',', '.') }}
                                </td>
                            @endforeach
                            @for ($s = 0; $s < 2; $s++)
                                @foreach ($items as $item)
                                    <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                                @endforeach
                            @endfor
                        </tr>

                        {{-- Daily Rows --}}
                        @foreach ($days as $d)
                            <tr>
                                <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">{{ $d['day'] }}</td>
                                {{-- Beli --}}
                                @foreach ($items as $item)
                                    @php $v = $d['purchase'][$item->id] ?? 0; @endphp
                                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; {{ $v > 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">{{ $v > 0 ? number_format($v, 0, ',', '.') : '-' }}</td>
                                @endforeach
                                {{-- Pakai --}}
                                @foreach ($items as $item)
                                    @php $v = $d['usage'][$item->id] ?? 0; @endphp
                                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; {{ $v > 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">{{ $v > 0 ? number_format($v, 0, ',', '.') : '-' }}</td>
                                @endforeach
                                {{-- Jual --}}
                                @foreach ($items as $item)
                                    @php $v = $d['sale'][$item->id] ?? 0; @endphp
                                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; {{ $v > 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">{{ $v > 0 ? number_format($v, 0, ',', '.') : '-' }}</td>
                                @endforeach
                                {{-- Buku --}}
                                @foreach ($items as $item)
                                    @php $v = $d['book_stock'][$item->id] ?? 0; @endphp
                                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">{{ number_format($v, 0, ',', '.') }}</td>
                                @endforeach
                                {{-- Opname --}}
                                @foreach ($items as $item)
                                    @php $v = $d['opname'][$item->id]; @endphp
                                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; {{ $v !== null ? 'font-weight: bold;' : 'color: #9ca3af;' }}">{{ $v !== null ? number_format($v, 0, ',', '.') : '-' }}</td>
                                @endforeach
                                {{-- Selisih --}}
                                @foreach ($items as $item)
                                    @php $v = $d['diff'][$item->id]; @endphp
                                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; {{ $v !== null && $v != 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">{{ $v !== null ? number_format($v, 0, ',', '.') : '-' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr style="background: #e5e7eb; font-weight: bold; border-top: 2px solid #000;">
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">TOTAL</td>
                            @foreach ($items as $item)
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($totals['purchase'][$item->id] ?? 0, 0, ',', '.') }}</td>
                            @endforeach
                            @foreach ($items as $item)
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($totals['usage'][$item->id] ?? 0, 0, ',', '.') }}</td>
                            @endforeach
                            @foreach ($items as $item)
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($totals['sale'][$item->id] ?? 0, 0, ',', '.') }}</td>
                            @endforeach
                            @foreach ($items as $item)
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; background: #d1d5db;">{{ number_format($finalStocks[$item->id] ?? 0, 0, ',', '.') }}</td>
                            @endforeach
                            @for ($s = 0; $s < 2; $s++)
                                @foreach ($items as $item)
                                    <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                                @endforeach
                            @endfor
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Signature / Meta Info Box --}}
            <div class="mt-4 flex justify-between items-start text-xs text-black">
                <div class="text-left text-[11px] text-zinc-500 italic">
                    * Dicetak otomatis pada: {{ now()->translatedFormat('d F Y, H:i') }} &bull; Kafinusa ERP
                </div>
                <div class="w-[260px] border border-black text-center text-xs">
                    <div class="border-b border-black p-1 font-bold bg-zinc-100 uppercase text-[11px]">
                        Penanggung Jawab Gudang
                    </div>
                    <div class="h-16"></div>
                    <div class="border-t border-black p-1 font-semibold uppercase text-[11px]">
                        ( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )
                    </div>
                </div>
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
            font-size: 10px;
            background: white !important;
            color: black !important;
        }

        @page {
            size: landscape;
            margin: 6mm;
        }

        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
        }
    }
</style>
