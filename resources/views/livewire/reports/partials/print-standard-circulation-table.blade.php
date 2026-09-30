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
