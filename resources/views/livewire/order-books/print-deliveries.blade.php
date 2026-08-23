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
                    linkDl.download = 'pengiriman-barang-{{ Str::slug($orderBook->market->name) }}-{{ $orderBook->book_date->format("dmY") }}.png';
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
    <div id="print-container" class="w-full overflow-x-auto bg-white print:overflow-visible">
        <div class="min-w-[950px] pt-4 print:pt-0 text-black">

            @php
                $batchesData = $this->reportData;
            @endphp

            @if ($batchesData->isEmpty())
                <div class="text-center py-12 text-zinc-500 no-print">
                    Belum ada pesanan untuk buku order ini.
                </div>
            @else
                @foreach ($batchesData as $batchNumber => $data)
                    @php
                        $customers = $data['customers'];
                        $categories = $data['categories'];
                        $quantities = $data['quantities'];
                        $customerWeights = $data['customerWeights'];
                        $itemTotals = $data['itemTotals'];
                    @endphp

                    <div
                        class="@if (!$loop->first) print:break-before-page mt-12 print:mt-0 @endif w-full text-black bg-white">
                        {{-- Document Header --}}
                        <div
                            class="flex items-center justify-between mb-4 border-b-2 border-black pb-2 bg-white text-black print:mb-2 print:pb-1">
                            <div>
                                <div class="text-sm font-semibold tracking-widest uppercase">PASAR :
                                    {{ $orderBook->market->name }}</div>
                            </div>
                            <h1 class="text-xl font-bold uppercase tracking-widest italic text-center flex-1">
                                Daftar Pengiriman Barang Pasar
                                {{ $batchesData->count() > 1 ? "(Muatan $batchNumber)" : '' }}
                            </h1>
                            <div class="text-sm font-semibold">
                                Hari / Tgl : {{ $orderBook->book_date->translatedFormat('l / d-m-Y') }}
                            </div>
                        </div>

                        @if ($customers->isEmpty())
                            <div class="text-center py-12 text-zinc-500">
                                Tidak ada pengiriman untuk muatan ini.
                            </div>
                        @else
                            <table class="w-full border-collapse text-[10px]" style="border: 2px solid #000;">
                                <thead>
                                    {{-- Category Header Row --}}
                                    <tr style="background: #e5e7eb;">
                                        <th rowspan="2"
                                            style="border: 1px solid #000; padding: 4px; text-align: left; vertical-align: bottom; font-weight: bold; width: 180px;">
                                            NAMA PELANGGAN
                                        </th>
                                        @foreach ($categories as $category)
                                            <th colspan="{{ count($category['items']) }}"
                                                style="border: 1px solid #000; padding: 2px; text-align: center; font-weight: bold;">
                                                {{ $category['name'] }}
                                            </th>
                                        @endforeach
                                        <th rowspan="2"
                                            style="border: 1px solid #000; padding: 2px; text-align: center; vertical-align: bottom; font-weight: bold; width: 60px;">
                                            TOTAL<br>(KG)
                                        </th>
                                    </tr>
                                    {{-- Item Header Row --}}
                                    <tr style="background: #e5e7eb;">
                                        @foreach ($categories as $category)
                                            @foreach ($category['items'] as $item)
                                                <th
                                                    style="border: 1px solid #000; padding: 2px; text-align: center; font-weight: bold; font-size: 9px; width: 35px;">
                                                    {{ $item->name }}
                                                </th>
                                            @endforeach
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($customers as $customer)
                                        <tr>
                                            <td
                                                style="border: 1px solid #000; padding: 4px; font-weight: 500; text-transform: uppercase; {{ $customer->has_debt ? 'background-color: #000 !important; color: #fff !important;' : '' }}">
                                                {{ $customer->name }}
                                            </td>
                                            @foreach ($categories as $category)
                                                @foreach ($category['items'] as $item)
                                                    @php
                                                        $qtyData = $quantities[$customer->id][$item->id] ?? null;
                                                        $qty = $qtyData['qty'] ?? 0;
                                                        $priceType = $qtyData['price_type'] ?? 'umum';

                                                        $colorStyle = match ($priceType) {
                                                            'promo'
                                                                => 'background-color: #dcfce7 !important; color: #166534;', // green-100 & green-800
                                                            'khusus'
                                                                => 'background-color: #fee2e2 !important; color: #991b1b;', // red-100 & red-800
                                                            default => '',
                                                        };
                                                    @endphp
                                                    <td
                                                        style="border: 1px solid #000; padding: 3px; text-align: center; font-weight: bold; font-size: 11px; {{ $colorStyle }}">
                                                        {{ $qty > 0 ? $qty : '' }}
                                                    </td>
                                                @endforeach
                                            @endforeach
                                            <td
                                                style="border: 1px solid #000; padding: 3px; text-align: center; font-weight: bold;">
                                                {{ number_format(($customerWeights[$customer->id] ?? 0) / 1000, 1, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="background: #e5e7eb;">
                                        <td
                                            style="border: 1px solid #000; padding: 4px; font-weight: bold; text-align: right; text-transform: uppercase;">
                                            TOTAL
                                        </td>
                                        @foreach ($categories as $category)
                                            @foreach ($category['items'] as $item)
                                                @php
                                                    $totalQty = $itemTotals[$item->id] ?? 0;
                                                @endphp
                                                <td
                                                    style="border: 1px solid #000; padding: 3px; text-align: center; font-weight: bold; font-size: 11px;">
                                                    {{ $totalQty > 0 ? $totalQty : '' }}
                                                </td>
                                            @endforeach
                                        @endforeach
                                        <td
                                            style="border: 1px solid #000; padding: 3px; text-align: center; font-weight: bold;">
                                            {{ number_format(array_sum($customerWeights) / 1000, 1, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        @endif
                    </div>
                @endforeach
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
