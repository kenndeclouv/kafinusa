<div x-data="{
    showCalculator: localStorage.getItem('calc_show') === 'true',
    x: localStorage.getItem('calc_x') !== null ? parseInt(localStorage.getItem('calc_x')) : (window.innerWidth > 400 ? window.innerWidth - 360 : 20),
    y: localStorage.getItem('calc_y') !== null ? parseInt(localStorage.getItem('calc_y')) : 80,
    dragging: false,
    startX: 0,
    startY: 0,
    init() {
        this.$watch('showCalculator', val => localStorage.setItem('calc_show', val));
        this.$watch('x', val => localStorage.setItem('calc_x', val));
        this.$watch('y', val => localStorage.setItem('calc_y', val));
    }
}" @modal-show.window="if($event.detail.name === 'calculator') showCalculator = true"
    @modal-close.window="if($event.detail.name === 'calculator') showCalculator = false">
    <!-- Draggable Floating Window -->
    <div x-show="showCalculator"
        class="fixed z-[9999] w-[320px] rounded-2xl overflow-hidden bg-zinc-100/95 dark:bg-[#202020]/90 backdrop-blur-xl shadow-2xl border border-zinc-200/50 dark:border-zinc-700/50"
        :style="`left: ${x}px; top: ${y}px;`" style="display: none;" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">
        <div class="flex flex-col text-zinc-900 dark:text-white font-sans select-none"
            @keydown.window="
                if (showCalculator && !$event.ctrlKey && !$event.metaKey) {
                    const key = $event.key;
                    if (/^[0-9\.]$/.test(key)) { $wire.input(key); }
                    if (key === '+') { $wire.operate('+'); }
                    if (key === '-') { $wire.operate('-'); }
                    if (key === '*') { $wire.operate('*'); }
                    if (key === '/') { $wire.operate('/'); }
                    if (key === 'Enter' || key === '=') { $event.preventDefault(); $wire.calculate(); }
                    if (key === 'Escape') { $wire.clear(); }
                    if (key === 'Backspace') { $wire.backspace(); }
                }
            ">
            <!-- Header (Drag Handle) -->
            <div class="flex items-center justify-between px-3 py-2 text-sm bg-transparent cursor-move"
                @mousedown="dragging = true; startX = $event.clientX - x; startY = $event.clientY - y;"
                @mousemove.window="if (dragging) { x = $event.clientX - startX; y = $event.clientY - startY; }"
                @mouseup.window="dragging = false"
                @touchstart="dragging = true; startX = $event.touches[0].clientX - x; startY = $event.touches[0].clientY - y;"
                @touchmove.window="if (dragging) { x = $event.touches[0].clientX - startX; y = $event.touches[0].clientY - startY; }"
                @touchend.window="dragging = false">
                <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-300 pointer-events-none">
                    <flux:icon.calculator class="w-4 h-4" />
                    <span class="font-medium text-xs">Calculator</span>
                </div>
                <div class="flex gap-2">
                    <button type="button" @click="showCalculator = false"
                        class="text-zinc-500 dark:text-zinc-400 hover:bg-red-500 hover:text-white dark:hover:text-white p-1 rounded-sm transition-colors">
                        <flux:icon.x-mark class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Mode -->
            <div class="px-3 py-1 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xl font-semibold">
                    <flux:dropdown>
                        <button type="button" class="p-2 hover:bg-black/5 dark:hover:bg-white/10 rounded-md transition-colors">
                            <flux:icon.bars-3 class="w-4 h-5 text-zinc-700 dark:text-white" />
                        </button>
                        <flux:menu class="w-56 bg-white dark:bg-[#2b2b2b] text-zinc-900 dark:text-white border-zinc-200 dark:border-zinc-700">
                            <flux:menu.item wire:click="setMode('standard')" class="hover:bg-zinc-100 dark:hover:bg-white/10">
                                <x-slot:icon>
                                    <flux:icon.calculator class="w-4 h-4 text-zinc-500 dark:text-white mr-2" />
                                </x-slot:icon>
                                Standard
                            </flux:menu.item>
                            <flux:menu.separator class="bg-zinc-200 dark:bg-zinc-700" />
                            <flux:menu.item wire:click="setMode('volume')" class="hover:bg-zinc-100 dark:hover:bg-white/10">
                                <x-slot:icon>
                                    <flux:icon.beaker class="w-4 h-4 text-zinc-500 dark:text-white mr-2" />
                                </x-slot:icon>
                                Volume
                            </flux:menu.item>
                            <flux:menu.item wire:click="setMode('length')" class="hover:bg-zinc-100 dark:hover:bg-white/10">
                                <x-slot:icon><flux:icon.arrows-right-left
                                        class="w-4 h-4 text-zinc-500 dark:text-white mr-2" /></x-slot:icon>
                                Length
                            </flux:menu.item>
                            <flux:menu.item wire:click="setMode('mass')" class="hover:bg-zinc-100 dark:hover:bg-white/10">
                                <x-slot:icon>
                                    <flux:icon.scale class="w-4 h-4 text-zinc-500 dark:text-white mr-2" />
                                </x-slot:icon>
                                Weight and mass
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                    <span>{{ $mode === 'mass' ? 'Weight and mass' : ucfirst($mode) }}</span>
                </div>

            </div>

            @if ($mode === 'standard')
                <!-- Display Standard -->
                <div class="px-4 pt-2 pb-4 flex flex-col items-end justify-end h-24">
                    <div class="text-zinc-500 dark:text-zinc-400 text-sm h-5 overflow-hidden font-medium">{{ $equation }}</div>
                    <div class="text-5xl font-semibold tracking-tight truncate w-full text-right"
                        style="font-family: 'Segoe UI', system-ui, sans-serif;">{{ $display }}</div>
                </div>

                <!-- Buttons Grid -->
                <div class="p-1 pb-1">


                    <!-- Main grid -->
                    <div class="grid grid-cols-4 gap-[2px]">
                        <!-- Row 1 -->
                        <button wire:click="percent"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-3 text-sm rounded-md transition-colors shadow-sm dark:shadow-none">%</button>
                        <button wire:click="clearEntry"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-3 text-sm rounded-md transition-colors shadow-sm dark:shadow-none">CE</button>
                        <button wire:click="clear"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-3 text-sm rounded-md transition-colors shadow-sm dark:shadow-none">C</button>
                        <button wire:click="backspace"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-3 rounded-md transition-colors flex items-center justify-center shadow-sm dark:shadow-none">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                            </svg>
                        </button>

                        <!-- Row 2 -->
                        <button wire:click="inverse"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-3 text-sm rounded-md transition-colors flex items-center justify-center shadow-sm dark:shadow-none">
                            <span class="italic font-serif">1/x</span>
                        </button>
                        <button wire:click="square"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-3 text-sm rounded-md transition-colors flex items-center justify-center shadow-sm dark:shadow-none">
                            <span class="italic font-serif">x²</span>
                        </button>
                        <button wire:click="sqrt"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-3 text-sm rounded-md transition-colors flex items-center justify-center shadow-sm dark:shadow-none">
                            <span class="italic font-serif">²√x</span>
                        </button>
                        <button wire:click="operate('/')"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-3 text-xl rounded-md transition-colors font-light shadow-sm dark:shadow-none">÷</button>

                        <!-- Row 3 -->
                        <button wire:click="input('7')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-3 text-lg rounded-md transition-colors shadow-sm dark:shadow-none">7</button>
                        <button wire:click="input('8')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-3 text-lg rounded-md transition-colors shadow-sm dark:shadow-none">8</button>
                        <button wire:click="input('9')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-3 text-lg rounded-md transition-colors shadow-sm dark:shadow-none">9</button>
                        <button wire:click="operate('*')"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-3 text-xl rounded-md transition-colors font-light shadow-sm dark:shadow-none">×</button>

                        <!-- Row 4 -->
                        <button wire:click="input('4')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-3 text-lg rounded-md transition-colors shadow-sm dark:shadow-none">4</button>
                        <button wire:click="input('5')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-3 text-lg rounded-md transition-colors shadow-sm dark:shadow-none">5</button>
                        <button wire:click="input('6')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-3 text-lg rounded-md transition-colors shadow-sm dark:shadow-none">6</button>
                        <button wire:click="operate('-')"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-3 text-2xl rounded-md transition-colors font-light shadow-sm dark:shadow-none">−</button>

                        <!-- Row 5 -->
                        <button wire:click="input('1')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-3 text-lg rounded-md transition-colors shadow-sm dark:shadow-none">1</button>
                        <button wire:click="input('2')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-3 text-lg rounded-md transition-colors shadow-sm dark:shadow-none">2</button>
                        <button wire:click="input('3')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-3 text-lg rounded-md transition-colors shadow-sm dark:shadow-none">3</button>
                        <button wire:click="operate('+')"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-3 text-xl rounded-md transition-colors font-light shadow-sm dark:shadow-none">+</button>

                        <!-- Row 6 -->
                        <button wire:click="negate"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-3 text-lg rounded-md transition-colors shadow-sm dark:shadow-none">+/-</button>
                        <button wire:click="input('0')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-3 text-lg rounded-md transition-colors shadow-sm dark:shadow-none">0</button>
                        <button wire:click="input('.')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-3 text-lg rounded-md transition-colors shadow-sm dark:shadow-none">,</button>
                        <button wire:click="calculate"
                            class="bg-accent hover:brightness-110 text-accent-foreground font-semibold py-3 text-2xl rounded-md transition-colors shadow-sm dark:shadow-none">=</button>
                    </div>
                </div>
            @else
                <!-- Display Converter -->
                <div class="px-4 pt-4 pb-2 flex flex-col h-[220px] justify-center gap-6">
                    <!-- From -->
                    <div class="flex flex-col gap-1">
                        <div class="text-4xl font-semibold tracking-tight truncate w-full"
                            style="font-family: 'Segoe UI', system-ui, sans-serif;">{{ $display }}</div>
                        <select wire:model.live="fromUnit"
                            class="bg-transparent text-sm font-medium text-zinc-900 dark:text-white border-none focus:ring-0 p-0 cursor-pointer outline-none">
                            @foreach ($this->availableUnits as $val => $label)
                                <option value="{{ $val }}" class="bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white">{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- To -->
                    <div class="flex flex-col gap-1">
                        <div class="text-4xl font-semibold tracking-tight truncate w-full text-zinc-500 dark:text-zinc-400"
                            style="font-family: 'Segoe UI', system-ui, sans-serif;">{{ $this->convertedValue }}</div>
                        <select wire:model.live="toUnit"
                            class="bg-transparent text-sm font-medium text-zinc-900 dark:text-white border-none focus:ring-0 p-0 cursor-pointer outline-none">
                            @foreach ($this->availableUnits as $val => $label)
                                <option value="{{ $val }}" class="bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white">{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Buttons Grid Converter -->
                <div class="p-1 pb-1 mt-1">
                    <div class="grid grid-cols-3 gap-[2px]">
                        <button wire:click="clearEntry"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-4 text-sm rounded-md transition-colors col-span-1 shadow-sm dark:shadow-none">CE</button>
                        <button wire:click="backspace"
                            class="bg-black/5 dark:bg-[#323232] hover:bg-black/10 dark:hover:bg-[#3b3b3b] py-4 rounded-md transition-colors flex items-center justify-center col-span-2 shadow-sm dark:shadow-none">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                            </svg>
                        </button>

                        <!-- Numpad -->
                        <button wire:click="input('7')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-4 text-xl rounded-md transition-colors shadow-sm dark:shadow-none">7</button>
                        <button wire:click="input('8')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-4 text-xl rounded-md transition-colors shadow-sm dark:shadow-none">8</button>
                        <button wire:click="input('9')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-4 text-xl rounded-md transition-colors shadow-sm dark:shadow-none">9</button>

                        <button wire:click="input('4')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-4 text-xl rounded-md transition-colors shadow-sm dark:shadow-none">4</button>
                        <button wire:click="input('5')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-4 text-xl rounded-md transition-colors shadow-sm dark:shadow-none">5</button>
                        <button wire:click="input('6')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-4 text-xl rounded-md transition-colors shadow-sm dark:shadow-none">6</button>

                        <button wire:click="input('1')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-4 text-xl rounded-md transition-colors shadow-sm dark:shadow-none">1</button>
                        <button wire:click="input('2')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-4 text-xl rounded-md transition-colors shadow-sm dark:shadow-none">2</button>
                        <button wire:click="input('3')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-4 text-xl rounded-md transition-colors shadow-sm dark:shadow-none">3</button>

                        <button
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-4 text-xl rounded-md transition-colors col-span-1 cursor-default shadow-sm dark:shadow-none"></button>
                        <button wire:click="input('0')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-4 text-xl rounded-md transition-colors shadow-sm dark:shadow-none">0</button>
                        <button wire:click="input('.')"
                            class="bg-white dark:bg-[#3b3b3b] hover:bg-black/5 dark:hover:bg-[#323232] font-semibold py-4 text-xl rounded-md transition-colors shadow-sm dark:shadow-none">,</button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
