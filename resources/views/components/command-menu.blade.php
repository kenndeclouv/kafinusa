<div
    x-data="{
        open: false,
        selectedIndex: 0,
        itemsCount: 0,
        init() {
            this.$watch('open', value => {
                if (value) {
                    this.selectedIndex = 0;
                    this.$nextTick(() => {
                        this.$refs.searchInput.focus();
                    });
                }
            });
            
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    this.open = true;
                }
            });
        },
        updateCount() {
            this.itemsCount = document.querySelectorAll('.command-item').length;
            if (this.selectedIndex >= this.itemsCount) {
                this.selectedIndex = 0;
            }
        },
        navigate(direction) {
            if (direction === 'down') {
                this.selectedIndex = (this.selectedIndex + 1) % this.itemsCount;
            } else if (direction === 'up') {
                this.selectedIndex = (this.selectedIndex - 1 + this.itemsCount) % this.itemsCount;
            }
            this.scrollToSelected();
        },
        scrollToSelected() {
            this.$nextTick(() => {
                const selectedEl = document.querySelectorAll('.command-item')[this.selectedIndex];
                if (selectedEl) {
                    selectedEl.scrollIntoView({ block: 'nearest' });
                }
            });
        },
        selectItem() {
            const selectedEl = document.querySelectorAll('.command-item')[this.selectedIndex];
            if (selectedEl) {
                selectedEl.click();
            }
        }
    }"
    @open-command-menu.window="open = true"
    x-init="$watch('open', () => updateCount())"
>
    <!-- Modal Backdrop -->
    <div
        x-show="open"
        x-transition.opacity.duration.300ms
        class="fixed inset-0 z-[100] bg-zinc-900/40 dark:bg-black/60 backdrop-blur-sm"
        @click="open = false"
        style="display: none;"
    >
        <!-- Modal Panel -->
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-4"
            class="absolute w-11/12 top-[10%] left-1/2 -translate-x-1/2 max-w-2xl bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl border border-zinc-200 dark:border-white/10 overflow-hidden flex flex-col max-h-[80vh]"
            @click.stop
            @keydown.escape.window="open = false"
            @keydown.arrow-down.prevent="navigate('down')"
            @keydown.arrow-up.prevent="navigate('up')"
            @keydown.enter.prevent="selectItem()"
        >
            <!-- Search Input -->
            <div class="relative flex items-center px-4 border-b border-zinc-100 dark:border-white/5">
                <flux:icon.magnifying-glass class="w-6 h-6 text-zinc-400" />
                <input 
                    x-ref="searchInput"
                    wire:model.live.debounce.150ms="search"
                    type="text"
                    class="w-full bg-transparent border-0 focus:ring-0 focus:outline-none !ring-0 !outline-none text-lg py-5 px-4 text-zinc-900 dark:text-white placeholder:text-zinc-400"
                    placeholder="Search commands, navigation, tools..."
                    autocomplete="off"
                >
                <div class="flex items-center gap-1">
                    <kbd class="hidden sm:inline-flex items-center justify-center rounded border border-zinc-200 bg-zinc-100 px-1.5 py-0.5 text-xs font-medium text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">ESC</kbd>
                </div>
            </div>

            <!-- Results Area -->
            <div class="overflow-y-auto flex-1 p-2" x-effect="updateCount()">
                @if($this->navigationItems->isEmpty())
                    <div class="py-14 text-center">
                        <flux:icon.magnifying-glass class="mx-auto h-8 w-8 text-zinc-400" />
                        <h3 class="mt-4 text-sm font-semibold text-zinc-900 dark:text-white">Tidak ada hasil</h3>
                        <p class="mt-2 text-sm text-zinc-500">Coba gunakan kata kunci lain.</p>
                    </div>
                @else
                    @php $globalIndex = 0; @endphp
                    @foreach($this->navigationItems as $group => $items)
                        <div class="mb-2 last:mb-0">
                            <div class="px-3 py-2 text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                {{ $group }}
                            </div>
                            <div class="space-y-1">
                                @foreach($items as $item)
                                    <a
                                        @if($item['route'])
                                            href="{{ route($item['route']) }}"
                                            wire:navigate
                                        @else
                                            href="#"
                                            @click.prevent="$dispatch('modal-show', { name: 'calculator' }); open = false;"
                                        @endif
                                        class="command-item flex items-center gap-3 px-3 py-3 rounded-lg text-sm text-zinc-700 dark:text-zinc-300 cursor-pointer transition-colors"
                                        :class="{ 'bg-accent/10 text-accent': selectedIndex === {{ $globalIndex }} }"
                                        @mouseenter="selectedIndex = {{ $globalIndex }}"
                                        @click="open = false"
                                    >
                                        <div class="flex items-center justify-center w-8 h-8 rounded-md bg-zinc-100 dark:bg-white/5"
                                             :class="{ 'bg-accent/20': selectedIndex === {{ $globalIndex }} }">
                                            <flux:icon :name="$item['icon']" class="w-5 h-5" />
                                        </div>
                                        <span class="font-medium">{{ $item['label'] }}</span>
                                        
                                        <!-- Enter icon indicator -->
                                        <span x-show="selectedIndex === {{ $globalIndex }}" class="ml-auto text-xs text-accent flex items-center gap-1" style="display: none;">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        </span>
                                    </a>
                                    @php $globalIndex++; @endphp
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            
            <!-- Footer -->
            <div class="px-4 py-3 border-t border-zinc-100 dark:border-white/5 bg-zinc-50 dark:bg-zinc-800/50 flex items-center justify-between">
                <div class="flex items-center gap-4 text-xs text-zinc-500">
                    <span class="flex items-center gap-1"><kbd class="bg-zinc-200 dark:bg-zinc-700 rounded px-1">↑</kbd> <kbd class="bg-zinc-200 dark:bg-zinc-700 rounded px-1">↓</kbd> untuk navigasi</span>
                    <span class="flex items-center gap-1"><kbd class="bg-zinc-200 dark:bg-zinc-700 rounded px-1">↵</kbd> untuk memilih</span>
                </div>
                <div class="text-xs text-zinc-400 font-medium">Spotlight</div>
            </div>
        </div>
    </div>
</div>
