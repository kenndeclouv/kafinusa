<div class="p-6 bg-white text-black min-h-screen">
    {{-- Action bar --}}
    <div class="no-print flex items-center justify-between pb-4 mb-4 border-b border-zinc-200">
        <div>
            <h1 class="text-xl font-bold">Cetak Proses Masakan Petis</h1>
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
        <h2 class="text-base font-extrabold uppercase tracking-wide">LAPORAN PROSES MASAKAN PETIS</h2>
        <div class="text-xs font-semibold uppercase mt-0.5">
            GUDANG: {{ $this->currentWarehouse->name ?? '-' }} &bull; BULAN: {{ \Carbon\Carbon::parse($month . '-01')->translatedFormat('F Y') }}
        </div>
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

    <table class="w-full text-[9px] border-collapse border border-black font-mono">
        <thead>
            <tr class="bg-zinc-100 font-bold uppercase text-center border-b border-black">
                <th rowspan="2" class="border border-black px-1.5 py-1 w-8">TGL</th>
                <th colspan="{{ $cookers->count() + 1 }}" class="border border-black px-1 py-1">PROSES MASAKAN</th>
                <th colspan="3" class="border border-black px-1 py-1">HASIL MASAKAN</th>
                <th colspan="4" class="border border-black px-1 py-1">KONVERSI HASIL</th>
                <th colspan="2" class="border border-black px-1 py-1">STOCK NYATA</th>
            </tr>
            <tr class="bg-zinc-50 border-b border-black text-[8px] text-center">
                @foreach ($cookers as $c)
                    <th class="border border-black px-1 py-0.5 truncate max-w-[45px]">{{ $c->name }}</th>
                @endforeach
                <th class="border border-black px-1 py-0.5 font-bold">TOTAL</th>

                <th class="border border-black px-1 py-0.5">KALENG</th>
                <th class="border border-black px-1 py-0.5">BAL</th>
                <th class="border border-black px-1 py-0.5">SISA (KG)</th>

                <th class="border border-black px-1 py-0.5">KL (KG)</th>
                <th class="border border-black px-1 py-0.5">RF (KG)</th>
                <th class="border border-black px-1 py-0.5 font-bold">TOTAL KG</th>
                <th class="border border-black px-1 py-0.5">RATA-RATA</th>

                <th class="border border-black px-1 py-0.5">KALENG</th>
                <th class="border border-black px-1 py-0.5">REFILL</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($days as $d)
                <tr>
                    <td class="border border-black px-1.5 py-0.5 text-center font-bold">{{ $d['day'] }}</td>

                    @foreach ($cookers as $c)
                        @php $s = $d['sessions'][$c->name] ?? 0; @endphp
                        <td class="border border-black px-1 py-0.5 text-center {{ $s > 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $s > 0 ? number_format($s, 0, ',', '.') : '-' }}</td>
                    @endforeach

                    <td class="border border-black px-1 py-0.5 text-center font-bold bg-zinc-50">
                        {{ $d['total_sessions'] > 0 ? number_format($d['total_sessions'], 0, ',', '.') : '-' }}
                    </td>

                    <td class="border border-black px-1 py-0.5 text-center {{ $d['kaleng'] > 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $d['kaleng'] > 0 ? number_format($d['kaleng'], 0, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center {{ $d['refill'] > 0 ? 'font-bold' : 'text-zinc-300' }}">{{ $d['refill'] > 0 ? number_format($d['refill'], 0, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center {{ $d['sisa_kg'] > 0 ? '' : 'text-zinc-300' }}">{{ $d['sisa_kg'] > 0 ? number_format($d['sisa_kg'], 1, ',', '.') : '-' }}</td>

                    <td class="border border-black px-1 py-0.5 text-center text-zinc-600">{{ $d['kg_kaleng'] > 0 ? number_format($d['kg_kaleng'], 1, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center text-zinc-600">{{ $d['kg_refill'] > 0 ? number_format($d['kg_refill'], 1, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center font-bold bg-zinc-50">{{ $d['total_kg'] > 0 ? number_format($d['total_kg'], 1, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center font-bold">{{ $d['avg_kg_per_session'] > 0 ? number_format($d['avg_kg_per_session'], 1, ',', '.') : '-' }}</td>

                    <td class="border border-black px-1 py-0.5 text-center">{{ $d['opname_kaleng'] !== null ? number_format($d['opname_kaleng'], 0, ',', '.') : '-' }}</td>
                    <td class="border border-black px-1 py-0.5 text-center">{{ $d['opname_refill'] !== null ? number_format($d['opname_refill'], 0, ',', '.') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr class="bg-zinc-200 font-bold border-t-2 border-black">
                <td class="border border-black px-1.5 py-1 text-center font-sans">TOTAL</td>
                @foreach ($cookers as $c)
                    <td class="border border-black px-1 py-1 text-center">{{ number_format($totalCookerSessions[$c->name] ?? 0, 0, ',', '.') }}</td>
                @endforeach
                <td class="border border-black px-1 py-1 text-center bg-zinc-300">{{ number_format($grandTotalSessions, 0, ',', '.') }}</td>

                <td class="border border-black px-1 py-1 text-center">{{ number_format($grandTotalKaleng, 0, ',', '.') }}</td>
                <td class="border border-black px-1 py-1 text-center">{{ number_format($grandTotalRefill, 0, ',', '.') }}</td>
                <td class="border border-black px-1 py-1 text-center">{{ number_format($grandTotalSisaKg, 1, ',', '.') }}</td>

                <td class="border border-black px-1 py-1 text-center">{{ number_format($grandTotalKaleng * \App\Models\PetisProduction::WEIGHT_KALENG_KG, 1, ',', '.') }}</td>
                <td class="border border-black px-1 py-1 text-center">{{ number_format($grandTotalRefill * \App\Models\PetisProduction::WEIGHT_REFILL_KG, 1, ',', '.') }}</td>
                <td class="border border-black px-1 py-1 text-center bg-zinc-300">{{ number_format($grandTotalKg, 1, ',', '.') }}</td>
                <td class="border border-black px-1 py-1 text-center">{{ number_format($overallAvg, 1, ',', '.') }}</td>

                <td class="border border-black px-1 py-1 text-center text-zinc-400">-</td>
                <td class="border border-black px-1 py-1 text-center text-zinc-400">-</td>
            </tr>

            <tr class="text-[8px] bg-zinc-100 font-sans">
                <td class="border border-black px-1.5 py-0.5 text-center">HARI</td>
                @foreach ($cookers as $c)
                    <td class="border border-black px-1 py-0.5 text-center">{{ $totalCookerDays[$c->name] ?? 0 }} Hari</td>
                @endforeach
                <td class="border border-black px-1 py-0.5 text-center font-bold">{{ $totalDaysCooking }} Hari</td>
                @for ($s = 0; $s < 9; $s++)
                    <td class="border border-black px-1 py-0.5 text-center text-zinc-400">-</td>
                @endfor
            </tr>
        </tfoot>
    </table>
</div>
