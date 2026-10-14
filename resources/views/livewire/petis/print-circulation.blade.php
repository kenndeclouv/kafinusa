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
                    linkDl.download = 'rekap-sirkulasi-petis-{{ Str::slug($this->currentWarehouse->name ?? "gudang") }}-{{ $month }}.png';
                    linkDl.href = canvas.toDataURL('image/png');
                    linkDl.click();
                    this.isDownloading = false;
                }).catch(err => {
                    console.error('Error generating image', err);
                    this.isDownloading = false;
                });
            }
        }">

            <flux:button href="{{ route('reports.petis-recap') }}" wire:navigate variant="outline"
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
        $c = $this->circulationData;
        $days = $c['days'] ?? [];
    @endphp

    {{-- The "Paper" Container for printing & mobile scrolling --}}
    <div id="print-container"
        class="w-full overflow-x-auto bg-white print:overflow-visible flex flex-col gap-6 print:block print:gap-0">
        <div class="w-full pt-4 print:pt-0 text-black">
            {{-- Document Header --}}
            <div class="mb-3 border-b-2 border-black pb-2 bg-white text-black print:mb-2 print:pb-1">
                <h1 class="text-base font-bold uppercase tracking-widest italic text-center mb-1">
                    Laporan Rekap Sirkulasi Petis
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
                <table class="w-full border-collapse text-[10px]" style="border: 2px solid #000;">
                    <thead>
                        <tr style="background: #e5e7eb;">
                            <th rowspan="2" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; width: 35px; text-align: center;">TGL</th>
                            <th colspan="2" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">HASIL MASAKAN</th>
                            <th colspan="2" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">PENJUALAN BERSIH</th>
                            <th colspan="2" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">STOK BUKU</th>
                            <th colspan="2" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">STOK OPNAME</th>
                            <th colspan="5" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">SELISIH STOK</th>
                        </tr>
                        <tr style="background: #f3f4f6; font-size: 9px; text-align: center;">
                            <th style="border: 1px solid #000; padding: 2px 4px;">KALENG</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">REFILL</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">KALENG</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">REFILL</th>
                            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: bold;">KALENG</th>
                            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: bold;">REFILL</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">KALENG</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">REFILL</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">KL (PCS)</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">RF (BAL)</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">KL (KG)</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">RF (KG)</th>
                            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: bold;">TOTAL KG</th>
                        </tr>
                    </thead>

                    <tbody>
                        {{-- S AWAL --}}
                        <tr style="background: #f3f4f6; font-weight: bold;">
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">S AWAL</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>

                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">{{ number_format($c['initial_kaleng'] ?? 0, 0, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">{{ number_format($c['initial_refill'] ?? 0, 0, ',', '.') }}</td>

                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                        </tr>

                        @foreach ($days as $d)
                            <tr>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">{{ $d['day'] }}</td>

                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['in_kaleng'] > 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['in_kaleng'] > 0 ? number_format($d['in_kaleng'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['in_refill'] > 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['in_refill'] > 0 ? number_format($d['in_refill'], 0, ',', '.') : '-' }}
                                </td>

                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['out_kaleng'] > 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['out_kaleng'] > 0 ? number_format($d['out_kaleng'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['out_refill'] > 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['out_refill'] > 0 ? number_format($d['out_refill'], 0, ',', '.') : '-' }}
                                </td>

                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                                    {{ number_format($d['book_kaleng'], 0, ',', '.') }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">
                                    {{ number_format($d['book_refill'], 0, ',', '.') }}
                                </td>

                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['opname_kaleng'] !== null ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['opname_kaleng'] !== null ? number_format($d['opname_kaleng'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['opname_refill'] !== null ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['opname_refill'] !== null ? number_format($d['opname_refill'], 0, ',', '.') : '-' }}
                                </td>

                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['diff_kaleng'] !== null && $d['diff_kaleng'] != 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['diff_kaleng'] !== null ? number_format($d['diff_kaleng'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['diff_refill'] !== null && $d['diff_refill'] != 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['diff_refill'] !== null ? number_format($d['diff_refill'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #4b5563;">
                                    {{ $d['diff_kaleng_kg'] !== null ? number_format($d['diff_kaleng_kg'], 1, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #4b5563;">
                                    {{ $d['diff_refill_kg'] !== null ? number_format($d['diff_refill_kg'], 1, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: bold; {{ $d['total_diff_kg'] !== null && $d['total_diff_kg'] != 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['total_diff_kg'] !== null ? number_format($d['total_diff_kg'], 1, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr style="background: #e5e7eb; font-weight: bold; border-top: 2px solid #000;">
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">TOTAL</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($c['total_prod_kaleng'] ?? 0, 0, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($c['total_prod_refill'] ?? 0, 0, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($c['total_sold_kaleng'] ?? 0, 0, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($c['total_sold_refill'] ?? 0, 0, ',', '.') }}</td>

                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; background: #d1d5db;">{{ number_format($c['final_kaleng'] ?? 0, 0, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; background: #d1d5db;">{{ number_format($c['final_refill'] ?? 0, 0, ',', '.') }}</td>

                            {{-- <td colspan="7" style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #4b5563; font-size: 8px;">
                                *Stok Akhir: S Awal + Hasil Masakan - Penjualan Bersih
                            </td> --}}
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Signature / Meta Info Box --}}
            <div class="mt-4 flex justify-between items-start text-xs text-black">
                <div class="text-left text-[11px] text-zinc-500 italic">
                    * Dicetak otomatis pada: {{ now()->translatedFormat('d F Y, H:i') }}
                </div>
                {{-- <div class="w-[260px] border border-black text-center text-xs">
                    <div class="border-b border-black p-1 font-bold bg-zinc-100 uppercase text-[11px]">
                        Penanggung Jawab Gudang
                    </div>
                    <div class="h-16"></div>
                    <div class="border-t border-black p-1 font-semibold uppercase text-[11px]">
                        ( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )
                    </div>
                </div> --}}
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
            background: white !important;
            color: black !important;
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
