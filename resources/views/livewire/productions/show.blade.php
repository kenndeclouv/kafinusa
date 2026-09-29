<div>
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" icon="arrow-left" href="{{ route('productions.index') }}" wire:navigate class="!px-2 hidden md:flex" />
            <div>
                <flux:heading size="xl">Produksi: {{ $warehouse->name }}</flux:heading>
                <flux:subheading>Input Spreadsheet Harian</flux:subheading>
            </div>
        </div>
        <div class="flex flex-col md:flex-row items-center gap-3 w-full lg:w-auto">
            <x-date-picker wire:model.live="date" type="date" variant="ios" class="w-full md:w-40" />
            <x-searchable-select wire:model.live="selected_category_id" :options="$this->categories" variant="ios" placeholder="Pilih Kategori Produksi..." class="w-full md:w-48" />
            @if($selected_category_id)
                <flux:button variant="primary" icon="printer" 
                    href="{{ route('productions.print', ['warehouse' => $warehouse->id, 'date' => $date, 'category' => $selected_category_id]) }}" wire:navigate
                    class="w-full md:w-auto">
                    Cetak
                </flux:button>
            @endif
        </div>
    </div>

    @if(!$selected_category_id)
        <div class="py-16 flex flex-col items-center justify-center text-center rounded-3xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5">
            <div class="rounded-full bg-white dark:bg-zinc-800 p-4 mb-4 shadow-sm border border-zinc-200 dark:border-white/10">
                <flux:icon.table-cells class="w-8 h-8 text-zinc-400" />
            </div>
            <flux:heading size="lg">Pilih Kategori Produksi</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-500">Pilih Kategori Produksi di atas untuk mulai mengisi spreadsheet.</flux:text>
        </div>
    @elseif(empty($grid))
        <div class="py-16 flex flex-col items-center justify-center text-center rounded-3xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5">
            <div class="rounded-full bg-white dark:bg-zinc-800 p-4 mb-4 shadow-sm border border-zinc-200 dark:border-white/10">
                <flux:icon.users class="w-8 h-8 text-zinc-400" />
            </div>
            <flux:heading size="lg">Belum Ada Tim</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-500">Belum ada tim yang ditugaskan ke gudang ini.</flux:text>
            <flux:button href="{{ route('teams.index') }}" variant="primary" class="mt-6" wire:navigate>Ke Master Tim</flux:button>
        </div>
    @else
        <div>
            <div class="rounded-2xl border border-zinc-200 dark:border-white/10 overflow-hidden bg-white dark:bg-zinc-900 shadow-sm">
                <div class="bg-zinc-50 dark:bg-zinc-800/80 px-6 py-4 border-b border-zinc-200 dark:border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:icon.document-text class="w-5 h-5 text-zinc-400" />
                        <span class="font-bold text-zinc-800 dark:text-zinc-200 tracking-wide text-sm">{{ $this->spreadsheet_title }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-zinc-500">
                        <flux:icon.check-circle class="w-4 h-4 text-green-500" />
                        Tersimpan Otomatis
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-zinc-700 dark:text-zinc-300 uppercase bg-zinc-100/50 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th class="px-4 py-3 font-semibold z-10 bg-zinc-100 dark:bg-zinc-800 border-r border-zinc-200 dark:border-zinc-700 w-32 shadow-[1px_0_0_0_rgba(0,0,0,0.05)] dark:shadow-none">NO / TIM</th>
                                @foreach($this->result_items as $item)
                                    <th class="px-2 py-3 font-semibold text-center min-w-[80px]">{{ $item->name }}</th>
                                @endforeach
                                <th class="px-3 py-3 font-semibold text-center border-l border-zinc-200 dark:border-zinc-700 min-w-[140px]">BAHAN BAKU</th>
                                <th class="px-3 py-3 font-semibold text-center border-l border-zinc-200 dark:border-zinc-700 min-w-[100px] bg-accent/5">TTL BAHAN</th>
                                <th class="px-4 py-3 font-semibold text-center min-w-[140px] z-10 bg-zinc-100 dark:bg-zinc-800 border-l border-zinc-200 dark:border-zinc-700 shadow-[-1px_0_0_0_rgba(0,0,0,0.05)] dark:shadow-none">KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($grid as $teamId => $row)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-white/[0.02] transition-colors group">
                                    <td class="px-4 py-2 z-10 bg-white dark:bg-zinc-900 group-hover:bg-zinc-50 dark:group-hover:bg-zinc-800 border-r border-zinc-200 dark:border-zinc-700 font-medium">
                                        <div class="flex items-center gap-2">
                                            <span class="text-zinc-400 text-xs w-4">{{ $loop->iteration }}</span>
                                            <span class="truncate">{{ $row['team_name'] }}</span>
                                        </div>
                                    </td>
                                    
                                    @foreach($this->result_items as $item)
                                        <td class="px-1 py-1.5">
                                            <input type="number" step="0.01" min="0" wire:key="res-{{ $teamId }}-{{ $item->id }}" wire:model.live.debounce.500ms="grid.{{ $teamId }}.results.{{ $item->id }}"
                                                class="w-full text-center bg-transparent border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 focus:bg-white dark:focus:bg-zinc-800 focus:border-accent focus:ring-1 focus:ring-accent rounded-md transition-all py-1.5 text-[15px]"
                                                placeholder="" />
                                        </td>
                                    @endforeach
                                    
                                    <td class="px-2 py-1.5 border-l border-zinc-200 dark:border-zinc-700">
                                        <x-searchable-select wire:model.live="grid.{{ $teamId }}.raw_item_id" :options="$this->raw_items->mapWithKeys(fn($i) => [$i->id => ($i->category->name ?? '') . ' ' . $i->name])" size="sm" placeholder="Pilih..." />
                                    </td>
                                    
                                    <td class="px-2 py-1.5 border-l border-zinc-200 dark:border-zinc-700 bg-accent/5">
                                        <div class="w-[100px] mx-auto">
                                            <x-stepper step="1" min="0" variant="inside" wire:key="raw-{{ $teamId }}" wire:model.live.debounce.500ms="grid.{{ $teamId }}.raw_quantity" />
                                        </div>
                                    </td>
                                    
                                    @if($loop->first)
                                        <td rowspan="{{ count($grid) }}" class="p-4 align-top border-l border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 min-w-[200px] w-[240px]">
                                            <div class="flex flex-col gap-1.5 font-medium text-[15px] tracking-tight text-zinc-700 dark:text-zinc-300 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="w-8 shrink-0">L</span>
                                                    <span class="w-4 text-center">=</span>
                                                    <span class="ml-2 flex-1">{{ $this->formatQty($this->summary['L']) }} kg</span>
                                                </div>
                                                
                                                <div class="flex items-center">
                                                    <span class="w-8 shrink-0">P</span>
                                                    <span class="w-4 text-center">=</span>
                                                    <span class="ml-2">{{ $this->formatQty($this->summary['P']) }} kg</span>
                                                    
                                                    <input type="text" wire:model.live.debounce.1000ms="global_notes" 
                                                        class="ml-2 flex-1 min-w-[60px] max-w-[100px] bg-transparent border-b border-zinc-300 dark:border-zinc-600 focus:border-accent focus:ring-0 px-1 py-0 text-[15px] transition-colors placeholder-zinc-400"
                                                        placeholder="/ sby" />
                                                </div>
                                                
                                                <div class="flex items-center pt-1 mt-1">
                                                    <span class="w-8 shrink-0"></span>
                                                    <span class="w-4 text-center"></span>
                                                    <span class="ml-2 flex-1 font-bold {{ $this->summary['selisih'] < 0 ? 'text-red-500' : 'text-zinc-700 dark:text-zinc-300' }}">
                                                        {{ $this->summary['selisih'] == 0 ? '-' : $this->formatQty($this->summary['selisih']) . ' kg' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-100/50 dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700 font-bold">
                            <tr>
                                <td class="px-4 py-3 z-10 bg-zinc-100/50 dark:bg-zinc-800 border-r border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 text-right">TTL</td>
                                @foreach($this->result_items as $item)
                                    @php
                                        $colTotal = collect($grid)->sum(fn($row) => (float)($row['results'][$item->id] ?? 0));
                                    @endphp
                                    <td class="px-2 py-3 text-center text-accent">{{ $colTotal > 0 ? $colTotal : '' }}</td>
                                @endforeach
                                <td class="px-2 py-3 border-l border-zinc-200 dark:border-zinc-700"></td>
                                <td class="px-2 py-3 text-center bg-accent/10 border-l border-zinc-200 dark:border-zinc-700">
                                    @php
                                        $rawTotal = collect($grid)->sum(fn($row) => (float)($row['raw_quantity'] ?? 0));
                                    @endphp
                                    {{ $rawTotal > 0 ? $rawTotal : '' }}
                                </td>
                                <td class="px-4 py-3 border-l border-zinc-200 dark:border-zinc-700"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
