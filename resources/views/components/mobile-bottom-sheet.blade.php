<div x-data="{ open: false }" @open-mobile-master-data.window="open = true" x-show="open"
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
        class="relative w-[calc(100%-2rem)] mx-auto mb-6 flex flex-col"
        style="padding-bottom: env(safe-area-inset-bottom);">

        <x-liquid-glass tint="rgba(255, 255, 255, 0.7)" darkTint="rgba(28, 28, 30, 0.7)"
            class="w-full rounded-[1.5rem] shadow-2xl flex flex-col overflow-hidden border border-white/40 dark:border-white/10">

            <!-- Items Vertical List -->
            <div class="flex flex-col w-full">

                <!-- GROUP: DATABASE -->
                @can('markets:read')
                    <a href="{{ route('markets.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                        <flux:icon.building-storefront class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Pasar</span>
                    </a>
                @endcan

                @can('customers:read')
                    <a href="{{ route('customers.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                        <flux:icon.user-group class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Pelanggan</span>
                    </a>
                @endcan

                @can('employees:read')
                    <a href="{{ route('employees.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                        <flux:icon.identification class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Pegawai</span>
                    </a>
                @endcan

                @can('sales_schedules:read')
                    <a href="{{ route('sales-schedules.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                        <flux:icon.calendar-days class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Jadwal Kunjungan</span>
                    </a>
                @endcan

                @can('items:read')
                    <a href="{{ route('items.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                        <flux:icon.cube class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Barang</span>
                    </a>
                @endcan

                @can('customer_categories:read')
                    <a href="{{ route('customer-categories.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b border-black/5 dark:border-white/5">
                        <flux:icon.users class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Kategori Pelanggan</span>
                    </a>
                @endcan

                @can('item_categories:read')
                    <a href="{{ route('item-categories.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors border-b-[3px] border-black/10 dark:border-white/10">
                        <flux:icon.tag class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Kategori Barang</span>
                    </a>
                @endcan

                <!-- GROUP: HAK AKSES -->
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

                @can('notifications:send')
                    <a href="{{ route('notifications.index') }}" wire:navigate
                        class="flex items-center px-5 py-3.5 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                        <flux:icon.paper-airplane class="w-5 h-5 text-zinc-700 dark:text-zinc-300 mr-4" />
                        <span class="text-[17px] font-normal text-zinc-900 dark:text-white flex-1">Kirim Notifikasi</span>
                    </a>
                @endcan
            </div>
        </x-liquid-glass>

        <!-- Cancel Button (iOS Style floating separated button) -->
        <div class="mt-2">
            <x-liquid-glass tint="rgba(255, 255, 255, 0.7)" darkTint="rgba(28, 28, 30, 0.7)"
                class="w-full rounded-[1.5rem] shadow-lg border border-white/40 dark:border-white/10 overflow-hidden">
                <button @click="open = false"
                    class="w-full py-3.5 text-[17px] font-semibold text-blue-500 dark:text-blue-400 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
            </x-liquid-glass>
        </div>
    </div>
</div>
