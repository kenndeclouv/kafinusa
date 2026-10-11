<div class="p-6 bg-white text-black min-h-screen">
    {{-- Action bar --}}
    <div class="no-print flex items-center justify-between pb-4 mb-4 border-b border-zinc-200">
        <div>
            <h1 class="text-xl font-bold">Cetak Sirkulasi Bahan Baku Petis</h1>
            <p class="text-xs text-zinc-500">Gudang: {{ $this->currentWarehouse->name ?? '-' }} | Periode: {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-zinc-900 text-white rounded-lg text-sm font-semibold hover:bg-zinc-800 transition">
                Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="px-3 py-2 bg-zinc-100 text-zinc-700 rounded-lg text-sm font-semibold hover:bg-zinc-200 transition">
                Tutup
            </button>
        </div>
    </div>

    {{-- Header Cetak --}}
    <div class="text-center mb-4">
        <h2 class="text-base font-extrabold uppercase tracking-wide">LAPORAN SIRKULASI BAHAN BAKU PETIS</h2>
        <div class="text-xs font-semibold uppercase mt-0.5">
            GUDANG: {{ $this->currentWarehouse->name ?? '-' }} &bull; BULAN: {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
        </div>
    </div>

    @php
        $m = $this->monthlyData;
        $items = $this->rawItems;
        $days = $m['days'] ?? [];
        $initialStocks = $m['initial_stocks'] ?? [];
        $finalStocks = $m['final_stocks'] ?? [];
        $totals = $m['totals'] ?? [];
    @endphp

    {{-- Main Printable Table --}}
    <table class="w-full text-[9px] border-collapse border border-black font-mono">
        <thead>
            <tr class="bg-zinc-100 font-bold uppercase text-center border-b border-black">
                <th rowspan="2" class="border border-black px-1.5 py-1 w-8">TGL</th>
                <th colspan="{{ $items->count() }}" class="border border-black px-1 py-1">PEMBELIAN</th>
                <th colspan="{{ $items->count() }}" class="border border-black px-1 py-1">PEMAKAIAN</th>
                <th colspan="{{ $items->count() }}" class="border border-black px-1 py-1">PENJUALAN</th>
                <th colspan="{{ $items->count() }}" class="border border-black px-1 py-1">STOK BUKU</th>
                <th colspan="{{ $items->count() }}" class="border border-black px-1 py-1">STOK OPNAME</th>
                <th colspan="{{ $items->count() }}" class="border border-black px-1 py-1">SELISIH</th>
            </tr>
            <tr class="bg-zinc-50 border-b border-black text-[8px] text-center">
                @for ($s = 0; $s < 6; $s++)
                    @foreach ($items as $item)
                        <th class="border border-black px-1 py-0.5 truncate max-w-[40px]">{{ $item->name }}</th>
                    @endforeach
                @endfor
            </tr>
        </thead>

        <tbody>
            {{-- S AWAL --}}
            <tr class="bg-zinc-100 font-bold">
                <td class="border border-black px-1.5 py-1 text-center font-sans">S AWAL</td>
                @for ($s = 0; $s < 3; $s++)
                    @foreach ($items as $item)
                        <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
                    @endforeach
                @endfor
                @foreach ($items as $item)
                    <td class="border border-black px-1 py-0.5 text-center">{{ number_format($initialStocks[$item->id] ?? 0, 0, ',', '.') }}</td>
                @endforeach
                @for ($s = 0; $s < 2; $s++)
                    @foreach ($items as $item)
                        <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
                    @endforeach
                @endfor
            </tr>

            {{-- Daily Rows --}}
            @foreach ($days as $d)
                <tr>
                    <td class="border border-black px-1.5 py-0.5 text-center font-bold">{{ $d['day'] }}</td>
                    {{-- Beli --}}
                    @foreach ($items as $item)
                        @php $v = $d['purchase'][$item->id] ?? 0; @endphp
                        <td class="border border-black px-1 py-0.5 text-center {{ $v > 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $v > 0 ? number_format($v, 0, ',', '.') : '-' }}</td>
                    @endforeach
                    {{-- Pakai --}}
                    @foreach ($items as $item)
                        @php $v = $d['usage'][$item->id] ?? 0; @endphp
                        <td class="border border-black px-1 py-0.5 text-center {{ $v > 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $v > 0 ? number_format($v, 0, ',', '.') : '-' }}</td>
                    @endforeach
                    {{-- Jual --}}
                    @foreach ($items as $item)
                        @php $v = $d['sale'][$item->id] ?? 0; @endphp
                        <td class="border border-black px-1 py-0.5 text-center {{ $v > 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $v > 0 ? number_format($v, 0, ',', '.') : '-' }}</td>
                    @endforeach
                    {{-- Buku --}}
                    @foreach ($items as $item)
                        @php $v = $d['book_stock'][$item->id] ?? 0; @endphp
                        <td class="border border-black px-1 py-0.5 text-center font-bold">{{ number_format($v, 0, ',', '.') }}</td>
                    @endforeach
                    {{-- Opname --}}
                    @foreach ($items as $item)
                        @php $v = $d['opname'][$item->id]; @endphp
                        <td class="border border-black px-1 py-0.5 text-center {{ $v !== null ? 'font-bold' : 'text-zinc-300' }}">{{ $v !== null ? number_format($v, 0, ',', '.') : '-' }}</td>
                    @endforeach
                    {{-- Selisih --}}
                    @foreach ($items as $item)
                        @php $v = $d['diff'][$item->id]; @endphp
                        <td class="border border-black px-1 py-0.5 text-center {{ $v !== null && $v != 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $v !== null ? number_format($v, 0, ',', '.') : '-' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr class="bg-zinc-200 font-bold border-t-2 border-black">
                <td class="border border-black px-1.5 py-1 text-center font-sans">TOTAL</td>
                @foreach ($items as $item)
                    <td class="border border-black px-1 py-1 text-center">{{ number_format($totals['purchase'][$item->id] ?? 0, 0, ',', '.') }}</td>
                @endforeach
                @foreach ($items as $item)
                    <td class="border border-black px-1 py-1 text-center">{{ number_format($totals['usage'][$item->id] ?? 0, 0, ',', '.') }}</td>
                @endforeach
                @foreach ($items as $item)
                    <td class="border border-black px-1 py-1 text-center">{{ number_format($totals['sale'][$item->id] ?? 0, 0, ',', '.') }}</td>
                @endforeach
                @foreach ($items as $item)
                    <td class="border border-black px-1 py-1 text-center">{{ number_format($finalStocks[$item->id] ?? 0, 0, ',', '.') }}</td>
                @endforeach
                @for ($s = 0; $s < 2; $s++)
                    @foreach ($items as $item)
                        <td class="border border-black px-1 py-1 text-center text-zinc-400">-</td>
                    @endforeach
                @endfor
            </tr>
        </tfoot>
    </table>
</div>
