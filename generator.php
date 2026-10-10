<?php

$out = <<<'BLADE'
@php
    $catName = strtolower(\App\Models\ItemCategory::find($this->item_category_id)?->name ?? '');
    $isTne = str_contains($catName, 'tne');
@endphp

<div>
    @if($isTne)
        <!-- TABLE 1: MASUK & PENJUALAN -->
        <table class="w-full border-collapse text-[10px]" style="border: 2px solid #000; margin-bottom: 20px;">
            <thead>
                <tr style="background: #e5e7eb;">
                    <th rowspan="2" style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold; width: 40px;">
                        TGL
                    </th>
                    <th colspan="{{ $this->rawItems->count() + $this->resultItems->count() }}" style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                        MASUK (IN)
                    </th>
                    <th colspan="{{ $this->rawItems->count() + $this->resultItems->count() }}" style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                        PENJUALAN
                    </th>
                </tr>
                <tr style="background: #e5e7eb;">
                    <!-- PEMBELIAN (Raw Items) -->
                    @foreach($this->rawItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach
                    <!-- HASIL PRODUKSI (Result Items) -->
                    @foreach($this->resultItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach
                    <!-- PENJUALAN (Raw Items) -->
                    @foreach($this->rawItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach
                    <!-- PENJUALAN (Result Items) -->
                    @foreach($this->resultItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <!-- SALDO AWAL -->
                <tr>
                    <td style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">STOC</td>
                    
                    <!-- MASUK (IN): Initial Stock -->
                    @foreach($this->rawItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px;">
                            {{ formatQuantity($this->circulationData['initial'][$item->id] ?? 0) }}
                        </td>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px;">
                            {{ formatQuantity($this->circulationData['initial'][$item->id] ?? 0) }}
                        </td>
                    @endforeach

                    <!-- PENJUALAN: Empty -->
                    @for ($i = 0; $i < ($this->rawItems->count() + $this->resultItems->count()); $i++)
                        <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                    @endfor
                </tr>

                <!-- DAILY ROWS -->
                @php
                    $totPembelian = [];
                    $totHasil = [];
                    $totPenjualanRaw = [];
                    $totPenjualanRes = [];
                    $totPemakaian = [];
                    foreach($this->rawItems as $it) {
                        $totPembelian[$it->id] = 0;
                        $totPenjualanRaw[$it->id] = 0;
                        $totPemakaian[$it->id] = 0;
                    }
                    foreach($this->resultItems as $it) {
                        $totHasil[$it->id] = 0;
                        $totPenjualanRes[$it->id] = 0;
                    }
                @endphp

                @foreach($this->circulationData['daily'] as $day => $data)
                    <tr>
                        <td style="border: 1px solid #000; padding: 1px 4px; text-align: center;">{{ $day }}</td>
                        
                        <!-- MASUK: PEMBELIAN -->
                        @foreach($this->rawItems as $item)
                            @php 
                                $val = $data['in'][$item->id]['pembelian'] ?? 0; 
                                $totPembelian[$item->id] += $val;
                            @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ $val > 0 ? formatQuantity($val) : '' }}
                            </td>
                        @endforeach
                        
                        <!-- MASUK: HASIL PRODUKSI -->
                        @foreach($this->resultItems as $item)
                            @php 
                                $val = $data['in'][$item->id]['hasil_produksi'] ?? 0; 
                                $totHasil[$item->id] += $val;
                            @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ $val > 0 ? formatQuantity($val) : '' }}
                            </td>
                        @endforeach

                        <!-- PENJUALAN: RAW -->
                        @foreach($this->rawItems as $item)
                            @php 
                                $val = $data['out'][$item->id]['penjualan'] ?? 0; 
                                $totPenjualanRaw[$item->id] += $val;
                            @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ $val > 0 ? formatQuantity($val) : '' }}
                            </td>
                        @endforeach
                        
                        <!-- PENJUALAN: RESULT -->
                        @foreach($this->resultItems as $item)
                            @php 
                                $val = $data['out'][$item->id]['penjualan'] ?? 0; 
                                $totPenjualanRes[$item->id] += $val;
                            @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ $val > 0 ? formatQuantity($val) : '' }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach

                <!-- JML ROW -->
                <tr>
                    <td style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">JML</td>
                    @foreach($this->rawItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ $totPembelian[$item->id] > 0 ? formatQuantity($totPembelian[$item->id]) : '' }}
                        </td>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ $totHasil[$item->id] > 0 ? formatQuantity($totHasil[$item->id]) : '' }}
                        </td>
                    @endforeach
                    @foreach($this->rawItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ $totPenjualanRaw[$item->id] > 0 ? formatQuantity($totPenjualanRaw[$item->id]) : '' }}
                        </td>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ $totPenjualanRes[$item->id] > 0 ? formatQuantity($totPenjualanRes[$item->id]) : '' }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>

        <!-- TABLE 2: PEMAKAIAN & STOK GUDANG -->
        <table class="w-full border-collapse text-[10px]" style="border: 2px solid #000;">
            <thead>
                <tr style="background: #e5e7eb;">
                    <th rowspan="2" style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold; width: 40px;">
                        TGL
                    </th>
                    <th colspan="{{ $this->rawItems->count() }}" style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                        PEMAKAIAN u/ PRODUKSI
                    </th>
                    <th colspan="{{ $this->rawItems->count() + $this->resultItems->count() }}" style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                        STOK GUDANG
                    </th>
                    <th rowspan="2" style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold; width: 60px;">
                        KET
                    </th>
                </tr>
                <tr style="background: #e5e7eb;">
                    <!-- PEMAKAIAN (Raw Items) -->
                    @foreach($this->rawItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach
                    <!-- STOK (Raw Items) -->
                    @foreach($this->rawItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach
                    <!-- STOK (Result Items) -->
                    @foreach($this->resultItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <!-- SALDO AWAL -->
                <tr>
                    <td style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">STOC</td>
                    <!-- Empty cells for Pemakaian -->
                    @for ($i = 0; $i < $this->rawItems->count(); $i++)
                        <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                    @endfor
                    <!-- Initial Stock -->
                    @foreach($this->rawItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px;">
                            {{ formatQuantity($this->circulationData['initial'][$item->id] ?? 0) }}
                        </td>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px;">
                            {{ formatQuantity($this->circulationData['initial'][$item->id] ?? 0) }}
                        </td>
                    @endforeach
                    <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                </tr>

                <!-- DAILY ROWS -->
                @foreach($this->circulationData['daily'] as $day => $data)
                    <tr>
                        <td style="border: 1px solid #000; padding: 1px 4px; text-align: center;">{{ $day }}</td>
                        
                        <!-- PEMAKAIAN: RAW -->
                        @foreach($this->rawItems as $item)
                            @php 
                                $val = $data['out'][$item->id]['pemakaian'] ?? 0; 
                                $totPemakaian[$item->id] += $val;
                            @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ $val > 0 ? formatQuantity($val) : '' }}
                            </td>
                        @endforeach
                        
                        <!-- BALANCES: RAW -->
                        @foreach($this->rawItems as $item)
                            @php $bal = $data['bal'][$item->id] ?? 0; @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ formatQuantity($bal) }}
                            </td>
                        @endforeach
                        
                        <!-- BALANCES: RESULT -->
                        @foreach($this->resultItems as $item)
                            @php $bal = $data['bal'][$item->id] ?? 0; @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ formatQuantity($bal) }}
                            </td>
                        @endforeach
                        <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                    </tr>
                @endforeach

                <!-- JML ROW -->
                <tr>
                    <td style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">JML</td>
                    @foreach($this->rawItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ $totPemakaian[$item->id] > 0 ? formatQuantity($totPemakaian[$item->id]) : '' }}
                        </td>
                    @endforeach
                    
                    @php
                        $lastDayData = end($this->circulationData['daily']);
                    @endphp
                    @foreach($this->rawItems as $item)
                        @php $bal = $lastDayData['bal'][$item->id] ?? 0; @endphp
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ formatQuantity($bal) }}
                        </td>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        @php $bal = $lastDayData['bal'][$item->id] ?? 0; @endphp
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ formatQuantity($bal) }}
                        </td>
                    @endforeach
                    <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                </tr>
            </tbody>
        </table>
    @else
        <!-- MERGED TABLE FOR AREN / NON-TNE -->
        <table class="w-full border-collapse text-[10px]" style="border: 2px solid #000;">
            <thead>
                <tr style="background: #e5e7eb;">
                    <th rowspan="2" style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold; width: 40px;">
                        TGL
                    </th>
                    <th colspan="{{ $this->rawItems->count() + $this->resultItems->count() }}" style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                        MASUK (IN)
                    </th>
                    <th colspan="{{ $this->rawItems->count() + $this->resultItems->count() }}" style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                        PENJUALAN
                    </th>
                    <th colspan="{{ $this->rawItems->count() }}" style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                        PEMAKAIAN
                    </th>
                    <th colspan="{{ $this->rawItems->count() + $this->resultItems->count() }}" style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">
                        STOK GUDANG
                    </th>
                    <th rowspan="2" style="border: 1px solid #000; padding: 1px 4px; text-align: center; vertical-align: bottom; font-weight: bold; width: 60px;">
                        KET
                    </th>
                </tr>
                <tr style="background: #e5e7eb;">
                    <!-- MASUK (IN) -->
                    @foreach($this->rawItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach

                    <!-- PENJUALAN -->
                    @foreach($this->rawItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach

                    <!-- PEMAKAIAN -->
                    @foreach($this->rawItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach

                    <!-- STOK GUDANG -->
                    @foreach($this->rawItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        <th style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 8px; width: 35px;">{{ $item->name }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <!-- SALDO AWAL -->
                <tr>
                    <td style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">STOC</td>
                    
                    <!-- MASUK (IN): Initial Stock -->
                    @foreach($this->rawItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px;">
                            {{ formatQuantity($this->circulationData['initial'][$item->id] ?? 0) }}
                        </td>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px;">
                            {{ formatQuantity($this->circulationData['initial'][$item->id] ?? 0) }}
                        </td>
                    @endforeach

                    <!-- PENJUALAN: Empty -->
                    @for ($i = 0; $i < ($this->rawItems->count() + $this->resultItems->count()); $i++)
                        <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                    @endfor

                    <!-- PEMAKAIAN: Empty -->
                    @for ($i = 0; $i < $this->rawItems->count(); $i++)
                        <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                    @endfor

                    <!-- STOK GUDANG: Initial Stock -->
                    @foreach($this->rawItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px;">
                            {{ formatQuantity($this->circulationData['initial'][$item->id] ?? 0) }}
                        </td>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold; font-size: 10px;">
                            {{ formatQuantity($this->circulationData['initial'][$item->id] ?? 0) }}
                        </td>
                    @endforeach

                    <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                </tr>

                <!-- DAILY ROWS -->
                @php
                    $totPembelian = [];
                    $totHasil = [];
                    $totPenjualanRaw = [];
                    $totPenjualanRes = [];
                    $totPemakaian = [];
                    foreach($this->rawItems as $it) {
                        $totPembelian[$it->id] = 0;
                        $totPenjualanRaw[$it->id] = 0;
                        $totPemakaian[$it->id] = 0;
                    }
                    foreach($this->resultItems as $it) {
                        $totHasil[$it->id] = 0;
                        $totPenjualanRes[$it->id] = 0;
                    }
                @endphp

                @foreach($this->circulationData['daily'] as $day => $data)
                    <tr>
                        <td style="border: 1px solid #000; padding: 1px 4px; text-align: center;">{{ $day }}</td>
                        
                        <!-- MASUK: PEMBELIAN -->
                        @foreach($this->rawItems as $item)
                            @php 
                                $val = $data['in'][$item->id]['pembelian'] ?? 0; 
                                $totPembelian[$item->id] += $val;
                            @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ $val > 0 ? formatQuantity($val) : '' }}
                            </td>
                        @endforeach
                        
                        <!-- MASUK: HASIL PRODUKSI -->
                        @foreach($this->resultItems as $item)
                            @php 
                                $val = $data['in'][$item->id]['hasil_produksi'] ?? 0; 
                                $totHasil[$item->id] += $val;
                            @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ $val > 0 ? formatQuantity($val) : '' }}
                            </td>
                        @endforeach

                        <!-- PENJUALAN: RAW -->
                        @foreach($this->rawItems as $item)
                            @php 
                                $val = $data['out'][$item->id]['penjualan'] ?? 0; 
                                $totPenjualanRaw[$item->id] += $val;
                            @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ $val > 0 ? formatQuantity($val) : '' }}
                            </td>
                        @endforeach
                        
                        <!-- PENJUALAN: RESULT -->
                        @foreach($this->resultItems as $item)
                            @php 
                                $val = $data['out'][$item->id]['penjualan'] ?? 0; 
                                $totPenjualanRes[$item->id] += $val;
                            @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ $val > 0 ? formatQuantity($val) : '' }}
                            </td>
                        @endforeach

                        <!-- PEMAKAIAN: RAW -->
                        @foreach($this->rawItems as $item)
                            @php 
                                $val = $data['out'][$item->id]['pemakaian'] ?? 0; 
                                $totPemakaian[$item->id] += $val;
                            @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ $val > 0 ? formatQuantity($val) : '' }}
                            </td>
                        @endforeach
                        
                        <!-- BALANCES: RAW -->
                        @foreach($this->rawItems as $item)
                            @php $bal = $data['bal'][$item->id] ?? 0; @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ formatQuantity($bal) }}
                            </td>
                        @endforeach
                        
                        <!-- BALANCES: RESULT -->
                        @foreach($this->resultItems as $item)
                            @php $bal = $data['bal'][$item->id] ?? 0; @endphp
                            <td style="border: 1px solid #000; padding: 1px 2px; text-align: center;">
                                {{ formatQuantity($bal) }}
                            </td>
                        @endforeach

                        <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                    </tr>
                @endforeach

                <!-- JML ROW -->
                <tr>
                    <td style="border: 1px solid #000; padding: 1px 4px; text-align: center; font-weight: bold;">JML</td>
                    @foreach($this->rawItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ $totPembelian[$item->id] > 0 ? formatQuantity($totPembelian[$item->id]) : '' }}
                        </td>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ $totHasil[$item->id] > 0 ? formatQuantity($totHasil[$item->id]) : '' }}
                        </td>
                    @endforeach
                    @foreach($this->rawItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ $totPenjualanRaw[$item->id] > 0 ? formatQuantity($totPenjualanRaw[$item->id]) : '' }}
                        </td>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ $totPenjualanRes[$item->id] > 0 ? formatQuantity($totPenjualanRes[$item->id]) : '' }}
                        </td>
                    @endforeach
                    @foreach($this->rawItems as $item)
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ $totPemakaian[$item->id] > 0 ? formatQuantity($totPemakaian[$item->id]) : '' }}
                        </td>
                    @endforeach
                    
                    @php
                        $lastDayData = end($this->circulationData['daily']);
                    @endphp
                    @foreach($this->rawItems as $item)
                        @php $bal = $lastDayData['bal'][$item->id] ?? 0; @endphp
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ formatQuantity($bal) }}
                        </td>
                    @endforeach
                    @foreach($this->resultItems as $item)
                        @php $bal = $lastDayData['bal'][$item->id] ?? 0; @endphp
                        <td style="border: 1px solid #000; padding: 1px 2px; text-align: center; font-weight: bold;">
                            {{ formatQuantity($bal) }}
                        </td>
                    @endforeach
                    <td style="border: 1px solid #000; padding: 1px 2px;"></td>
                </tr>
            </tbody>
        </table>
    @endif
</div>
BLADE;
file_put_contents('resources/views/livewire/reports/partials/print-production-circulation-table.blade.php', $out);
echo "Written";
