<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Template Jadwal Mingguan</flux:heading>
            <flux:subheading>Atur siklus jadwal kunjungan sales per minggu.</flux:subheading>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto mt-4 sm:mt-0">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari pasar atau sales..."
                icon="magnifying-glass" class="w-full sm:w-64" />
            <flux:button x-on:click="$flux.modal('add-schedule-modal').show()" variant="primary" icon="plus" class="ms-auto w-full sm:w-auto">
                Tambah Jadwal
            </flux:button>
        </div>
    </div>

    <!-- Tampilan Jadwal Per Sales -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($this->sales as $sale)
            @php 
                $hasSchedule = isset($this->schedules[$sale->id]);
            @endphp
            
            @if($hasSchedule)
            <div class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden flex flex-col">
                <div class="bg-white dark:bg-white/5 px-4 py-4 border-b border-zinc-200 dark:border-white/5">
                    <div class="flex items-center gap-3">
                        <flux:avatar size="sm" :name="$sale->name" />
                        <div>
                            <flux:heading size="lg">{{ $sale->name }}</flux:heading>
                            <span class="text-xs text-zinc-500">{{ $sale->position }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto flex-1">
                    <flux:table class="[&_th:first-child]:!ps-4 [&_td:first-child]:!ps-4 [&_th:last-child]:!pe-4 [&_td:last-child]:!pe-4">
                        <flux:table.columns>
                            <flux:table.column>Hari</flux:table.column>
                            <flux:table.column>Pasar</flux:table.column>
                            <flux:table.column>Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($this->days as $dayVal => $dayName)
                                @if(isset($this->schedules[$sale->id][$dayVal]))
                                    @foreach($this->schedules[$sale->id][$dayVal] as $index => $sch)
                                        <flux:table.row :key="$sch->id" class="hover:bg-white dark:hover:bg-zinc-800/50 transition-colors">
                                            <flux:table.cell>
                                                @if($index === 0)
                                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $dayName }}</span>
                                                @endif
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                <span class="text-zinc-900 dark:text-white">{{ $sch->market->name }}</span>
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                <x-delete-modal action="removeSchedule({{ $sch->id }})" id="delete-schedule-{{ $sch->id }}" requireSlide="true">
                                                    <flux:button variant="ghost" size="sm" icon="trash" class="text-red-500 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10" />
                                                </x-delete-modal>
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforeach
                                @endif
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </div>
            @endif
        @endforeach
    </div>

    @if(empty($this->schedules))
        <div class="text-center py-12 bg-zinc-50 dark:bg-white/5 rounded-2xl border border-dashed border-zinc-300 dark:border-white/20">
            <flux:icon.calendar-days class="mx-auto size-12 text-zinc-400" />
            <flux:heading size="lg" class="mt-4">Belum ada jadwal</flux:heading>
            <flux:text class="mt-2 mb-4 text-sm text-zinc-500 dark:text-zinc-400">Klik tombol Tambah Jadwal untuk mulai mengatur kunjungan rutin.</flux:text>
            <div class="mt-2">
                <flux:button x-on:click="$flux.modal('add-schedule-modal').show()" variant="primary" icon="plus">Tambah Jadwal</flux:button>
            </div>
        </div>
    @endif

    <!-- Modal Tambah Jadwal -->
    <flux:modal :closable="false" scroll="body" name="add-schedule-modal" class="md:max-w-xl !rounded-3xl">
        <form wire:submit="addSchedule">
            <flux:heading>Tambah Jadwal</flux:heading>
            <flux:description>Pilih sales, hari, dan pasar yang dituju.</flux:description>

            <div class="mt-6 space-y-6">
                <div class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">
                    
                    <!-- Sales -->
                    <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Sales
                        </span>
                        <div class="flex-1">
                            <x-searchable-select wire:model="salesId" :options="$this->sales->pluck('name', 'id')->toArray()" variant="ios" placeholder="Pilih sales..." />
                        </div>
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    </label>
                    <x-error-ios name="salesId" />

                    <!-- Hari -->
                    <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Hari
                        </span>
                        <div class="flex-1">
                            <x-searchable-select wire:model="dayOfWeek" :options="$days" variant="ios" placeholder="Pilih hari..." />
                        </div>
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    </label>
                    <x-error-ios name="dayOfWeek" />

                    <!-- Pasar -->
                    <label class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Pasar
                        </span>
                        <div class="flex-1">
                            <x-searchable-select wire:model="marketId" :options="$this->markets->pluck('name', 'id')->toArray()" variant="ios" placeholder="Pilih pasar..." />
                        </div>
                    </label>
                    <x-error-ios name="marketId" />

                </div>
            </div>

            <div class="mt-8 flex flex-col gap-2">
                <flux:button type="submit" variant="primary" class="!rounded-full">Simpan</flux:button>
                <flux:button x-on:click="$flux.modal('add-schedule-modal').close()" variant="outline" class="!rounded-full">
                    Tutup
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
