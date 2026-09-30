<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 no-print">
        <div>
            <flux:heading size="xl" level="1">Sirkulasi Stok Bulanan</flux:heading>
            <flux:description>Rangkuman mutasi barang masuk, keluar, dan sisa saldo harian.</flux:description>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="w-full sm:w-48">
                <x-searchable-select wire:model.live="warehouse_id" :options="$this->warehouses" variant="ios" placeholder="Pilih Gudang" />
            </div>

            <div class="w-full sm:w-48">
                <x-searchable-select wire:model.live="item_category_id" :options="$this->categories" variant="ios" placeholder="Pilih Kategori" />
            </div>

            <div class="w-full sm:w-32">
                <x-searchable-select wire:model.live="month" :options="$this->months" variant="ios" :searchable="false" />
            </div>

            <div class="w-full sm:w-28">
                <x-searchable-select wire:model.live="year" :options="$this->years" variant="ios" :searchable="false" />
            </div>

            <flux:button href="{{ route('reports.print-stock-circulation', ['month' => $month, 'year' => $year, 'warehouse_id' => $warehouse_id, 'item_category_id' => $item_category_id]) }}" target="_blank" variant="primary" icon="printer" class="w-full sm:w-auto">
                Cetak
            </flux:button>
        </div>
    </div>

    <!-- Print Header (Only visible when printing) -->
    <div class="hidden print:block mb-6 text-center">
        <h1 class="text-2xl font-bold text-black uppercase">SIRKULASI {{ $this->item_category_id ? strtoupper($this->categories[$this->item_category_id] ?? 'STOK') : 'STOK' }} HARIAN</h1>
        <h2 class="text-lg font-bold text-black uppercase mt-1">UD. CITRA MAJU BERSAMA</h2>
        <div class="mt-4 text-right text-black font-medium">
            BULAN : {{ \Carbon\Carbon::create()->month((int)$month)->translatedFormat('F') }} {{ $year }}
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/5 shadow-xs print:shadow-none print:border-none overflow-hidden print:overflow-visible">
        @if(!$this->circulationData)
            <div class="py-16 flex flex-col items-center justify-center text-center">
                <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-4 mb-4">
                    <flux:icon.clipboard-document-list class="w-8 h-8 text-zinc-500 dark:text-zinc-400" />
                </div>
                <flux:heading size="lg">Tidak Ada Data</flux:heading>
                <flux:text class="mt-2 max-w-sm text-zinc-500 dark:text-zinc-400">
                    Silakan pilih Gudang dan Kategori Barang yang memiliki data item.
                </flux:text>
            </div>
        @elseif($this->isProductionCategory)
            @include('livewire.reports.partials.production-circulation-table')
        @else
            @include('livewire.reports.partials.standard-circulation-table')
        @endif
    </div>
</div>
