<div class="min-h-screen">
    {{-- Print Action Bar (hidden on print) --}}
    <div class="no-print flex items-center gap-3 mb-6 p-4 bg-white dark:bg-white/5 rounded-2xl shadow-sm border border-zinc-200 dark:border-white/10">
        <flux:button href="{{ route('order-books.shipments', $orderBook) }}" wire:navigate variant="outline" icon="arrow-left">
            Kembali
        </flux:button>
        <flux:button onclick="window.print()" variant="primary" icon="printer">
            Cetak Nota
        </flux:button>
        <span class="text-sm text-zinc-500">Atau tekan <kbd class="bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded text-xs border border-zinc-200 dark:border-white/10">Ctrl+P</kbd> untuk cetak</span>
    </div>

    {{-- Notas --}}
    <div class="w-full overflow-x-auto pb-8">
        <div class="notas-container flex flex-col items-center gap-6 min-w-max mx-auto px-4">
            @foreach ($orders as $order)
                <div class="nota-card bg-white text-black p-6 rounded-lg shadow-sm shrink-0" style="width: 21cm; min-height: 13cm;">
                {{-- Header --}}
                <div class="flex justify-between items-start mb-4">
                    <div class="font-bold text-blue-800 text-xl tracking-wider">
                        NOTA PENJUALAN
                    </div>
                    <div class="text-sm text-blue-800" style="font-family: monospace; line-height: 1.4;">
                        <table class="w-full">
                            <tr>
                                <td class="pr-2">Hari/Tgl</td>
                                <td>: {{ $orderBook->book_date->translatedFormat('l, d-m-Y') }}</td>
                            </tr>
                            <tr>
                                <td class="pr-2 align-top">Kepada</td>
                                <td>: <span class="font-bold">{{ $order->customer?->name ?? 'Customer Terhapus' }}</span> ({{ str_pad($order->customer?->id ?? 0, 5, '0', STR_PAD_LEFT) }})
                                    @php
                                        $batches = $orderBatches[$order->id] ?? [];
                                        sort($batches);
                                    @endphp
                                    @if (count($batches) > 0 && max($batches) > 1)
                                        <span class="text-red-600 font-bold ml-1 text-xs uppercase" style="border: 1px solid red; padding: 0 4px; border-radius: 4px; display: inline-block;">
                                            {{ count($batches) > 1 ? 'MUATAN ' . implode(' & ', $batches) : 'MUATAN ' . max($batches) }}
                                        </span>
                                    @endif
                                    <br>
                                  &nbsp;&nbsp;{{ $order->customer?->market?->name ?? $orderBook->market->name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pr-2">Nota</td>
                                <td>: {{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }} &nbsp;&nbsp; Sales : {{ strtoupper($orderBook->employee->name) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Table --}}
                <table class="w-full text-blue-800 text-sm mb-1" style="border-collapse: collapse; border: 1px solid #1e40af;">
                    <thead>
                        <tr>
                            <th class="py-1 px-2 text-center border-b border-r" style="border-color: #1e40af; width: 50px;">NO.</th>
                            <th class="py-1 px-2 text-center border-b border-r" style="border-color: #1e40af;">NAMA BARANG</th>
                            <th class="py-1 px-2 text-center border-b border-r" style="border-color: #1e40af; width: 80px;">JUMLAH</th>
                            <th class="py-1 px-2 text-center border-b border-r" style="border-color: #1e40af; width: 120px;">HARGA</th>
                            <th class="py-1 px-2 text-center border-b" style="border-color: #1e40af; width: 150px;">NILAI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @foreach ($order->orderItems as $index => $item)
                            @php
                                // Attempt to get correct price based on customer category
                                $catName = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($order->customer?->category?->name ?? 'normal')));
                                $prices = is_array($item->item?->prices) ? $item->item?->prices : [];
                                $harga = $item->price > 0 ? $item->price : ($prices[$catName] ?? $prices['normal'] ?? 0);
                                $nilai = $item->quantity * $harga;
                                $grandTotal += $nilai;
                            @endphp
                            <tr style="height: 24px;">
                                <td class="py-1 px-2 text-center border-r" style="border-color: #1e40af;">{{ $index + 1 }}</td>
                                <td class="py-1 px-2 border-r" style="border-color: #1e40af;">{{ $item->item?->name ?? 'Produk Terhapus' }}</td>
                                <td class="py-1 px-2 text-center border-r" style="border-color: #1e40af;">{{ $item->quantity }}</td>
                                <td class="py-1 px-2 text-right border-r" style="border-color: #1e40af;">{{ number_format($harga, 0, ',', '.') }}</td>
                                <td class="py-1 px-2 text-right" style="border-color: #1e40af;">{{ number_format($nilai, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        {{-- Empty rows --}}
                        @for ($i = $order->orderItems->count(); $i < 6; $i++)
                            <tr style="height: 24px;">
                                <td class="border-r" style="border-color: #1e40af;"></td>
                                <td class="border-r" style="border-color: #1e40af;"></td>
                                <td class="border-r" style="border-color: #1e40af;"></td>
                                <td class="border-r" style="border-color: #1e40af;"></td>
                                <td></td>
                            </tr>
                        @endfor
                        
                        <tr style="border-top: 1px solid #1e40af;">
                            <td colspan="3" class="py-2 px-2 border-r align-bottom" style="border-color: #1e40af;">
                                <div class="flex justify-between text-center px-4">
                                    <div class="flex flex-col justify-between" style="min-height: 60px;">
                                        <div>Mengetahui</div>
                                        <div>...................</div>
                                    </div>
                                    <div class="flex flex-col justify-between" style="min-height: 60px;">
                                        <div>Pengirim</div>
                                        <div>...................</div>
                                    </div>
                                    <div class="flex flex-col justify-between" style="min-height: 60px;">
                                        <div>Penerima</div>
                                        <div>...................</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2 px-4 text-center align-bottom border-r font-bold" style="border-color: #1e40af;">TOTAL</td>
                            <td class="py-2 px-2 text-right align-bottom font-bold" style="border-color: #1e40af;">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-blue-800 text-[11px] font-medium leading-tight pt-1">
                    Barang yang sudah dibeli Tidak Boleh dikembalikan<br>
                    Kecuali ada perjanjian terlebih dahulu.
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    @media print {
        @page {
            size: A5 portrait; /* Or half A4 landscape */
            margin: 5mm;
        }

        body {
            background-color: white !important;
            margin: 0;
            padding: 0;
        }

        .no-print {
            display: none !important;
        }

        .notas-container {
            display: block;
            background: white;
            padding: 0;
            gap: 0;
        }

        .nota-card {
            page-break-after: always;
            border: none !important;
            box-shadow: none !important;
            margin: 0;
            padding: 0 !important;
            width: 100% !important;
            min-height: auto !important;
        }
        
        .nota-card:last-child {
            page-break-after: auto;
        }
        
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
    
    /* Simulate dot matrix font somewhat for aesthetic in browser */
    .nota-card {
        font-family: 'Courier New', Courier, monospace;
    }
</style>
