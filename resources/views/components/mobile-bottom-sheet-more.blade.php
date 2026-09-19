<div x-data="{ open: false }" @toggle-mobile-more.window="open = !open" @close-mobile-more.window="open = false" x-show="open"
    class="fixed inset-0 z-[60] lg:hidden flex flex-col justify-end" style="display: none;">

    <!-- Backdrop -->
    <div x-show="open" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="open = false"
        class="absolute inset-0 bg-black/20 dark:bg-black/40 backdrop-blur-[2px]"></div>

    <!-- Floating Context Menu (iOS Style) -->
    <div x-show="open" x-transition:enter="transition-all ease-out duration-300 transform"
        x-transition:enter-start="translate-y-[120%] opacity-0"
        x-transition:enter-end="translate-y-0 scale-100 opacity-100"
        x-transition:leave="transition-all ease-in duration-300 transform"
        x-transition:leave-start="translate-y-0 scale-100 opacity-100"
        x-transition:leave-end="translate-y-[120%] opacity-0"
        class="relative w-[calc(100%-2rem)] mx-auto mb-24 flex flex-col"
        style="padding-bottom: env(safe-area-inset-bottom);">

        <x-liquid-glass tint="rgba(255, 255, 255, 0.7)" darkTint="rgba(28, 28, 30, 0.7)"
            class="w-full rounded-[1.5rem] shadow-2xl flex flex-col overflow-hidden border border-white/40 dark:border-white/10">

            <!-- Items Vertical List -->
            <div class="flex flex-col w-full max-h-[80vh] overflow-y-auto overscroll-contain">

                <!-- Stok & Jadwal -->
                @canany(['stock_mutations:read', 'stock_mutations:read-self'])
                    <a href="{{ route('stock-mutations.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                        <flux:icon.clipboard-document-list class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Stok Gudang</span>
                    </a>
                @endcanany

                @can('sales_schedules:read')
                    <a href="{{ route('sales-schedules.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                        <flux:icon.calendar-days class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Jadwal Mingguan</span>
                    </a>
                @endcan

                <!-- Hak Akses -->
                @can('users:read')
                    <a href="{{ route('users.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                        <flux:icon.users class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Pengguna</span>
                    </a>
                @endcan

                @can('roles:read')
                    <a href="{{ route('roles.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                        <flux:icon.key class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Peran</span>
                    </a>
                @endcan

                @if (env('ONESIGNAL_APP_ID'))
                    @can('notifications:send')
                        <a href="{{ route('notifications.index') }}" wire:navigate
                            class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                            <flux:icon.paper-airplane class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                            <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Kirim Notifikasi</span>
                        </a>
                    @endcan
                @endif

                <!-- Sistem / Developer -->
                @can('backups:read')
                    <a href="{{ route('backups.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                        <flux:icon.archive-box class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Backup & Tutup Buku</span>
                    </a>
                @endcan

                @can('logs.view')
                    <a href="{{ route('logs.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                        <flux:icon.document-text class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Logs Viewer</span>
                    </a>
                @endcan

                @can('system_monitor.view')
                    <a href="{{ route('system-monitor.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b-[3px] border-black/10 dark:border-white/10">
                        <flux:icon.cpu-chip class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">System Monitor</span>
                    </a>
                @endcan

                <!-- Profil & Logout -->
                <a href="{{ route('profile.edit') }}" wire:navigate
                    class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                    <flux:icon.cog class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                    <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Pengaturan</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors text-red-600 dark:text-red-400">
                        <flux:icon.arrow-right-start-on-rectangle class="w-5 h-5 mr-4" />
                        <span class="text-[17px] font-normal flex-1 text-left">Keluar</span>
                    </button>
                </form>

            </div>
        </x-liquid-glass>
    </div>
</div>
