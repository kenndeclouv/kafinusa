<div class="p-6 bg-white text-black min-h-screen">
    {{-- Action bar --}}
    <div class="no-print flex items-center justify-between pb-4 mb-4 border-b border-zinc-200">
        <div>
            <h1 class="text-xl font-bold">Cetak Rekap Sirkulasi Petis</h1>
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
        <h2 class="text-base font-extrabold uppercase tracking-wide">LAPORAN REKAP SIRKULASI PETIS</h2>
        <div class="text-xs font-semibold uppercase mt-0.5">
            GUDANG: {{ $this->currentWarehouse->name ?? '-' }} &bull; BULAN: {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
        </div>
    </div>

    @php
        $c = $this->circulationData;
        $days = $c['days'] ?? [];
    @endphp

    <table class="w-full text-[9px] border-collapse border border-black font-mono">
        <thead>
            <tr class="bg-zinc-100 font-bold uppercase text-center border-b border-black">
                <th rowspan="2" class="border border-black px-1.5 py-1 w-8">TGL</th>
                <th colspan="2" class="border border-black px-1 py-1">HASIL MASAKAN</th>
                <th colspan="2" class="border border-black px-1 py-1">PENJUALAN BERSIH</th>
                <th colspan="2" class="border border-black px-1 py-1">STOK BUKU</th>
                <th colspan="2" class="border border-black px-1 py-1">STOK OPNAME</th>
                <th colspan="5" class="border border-black px-1 py-1">SELISIH STOK</th>
            </tr>
            <tr class="bg-zinc-50 border-b border-black text-[8px] text-center">
                <th class="border border-black px-1 py-0.5">KALENG</th>
                <th class="border border-black px-1 py-0.5">REFILL</th>
                <th class="border border-black px-1 py-0.5">KALENG</th>
                <th class="border border-black px-1 py-0.5">REFILL</th>
                <th class="border border-black px-1 py-0.5 font-bold">KALENG</th>
                <th class="border border-black px-1 py-0.5 font-bold">REFILL</th>
                <th class="border border-black px-1 py-0.5">KALENG</th>
                <th class="border border-black px-1 py-0.5">REFILL</th>
                <th class="border border-black px-1 py-0.5">KL (PCS)</th>
                <th class="border border-black px-1 py-0.5">RF (BAL)</th>
                <th class="border border-black px-1 py-0.5">KL (KG)</th>
                <th class="border border-black px-1 py-0.5">RF (KG)</th>
                <th class="border border-black px-1 py-0.5 font-bold">TOTAL KG</th>
            </tr>
        </thead>

        <tbody>
            {{-- S AWAL --}}
            <tr class="bg-zinc-100 font-bold">
                <td class="border border-black px-1.5 py-1 text-center font-sans">S AWAL</td>
                <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
                <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
                <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
                <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>

                <td class="border border-black px-1 py-0.5 text-center font-bold">{{ number_format($c['initial_kaleng'] ?? 0, 0, ',', '.') }}</td>
                <td class="border border-black px-1 py-0.5 text-center font-bold">{{ number_format($c['initial_refill'] ?? 0, 0, ',', '.') }}</td>

                <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
                <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
                <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
                <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
                <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
                <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
                <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
            </tr>

            @foreach ($days as $d)
                <tr>
                    <td class="border border-black px-1.5 py-0.5 text-center font-bold">{{ $d['day'] }}</td>

                    <td class="border border-black px-1 py-0.5 text-center {{ $d['in_kaleng'] > 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $d['in_kaleng'] > 0 ? number_format($d['in_kaleng'], 0, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center {{ $d['in_refill'] > 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $d['in_refill'] > 0 ? number_format($d['in_refill'], 0, ',', '.') : '-' }}</td>

                    <td class="border border-black px-1 py-0.5 text-center {{ $d['out_kaleng'] > 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $d['out_kaleng'] > 0 ? number_format($d['out_kaleng'], 0, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center {{ $d['out_refill'] > 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $d['out_refill'] > 0 ? number_format($d['out_refill'], 0, ',', '.') : '-' }}</td>

                    <td class="border border-black px-1 py-0.5 text-center font-bold">{{ number_format($d['book_kaleng'], 0, ',', '.') }}</td>
                    <td class="border border-black px-1 py-0.5 text-center font-bold">{{ number_format($d['book_refill'], 0, ',', '.') }}</td>

                    <td class="border border-black px-1 py-0.5 text-center {{ $d['opname_kaleng'] !== null ? 'font-bold' : 'text-zinc-300' }}">{{ $d['opname_kaleng'] !== null ? number_format($d['opname_kaleng'], 0, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center {{ $d['opname_refill'] !== null ? 'font-bold' : 'text-zinc-300' }}">{{ $d['opname_refill'] !== null ? number_format($d['opname_refill'], 0, ',', '.') : '-' }}</td>

                    <td class="border border-black px-1 py-0.5 text-center {{ $d['diff_kaleng'] !== null && $d['diff_kaleng'] != 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $d['diff_kaleng'] !== null ? number_format($d['diff_kaleng'], 0, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center {{ $d['diff_refill'] !== null && $d['diff_refill'] != 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $d['diff_refill'] !== null ? number_format($d['diff_refill'], 0, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center text-zinc-600">{{ $d['diff_kaleng_kg'] !== null ? number_format($d['diff_kaleng_kg'], 1, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center text-zinc-600">{{ $d['diff_refill_kg'] !== null ? number_format($d['diff_refill_kg'], 1, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center font-bold {{ $d['total_diff_kg'] !== null && $d['total_diff_kg'] != 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $d['total_diff_kg'] !== null ? number_format($d['total_diff_kg'], 1, ',', '.') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr class="bg-zinc-200 font-bold border-t-2 border-black">
                <td class="border border-black px-1.5 py-1 text-center font-sans">TOTAL</td>
                <td class="border border-black px-1 py-1 text-center">{{ number_format($c['total_prod_kaleng'] ?? 0, 0, ',', '.') }}</td>
                <td class="border border-black px-1 py-1 text-center">{{ number_format($c['total_prod_refill'] ?? 0, 0, ',', '.') }}</td>
                <td class="border border-black px-1 py-1 text-center">{{ number_format($c['total_sold_kaleng'] ?? 0, 0, ',', '.') }}</td>
                <td class="border border-black px-1 py-1 text-center">{{ number_format($c['total_sold_refill'] ?? 0, 0, ',', '.') }}</td>

                <td class="border border-black px-1 py-1 text-center bg-zinc-300">{{ number_format($c['final_kaleng'] ?? 0, 0, ',', '.') }}</td>
                <td class="border border-black px-1 py-1 text-center bg-zinc-300">{{ number_format($c['final_refill'] ?? 0, 0, ',', '.') }}</td>

                <td colspan="7" class="border border-black px-1 py-1 text-center text-zinc-400 font-sans text-[8px]">
                    *Stok Akhir: S Awal + Hasil Masakan - Penjualan Bersih
                </td>
            </tr>
        </tfoot>
    </table>
</div>
