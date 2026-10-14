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
                    linkDl.download = 'proses-masakan-petis-{{ Str::slug($this->currentWarehouse->name ?? "gudang") }}-{{ $month }}.png';
                    linkDl.href = canvas.toDataURL('image/png');
                    linkDl.click();
                    this.isDownloading = false;
                }).catch(err => {
                    console.error('Error generating image', err);
                    this.isDownloading = false;
                });
            }
        }">

            <flux:button href="{{ route('petis.cookings.index') }}" wire:navigate variant="outline"
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
        $cookers = $this->cookers;
        $days = $m['days'] ?? [];
        $totalCookerSessions = $m['totalCookerSessions'] ?? [];
        $totalCookerDays = $m['totalCookerDays'] ?? [];
        $grandTotalSessions = $m['grandTotalSessions'] ?? 0;
        $grandTotalKaleng = $m['grandTotalKaleng'] ?? 0;
        $grandTotalRefill = $m['grandTotalRefill'] ?? 0;
        $grandTotalSisaKg = $m['grandTotalSisaKg'] ?? 0;
        $grandTotalKg = $m['grandTotalKg'] ?? 0;
        $overallAvg = $m['overallAvg'] ?? 0;
        $totalDaysCooking = $m['totalDaysCooking'] ?? 0;
    @endphp

    {{-- The "Paper" Container for printing & mobile scrolling --}}
    <div id="print-container"
        class="w-full overflow-x-auto bg-white print:overflow-visible flex flex-col gap-6 print:block print:gap-0">
        <div class="w-full pt-4 print:pt-0 text-black">
            {{-- Document Header --}}
            <div class="mb-3 border-b-2 border-black pb-2 bg-white text-black print:mb-2 print:pb-1">
                <h1 class="text-base font-bold uppercase tracking-widest italic text-center mb-1">
                    Laporan Proses Masakan Petis
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
                            <th colspan="{{ $cookers->count() + 1 }}" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">PROSES MASAKAN (SESI)</th>
                            <th colspan="3" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">HASIL MASAKAN</th>
                            <th colspan="4" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">KONVERSI HASIL</th>
                            <th colspan="2" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">STOCK NYATA (OPNAME)</th>
                        </tr>
                        <tr style="background: #f3f4f6; font-size: 9px; text-align: center;">
                            @foreach ($cookers as $c)
                                <th style="border: 1px solid #000; padding: 2px 4px;" class="truncate max-w-[45px]">{{ $c->name }}</th>
                            @endforeach
                            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: bold;">TOTAL</th>

                            <th style="border: 1px solid #000; padding: 2px 4px;">KALENG</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">BAL</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">SISA (KG)</th>

                            <th style="border: 1px solid #000; padding: 2px 4px;">KL (KG)</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">RF (KG)</th>
                            <th style="border: 1px solid #000; padding: 2px 4px; font-weight: bold;">TOTAL KG</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">RATA-RATA</th>

                            <th style="border: 1px solid #000; padding: 2px 4px;">KALENG</th>
                            <th style="border: 1px solid #000; padding: 2px 4px;">REFILL</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($days as $d)
                            <tr>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">{{ $d['day'] }}</td>

                                @foreach ($cookers as $c)
                                    @php $val = $d['sessions'][$c->name] ?? 0; @endphp
                                    <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $val > 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                        {{ $val > 0 ? $val : '-' }}
                                    </td>
                                @endforeach

                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: bold; {{ $d['day_sessions'] > 0 ? '' : 'color: #9ca3af;' }}">
                                    {{ $d['day_sessions'] > 0 ? $d['day_sessions'] : '-' }}
                                </td>

                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['result_kaleng'] > 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['result_kaleng'] > 0 ? number_format($d['result_kaleng'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['result_refill'] > 0 ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['result_refill'] > 0 ? number_format($d['result_refill'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['result_sisa_kg'] > 0 ? '' : 'color: #9ca3af;' }}">
                                    {{ $d['result_sisa_kg'] > 0 ? number_format($d['result_sisa_kg'], 1, ',', '.') : '-' }}
                                </td>

                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['kg_kaleng'] > 0 ? '' : 'color: #9ca3af;' }}">
                                    {{ $d['kg_kaleng'] > 0 ? number_format($d['kg_kaleng'], 1, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['kg_refill'] > 0 ? '' : 'color: #9ca3af;' }}">
                                    {{ $d['kg_refill'] > 0 ? number_format($d['kg_refill'], 1, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: bold; {{ $d['day_kg'] > 0 ? '' : 'color: #9ca3af;' }}">
                                    {{ $d['day_kg'] > 0 ? number_format($d['day_kg'], 1, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['avg_kg_per_session'] > 0 ? '' : 'color: #9ca3af;' }}">
                                    {{ $d['avg_kg_per_session'] > 0 ? number_format($d['avg_kg_per_session'], 1, ',', '.') : '-' }}
                                </td>

                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['opname_kaleng'] !== null ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['opname_kaleng'] !== null ? number_format($d['opname_kaleng'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; {{ $d['opname_refill'] !== null ? 'font-weight: bold;' : 'color: #9ca3af;' }}">
                                    {{ $d['opname_refill'] !== null ? number_format($d['opname_refill'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr style="background: #e5e7eb; font-weight: bold; border-top: 2px solid #000;">
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">TOTAL</td>
                            @foreach ($cookers as $c)
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($totalCookerSessions[$c->name] ?? 0, 0, ',', '.') }}</td>
                            @endforeach
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; background: #d1d5db;">{{ number_format($grandTotalSessions, 0, ',', '.') }}</td>

                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($grandTotalKaleng, 0, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($grandTotalRefill, 0, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($grandTotalSisaKg, 1, ',', '.') }}</td>

                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($grandTotalKaleng * \App\Models\PetisProduction::WEIGHT_KALENG_KG, 1, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($grandTotalRefill * \App\Models\PetisProduction::WEIGHT_REFILL_KG, 1, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; background: #d1d5db;">{{ number_format($grandTotalKg, 1, ',', '.') }}</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ number_format($overallAvg, 1, ',', '.') }}</td>

                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
                        </tr>

                        <tr style="background: #f3f4f6; font-size: 9px;">
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">HARI</td>
                            @foreach ($cookers as $c)
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center;">{{ $totalCookerDays[$c->name] ?? 0 }} Hari</td>
                            @endforeach
                            <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; font-weight: bold;">{{ $totalDaysCooking }} Hari</td>
                            @for ($s = 0; $s < 9; $s++)
                                <td style="border: 1px solid #000; padding: 2px 4px; text-align: center; color: #9ca3af;">-</td>
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
