<table class="w-full border-collapse text-[10px]" style="border: 2px solid #000;">
    <thead>
        <tr style="background: #e5e7eb;">
            <th rowspan="2"
                style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold; width: 40px;">
                TGL
            </th>
            <th colspan="{{ $this->rawItems->count() + $this->resultItems->count() }}"
                style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold; color: #047857; background: #ecfdf5;">
                MASUK (IN)
            </th>
            <th colspan="{{ $this->rawItems->count() + $this->resultItems->count() }}"
                style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold; color: #1d4ed8; background: #eff6ff;">
                PENJUALAN
            </th>
            <th colspan="{{ $this->rawItems->count() }}"
                style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold; color: #7e22ce; background: #faf5ff;">
                PEMAKAIAN u/ PRODUKSI
            </th>
            <th colspan="{{ $this->rawItems->count() + $this->resultItems->count() }}"
                style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold; color: #0f766e; background: #f0fdfa;">
                STOK GUDANG
            </th>
            <th rowspan="2"
                style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold; width: 60px;">
                KET
            </th>
        </tr>
        <tr style="background: #e5e7eb;">
            <!-- PEMBELIAN (Raw Items) -->
            @foreach($this->rawItems as $item)
                <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">
                    {{ $item->name }}
                </th>
            @endforeach
            <!-- HASIL PRODUKSI (Result Items) -->
            @foreach($this->resultItems as $item)
                <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">
                    {{ $item->name }}
                </th>
            @endforeach
            <!-- PENJUALAN (Raw Items) -->
            @foreach($this->rawItems as $item)
                <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">
                    {{ $item->name }}
                </th>
            @endforeach
            <!-- PENJUALAN (Result Items) -->
            @foreach($this->resultItems as $item)
                <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">
                    {{ $item->name }}
                </th>
            @endforeach
            <!-- PEMAKAIAN (Raw Items) -->
            @foreach($this->rawItems as $item)
                <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">
                    {{ $item->name }}
                </th>
            @endforeach
            <!-- STOK (Raw Items) -->
            @foreach($this->rawItems as $item)
                <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">
                    {{ $item->name }}
                </th>
            @endforeach
            <!-- STOK (Result Items) -->
            @foreach($this->resultItems as $item)
                <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">
                    {{ $item->name }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <!-- SALDO AWAL -->
        <tr style="background-color: #f3f4f6;">
            <td style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                STOC
            </td>
            <!-- Empty cells -->
            @for ($i = 0; $i < $this->rawItems->count() * 3 + $this->resultItems->count() * 2; $i++)
                <td style="border: 1px solid #000; padding: 1px 2px;"></td>
            @endfor
            <!-- Initial Stock -->
            @foreach($this->rawItems as $item)
                <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px; color: #0f766e; background: #f0fdfa;">
                    {{ number_format($this->circulationData['initial'][$item->id] ?? 0) }}
                </td>
            @endforeach
            @foreach($this->resultItems as $item)
                <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px; color: #0f766e; background: #f0fdfa;">
                    {{ number_format($this->circulationData['initial'][$item->id] ?? 0) }}
                </td>
            @endforeach
            <td style="border: 1px solid #000; padding: 1px 4px;"></td>
        </tr>

        <!-- DAILY ROWS -->
        @php
            $totPembelian = [];
            $totHasil = [];
            $totPenjualanRaw = [];
            $totPenjualanRes = [];
            $totPemakaian = [];
        @endphp

        @foreach($this->circulationData['daily'] as $day => $data)
            <tr>
                <td style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                    {{ $day }}
                </td>
                
                <!-- PEMBELIAN (Raw Items) -->
                @foreach($this->rawItems as $item)
                    @php 
                        $val = $data['in'][$item->id]['pembelian'] ?? 0;
                        $totPembelian[$item->id] = ($totPembelian[$item->id] ?? 0) + $val;
                    @endphp
                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-size: 10px; {{ $val > 0 ? 'font-weight: bold; color: #b91c1c;' : 'color: transparent;' }}">
                        {{ $val > 0 ? number_format($val) : '0' }}
                    </td>
                @endforeach

                <!-- HASIL PRODUKSI (Result Items) -->
                @foreach($this->resultItems as $item)
                    @php 
                        $val = $data['in'][$item->id]['hasil_produksi'] ?? 0;
                        $totHasil[$item->id] = ($totHasil[$item->id] ?? 0) + $val;
                    @endphp
                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-size: 10px; {{ $val > 0 ? 'font-weight: bold; color: #047857;' : 'color: transparent;' }}">
                        {{ $val > 0 ? number_format($val) : '0' }}
                    </td>
                @endforeach

                <!-- PENJUALAN (Raw Items) -->
                @foreach($this->rawItems as $item)
                    @php 
                        $val = $data['out'][$item->id]['penjualan'] ?? 0;
                        $totPenjualanRaw[$item->id] = ($totPenjualanRaw[$item->id] ?? 0) + $val;
                    @endphp
                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-size: 10px; {{ $val > 0 ? 'font-weight: bold; color: #1d4ed8;' : 'color: transparent;' }}">
                        {{ $val > 0 ? number_format($val) : '0' }}
                    </td>
                @endforeach
                <!-- PENJUALAN (Result Items) -->
                @foreach($this->resultItems as $item)
                    @php 
                        $val = $data['out'][$item->id]['penjualan'] ?? 0;
                        $totPenjualanRes[$item->id] = ($totPenjualanRes[$item->id] ?? 0) + $val;
                    @endphp
                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-size: 10px; {{ $val > 0 ? 'font-weight: bold; color: #1d4ed8;' : 'color: transparent;' }}">
                        {{ $val > 0 ? number_format($val) : '0' }}
                    </td>
                @endforeach

                <!-- PEMAKAIAN (Raw Items) -->
                @foreach($this->rawItems as $item)
                    @php 
                        $val = $data['out'][$item->id]['pemakaian'] ?? 0;
                        $totPemakaian[$item->id] = ($totPemakaian[$item->id] ?? 0) + $val;
                    @endphp
                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-size: 10px; {{ $val > 0 ? 'font-weight: bold; color: #7e22ce;' : 'color: transparent;' }}">
                        {{ $val > 0 ? number_format($val) : '0' }}
                    </td>
                @endforeach

                <!-- BAL (Raw Items) -->
                @foreach($this->rawItems as $item)
                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-size: 10px; font-weight: bold; {{ ($data['bal'][$item->id] ?? 0) < 0 ? 'color: #dc2626;' : 'color: #0f766e;' }}">
                        {{ number_format($data['bal'][$item->id] ?? 0) }}
                    </td>
                @endforeach
                <!-- BAL (Result Items) -->
                @foreach($this->resultItems as $item)
                    <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-size: 10px; font-weight: bold; {{ ($data['bal'][$item->id] ?? 0) < 0 ? 'color: #dc2626;' : 'color: #0f766e;' }}">
                        {{ number_format($data['bal'][$item->id] ?? 0) }}
                    </td>
                @endforeach
                
                <td style="border: 1px solid #000; padding: 1px 4px;"></td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background: #e5e7eb;">
            <td style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                TTL
            </td>
            
            <!-- Total Pembelian -->
            @foreach($this->rawItems as $item)
                <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px; color: #b91c1c;">
                    {{ ($totPembelian[$item->id] ?? 0) > 0 ? number_format($totPembelian[$item->id]) : '-' }}
                </td>
            @endforeach
            
            <!-- Total Hasil -->
            @foreach($this->resultItems as $item)
                <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px; color: #047857;">
                    {{ ($totHasil[$item->id] ?? 0) > 0 ? number_format($totHasil[$item->id]) : '-' }}
                </td>
            @endforeach
            
            <!-- Total Penjualan -->
            @foreach($this->rawItems as $item)
                <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px; color: #1d4ed8;">
                    {{ ($totPenjualanRaw[$item->id] ?? 0) > 0 ? number_format($totPenjualanRaw[$item->id]) : '-' }}
                </td>
            @endforeach
            @foreach($this->resultItems as $item)
                <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px; color: #1d4ed8;">
                    {{ ($totPenjualanRes[$item->id] ?? 0) > 0 ? number_format($totPenjualanRes[$item->id]) : '-' }}
                </td>
            @endforeach
            
            <!-- Total Pemakaian -->
            @foreach($this->rawItems as $item)
                <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px; color: #7e22ce;">
                    {{ ($totPemakaian[$item->id] ?? 0) > 0 ? number_format($totPemakaian[$item->id]) : '-' }}
                </td>
            @endforeach
            
            <!-- Final BAL -->
            @foreach($this->rawItems as $item)
                @php $f = $this->circulationData['daily'][Carbon\Carbon::create($year, $month, 1)->daysInMonth]['bal'][$item->id] ?? 0; @endphp
                <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px; color: #0f766e; background: #f0fdfa;">
                    {{ number_format($f) }}
                </td>
            @endforeach
            @foreach($this->resultItems as $item)
                @php $f = $this->circulationData['daily'][Carbon\Carbon::create($year, $month, 1)->daysInMonth]['bal'][$item->id] ?? 0; @endphp
                <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px; color: #0f766e; background: #f0fdfa;">
                    {{ number_format($f) }}
                </td>
            @endforeach
            
            <td style="border: 1px solid #000; padding: 1px 4px;"></td>
        </tr>
    </tfoot>
</table>
