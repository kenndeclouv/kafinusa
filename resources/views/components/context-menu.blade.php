<div x-data="{
        show: false,
        x: 0,
        y: 0,
        open(e) {
            // Check if clicking inside an input or textarea, if so allow native menu for copy/paste
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName) || e.target.isContentEditable) {
                return;
            }

            this.x = e.clientX;
            this.y = e.clientY;
            this.show = true;
            
            // Adjust menu position if it overflows the window
            this.$nextTick(() => {
                const el = this.$refs.menu;
                const rect = el.getBoundingClientRect();
                
                if (this.x + rect.width > window.innerWidth) {
                    this.x = window.innerWidth - rect.width - 10;
                }
                if (this.y + rect.height > window.innerHeight) {
                    this.y = window.innerHeight - rect.height - 10;
                }
            });
        },
        close() {
            this.show = false;
        },
        goBack() { window.history.back(); this.close(); },
        goForward() { window.history.forward(); this.close(); },
        reload() { window.location.reload(); this.close(); },
        setTheme(theme) {
            if (this.$flux) {
                this.$flux.appearance = theme;
            } else if (window.Flux) {
                window.Flux.applyAppearance(theme);
            } else {
                // Fallback if Flux is not loaded
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('flux.appearance', 'dark');
                } else if (theme === 'light') {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('flux.appearance', 'light');
                } else {
                    localStorage.removeItem('flux.appearance');
                    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            }
            this.close();
        }
    }"
    @contextmenu.window.prevent="open($event)"
    @click.window="if (show) close()"
    @keydown.escape.window="if (show) close()"
    @scroll.window="if (show) close()"
>
    <!-- macOS style context menu -->
    <div 
        x-show="show" 
        x-ref="menu"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed z-[9999] w-[260px] bg-white/70 dark:bg-zinc-800/70 backdrop-blur-2xl border border-zinc-200/60 dark:border-zinc-700/60 rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] p-1.5 text-sm text-zinc-800 dark:text-zinc-200"
        x-bind:style="`left: ${x}px; top: ${y}px;`"
        style="display: none;"
    >
        <button @click="goBack" class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-accent hover:text-white group transition-none cursor-default">
            <flux:icon.arrow-left variant="micro" class="size-4 text-zinc-500 dark:text-zinc-400 group-hover:text-white" />
            <span class="font-medium">Kembali</span>
            <span class="ml-auto text-xs text-zinc-400 dark:text-zinc-500 group-hover:text-white/70">Alt + ←</span>
        </button>
        <button @click="goForward" class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-accent hover:text-white group transition-none cursor-default">
            <flux:icon.arrow-right variant="micro" class="size-4 text-zinc-500 dark:text-zinc-400 group-hover:text-white" />
            <span class="font-medium">Maju</span>
            <span class="ml-auto text-xs text-zinc-400 dark:text-zinc-500 group-hover:text-white/70">Alt + →</span>
        </button>
        <button @click="reload" class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-accent hover:text-white group transition-none cursor-default">
            <flux:icon.arrow-path variant="micro" class="size-4 text-zinc-500 dark:text-zinc-400 group-hover:text-white" />
            <span class="font-medium">Muat Ulang</span>
            <span class="ml-auto text-xs text-zinc-400 dark:text-zinc-500 group-hover:text-white/70">Ctrl + R</span>
        </button>

        <div class="h-[1px] bg-zinc-200 dark:bg-zinc-700/80 my-1 mx-2"></div>

        <button x-data @click="$dispatch('open-command-menu'); close()" class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-accent hover:text-white group transition-none cursor-default">
            <flux:icon.magnifying-glass variant="micro" class="size-4 text-zinc-500 dark:text-zinc-400 group-hover:text-white" />
            <span class="font-medium">Pencarian Global</span>
            <span class="ml-auto text-xs text-zinc-400 dark:text-zinc-500 group-hover:text-white/70">⌘K</span>
        </button>

        <a href="{{ route('dashboard') }}" wire:navigate class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-accent hover:text-white group transition-none cursor-default">
            <flux:icon.home variant="micro" class="size-4 text-zinc-500 dark:text-zinc-400 group-hover:text-white" />
            <span class="font-medium">Beranda</span>
        </a>

        <div class="h-[1px] bg-zinc-200 dark:bg-zinc-700/80 my-1 mx-2"></div>

        <button @click="setTheme('light')" class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-accent hover:text-white group transition-none cursor-default">
            <flux:icon.sun variant="micro" class="size-4 text-zinc-500 dark:text-zinc-400 group-hover:text-white" />
            <span class="font-medium">Terang</span>
        </button>
        <button @click="setTheme('dark')" class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-accent hover:text-white group transition-none cursor-default">
            <flux:icon.moon variant="micro" class="size-4 text-zinc-500 dark:text-zinc-400 group-hover:text-white" />
            <span class="font-medium">Gelap</span>
        </button>
        <button @click="setTheme('system')" class="w-full flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-accent hover:text-white group transition-none cursor-default">
            <flux:icon.computer-desktop variant="micro" class="size-4 text-zinc-500 dark:text-zinc-400 group-hover:text-white" />
            <span class="font-medium">Mengikuti Sistem</span>
        </button>
    </div>
</div>
