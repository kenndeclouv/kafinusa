<div class="fixed bottom-0 left-0 right-0 z-[100] lg:hidden px-4 pb-4 pointer-events-none"
    style="padding-bottom: calc(1rem + env(safe-area-inset-bottom));">

    <x-liquid-glass tint="rgba(255, 255, 255, 0.6)" darkTint="rgba(24, 24, 27, 0.6)"
        class="mx-auto w-fit pointer-events-auto rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.12)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.5)]">
        <div class="flex items-center gap-2 px-1 py-1 relative">

            <!-- Home -->
            <a href="{{ route('dashboard') }}" wire:navigate.hover
                class="flex flex-col items-center justify-center px-4 h-12 gap-1 rounded-full transition-all duration-300 {{ request()->routeIs('dashboard') ? 'bg-accent/10 dark:bg-accent/20 text-accent' : 'text-zinc-500 dark:text-zinc-400' }}">
                <flux:icon.home variant="{{ request()->routeIs('dashboard') ? 'solid' : 'outline' }}" class="w-6 h-6" />
                <span class="text-[10px] font-medium leading-none">Beranda</span>
            </a>

            <!-- Order Book -->
            @canany(['order_books:read', 'order_books:read-self'])
                <a href="{{ route('order-books.index') }}" wire:navigate.hover
                    class="flex flex-col items-center justify-center px-4 h-12 gap-1 rounded-full transition-all duration-300 {{ request()->routeIs('order-books.*') ? 'bg-accent/10 dark:bg-accent/20 text-accent' : 'text-zinc-500 dark:text-zinc-400' }}">
                    <flux:icon.book-open variant="{{ request()->routeIs('order-books.*') ? 'solid' : 'outline' }}"
                        class="w-6 h-6" />
                    <span class="text-[10px] font-medium leading-none">Pesanan</span>
                </a>
            @endcanany

            <!-- Master Data / Database -->
            @canany(['markets:read', 'customers:read', 'employees:read', 'items:read', 'customer-categories:read',
                'item-categories:read', 'warehouses:read'])
                <button x-data x-on:click="$dispatch('toggle-mobile-master-data'); $dispatch('close-mobile-more')"
                    class="flex flex-col items-center justify-center px-4 h-12 gap-1 rounded-full transition-all duration-300 {{ request()->routeIs('markets.*', 'customers.*', 'employees.*', 'items.*', 'customer-categories.*', 'item-categories.*', 'warehouses.*') ? 'bg-accent/10 dark:bg-accent/20 text-accent' : 'text-zinc-500 dark:text-zinc-400' }}">
                    <flux:icon.circle-stack
                        variant="{{ request()->routeIs('markets.*', 'customers.*', 'employees.*', 'items.*', 'customer-categories.*', 'item-categories.*', 'warehouses.*') ? 'solid' : 'outline' }}"
                        class="w-6 h-6" />
                    <span class="text-[10px] font-medium leading-none">Database</span>
                </button>
            @endcanany

            <!-- Lainnya (More) -->
            <button x-data x-on:click="$dispatch('toggle-mobile-more'); $dispatch('close-mobile-master-data')"
                class="flex flex-col items-center justify-center px-4 h-12 gap-1 rounded-full transition-all duration-300 {{ request()->routeIs('stock-mutations.*', 'sales-schedules.*', 'users.*', 'roles.*', 'permissions.*', 'notifications.*', 'backups.*', 'logs.*', 'system-monitor.*', 'profile.edit') ? 'bg-accent/10 dark:bg-accent/20 text-accent' : 'text-zinc-500 dark:text-zinc-400' }}">
                <flux:icon.ellipsis-horizontal variant="{{ request()->routeIs('stock-mutations.*', 'sales-schedules.*', 'users.*', 'roles.*', 'permissions.*', 'notifications.*', 'backups.*', 'logs.*', 'system-monitor.*', 'profile.edit') ? 'solid' : 'outline' }}"
                    class="w-6 h-6" />
                <span class="text-[10px] font-medium leading-none">Lainnya</span>
            </button>
        </div>
    </x-liquid-glass>
</div>
