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
                    linkDl.download = 'produksi-{{ Str::slug($warehouse->name) }}-{{ \Carbon\Carbon::parse($date)->format('dmY') }}-{{ Str::slug($this->category->name) }}.png';
                    linkDl.href = canvas.toDataURL('image/png');
                    linkDl.click();
                    this.isDownloading = false;
                }).catch(err => {
                    console.error('Error generating image', err);
                    this.isDownloading = false;
                });
            }
        }">

            <flux:button href="{{ route('productions.show', ['warehouse' => $warehouse->id]) }}" wire:navigate variant="outline"
                icon="arrow-left" class="w-full lg:w-auto">
                Kembali & Edit
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
        </span>
    </div>

    {{-- The "Paper" Container --}}
    <div id="print-container"
        class="w-full overflow-x-auto bg-white print:overflow-visible flex flex-col gap-8 print:block print:gap-0">
        
        <div class="w-full pt-4 print:pt-0 text-black @if(count($this->result_items) > 10) @else print-half-width @endif"
            style="page-break-inside: avoid; padding-right: 15px;">
            
            {{-- Document Header --}}
            <div class="mb-2 bg-white text-black print:mb-2 print:pb-1">
                <div class="flex items-start justify-between">
                    <h1 class="text-base font-bold uppercase tracking-widest italic mb-2">
                        PRODUKSI {{ $this->category->name }}
                    </h1>
                    <div class="text-xs font-semibold text-right uppercase italic tracking-wider">
                        {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d M Y') }}
                    </div>
                </div>
            </div>

            {{-- Main Layout --}}
            <div class="w-full text-black bg-white">
                <table class="w-full border-collapse text-[11px]" style="border: 2px solid #000;">
                    <thead>
                        <tr style="background: #e5e7eb;">
                            <th rowspan="2" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; width: 40px; text-align: center;">TIM</th>
                            <th colspan="{{ count($this->result_items) }}" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">HASIL ({{ $this->category->name }})</th>
                            <th rowspan="2" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center; width: 60px;">TTL<br>BAHAN</th>
                            <th rowspan="2" style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center; width: 200px;">KETERANGAN</th>
                        </tr>
                        <tr style="background: #e5e7eb;">
                            @foreach($this->result_items as $item)
                                <th style="border: 1px solid #000; padding: 2px 4px; font-weight: bold; text-align: center;">
                                    {{ $item->name }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grid as $teamId => $row)
                            <tr>
                                <td style="border: 1px solid #000; padding: 4px; font-weight: bold; text-align: center;">
                                    {{ $row['team_name'] }}
                                </td>
                                
                                @foreach($this->result_items as $item)
                                    <td style="border: 1px solid #000; padding: 4px; text-align: center; font-size: 12px; font-weight: 500;">
                                        {{ !empty($row['results'][$item->id]) && $row['results'][$item->id] > 0 ? $this->formatQty($row['results'][$item->id]) : '' }}
                                    </td>
                                @endforeach
                                
                                <td style="border: 1px solid #000; padding: 4px; text-align: center; font-size: 12px; font-weight: bold; background: rgba(0,0,0,0.03);">
                                    {{ !empty($row['raw_quantity']) && $row['raw_quantity'] > 0 ? $this->formatQty($row['raw_quantity']) : '' }}
                                </td>
                                
                                @if($loop->first)
                                    <td rowspan="{{ count($grid) }}" style="border: 1px solid #000; padding: 6px; vertical-align: top; width: 220px;">
                                        <table style="width: 100%; border: none; text-align: left; font-size: 13px; font-weight: 600;">
                                            <tr>
                                                <td style="width: 25px;">L</td>
                                                <td style="width: 15px; text-align: center;">=</td>
                                                <td>{{ $this->formatQty($this->summary['L']) }}</td>
                                            </tr>
                                            <tr>
                                                <td>P</td>
                                                <td style="text-align: center;">=</td>
                                                <td style="white-space: nowrap;">
                                                    {{ $this->formatQty($this->summary['P']) }}
                                                    @if($global_notes)
                                                        &nbsp;&nbsp;&nbsp;&nbsp;{{ $global_notes }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" style="padding-top: 6px; color: {{ $this->summary['selisih'] < 0 ? '#ef4444' : '#000' }};">
                                                    {{ $this->summary['selisih'] == 0 ? '-' : $this->formatQty($this->summary['selisih']) }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background: rgba(0,0,0,0.03); font-weight: bold;">
                        <tr>
                            <td style="border: 1px solid #000; padding: 4px; text-align: right;">TTL</td>
                            @foreach($this->result_items as $item)
                                @php
                                    $colTotal = collect($grid)->sum(fn($row) => (float)($row['results'][$item->id] ?? 0));
                                @endphp
                                <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                                    {{ $colTotal > 0 ? $this->formatQty($colTotal) : '' }}
                                </td>
                            @endforeach
                            <td style="border: 1px solid #000; padding: 4px; text-align: center;">
                                @php
                                    $rawTotal = collect($grid)->sum(fn($row) => (float)($row['raw_quantity'] ?? 0));
                                @endphp
                                {{ $rawTotal > 0 ? $this->formatQty($rawTotal) : '' }}
                            </td>
                            @if(count($grid) == 0)
                                <td style="border: 1px solid #000; padding: 4px;"></td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
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

        .print-half-width {
            width: 50% !important;
        }
    }
</style>
