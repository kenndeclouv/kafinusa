<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 overflow-x-hidden">
    <flux:sidebar sticky collapsible class="bg-zinc-50 dark:bg-zinc-900 max-lg:hidden">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate.hover />
            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <div @click="$dispatch('open-command-menu')" class="w-full">
                <flux:sidebar.search placeholder="⌘k" class="mb-2 !py-0 pointer" />
            </div>

            <div class="px-3 mb-1 text-xs font-medium text-zinc-500 dark:text-zinc-400 in-data-flux-sidebar-collapsed-desktop:hidden"
                bis_skin_checked="1">
                Overview
            </div>

            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate.hover>
                {{ __('Beranda') }}
            </flux:sidebar.item>

            {{-- <div class="border-t-1 border-zinc-200 dark:border-zinc-700 my-1"></div> --}}

            @canany(['order_books:read', 'order_books:read-self'])
                <flux:sidebar.item icon="book-open" :href="route('order-books.index')"
                    :current="request()->routeIs('order-books.*')" wire:navigate.hover>
                    {{ __('Buku Order') }}
                </flux:sidebar.item>
                @can('sales_schedules:read')
                <flux:sidebar.item icon="calendar-days" :href="route('sales-schedules.index')"
                    :current="request()->routeIs('sales-schedules.*')" wire:navigate.hover>
                    {{ __('Jadwal Mingguan') }}
                </flux:sidebar.item>
                @endcan
            @endcanany

            <div class="px-3 mt-2 mb-1 text-xs font-medium text-zinc-500 dark:text-zinc-400 in-data-flux-sidebar-collapsed-desktop:hidden"
                bis_skin_checked="1">
                Advanced
            </div>

            @canany(['markets:read', 'customers:read', 'employees:read', 'items:read', 'customer-categories:read',
                'item-categories:read'])
                <flux:sidebar.group expandable
                    :expanded="request()->routeIs('markets.*', 'customers.*', 'employees.*', 'items.*', 'customer-categories.*', 'item-categories.*')"
                    icon="circle-stack" :heading="__('Database')">
                    @can('markets:read')
                        <flux:sidebar.item icon="building-storefront" :href="route('markets.index')"
                            :current="request()->routeIs('markets.*')" wire:navigate.hover>
                            {{ __('Pasar') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('customers:read')
                        <flux:sidebar.item icon="user-group" :href="route('customers.index')"
                            :current="request()->routeIs('customers.*')" wire:navigate.hover>
                            {{ __('Pelanggan') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('employees:read')
                        <flux:sidebar.item icon="identification" :href="route('employees.index')"
                            :current="request()->routeIs('employees.*')" wire:navigate.hover>
                            {{ __('Pegawai') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('items:read')
                        <flux:sidebar.item icon="cube" :href="route('items.index')"
                            :current="request()->routeIs('items.*')" wire:navigate.hover>
                            {{ __('Barang') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('customer_categories:read')
                        <flux:sidebar.item icon="users" :href="route('customer-categories.index')"
                            :current="request()->routeIs('customer-categories.*')" wire:navigate.hover>
                            {{ __('Kategori Pelanggan') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('item_categories:read')
                        <flux:sidebar.item icon="tag" :href="route('item-categories.index')"
                            :current="request()->routeIs('item-categories.*')" wire:navigate.hover>
                            {{ __('Kategori Barang') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
            @endcanany

            @canany(['users:read', 'roles:read', 'permissions:read', 'notifications:send'])
                <flux:sidebar.group expandable :expanded="request()->routeIs('users.*', 'roles.*', 'permissions.*', 'notifications.*')"
                    icon="shield-check" :heading="__('Hak Akses')">
                    @can('users:read')
                        <flux:sidebar.item icon="users" :href="route('users.index')"
                            :current="request()->routeIs('users.index')" wire:navigate>
                            {{ __('Pengguna') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('roles:read')
                        <flux:sidebar.item icon="key" :href="route('roles.index')"
                            :current="request()->routeIs('roles.index')" wire:navigate>
                            {{ __('Peran') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('notifications:send')
                        <flux:sidebar.item icon="paper-airplane" :href="route('notifications.index')"
                            :current="request()->routeIs('notifications.index')" wire:navigate>
                            {{ __('Kirim Notifikasi') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>
            @endcanany
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile :avatar="auth()->user()->avatarUrl()" :initials="auth()->user()->initials()"
                :name="auth()->user()->name" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :src="auth()->user()->avatarUrl()" :name="auth()->user()->name"
                                :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                    {{ __('Pengaturan') }}
                </flux:menu.item>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" variant="danger" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer">
                        {{ __('Keluar') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile Top Header (Search & Profile) -->
    {{-- <div class="sticky top-0 z-40 lg:hidden px-4 pt-3 pb-2 flex items-center gap-3" style="padding-top: calc(0.75rem + env(safe-area-inset-top));">
        
        <!-- Search Bar (Liquid Glass) -->
        <x-liquid-glass blur="16" depth="15" tint="rgba(255, 255, 255, 0.7)" darkTint="rgba(24, 24, 27, 0.7)" class="flex-1 rounded-full shadow-sm dark:shadow-[0_4px_20px_rgb(0,0,0,0.5)] border border-zinc-200/50 dark:border-white/10">
            <button x-data x-on:click="$dispatch('open-command-menu')" class="w-full flex items-center gap-2.5 px-4 py-2.5 active:scale-[0.98] transition-all">
                <flux:icon.magnifying-glass class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                <span class="text-sm text-zinc-500 dark:text-zinc-400 font-medium">Cari pesanan, pelanggan...</span>
            </button>
        </x-liquid-glass>
        
        <!-- User Avatar / Notification (Liquid Glass) -->
        <x-liquid-glass blur="16" depth="15" tint="rgba(255, 255, 255, 0.7)" darkTint="rgba(24, 24, 27, 0.7)" class="shrink-0 rounded-full shadow-sm border border-zinc-200/50 dark:border-white/10 p-1">
            <a href="{{ route('profile.edit') }}" wire:navigate class="relative block active:scale-90 transition-transform">
                <flux:avatar :src="auth()->user()->avatarUrl()" :name="auth()->user()->name" :initials="auth()->user()->initials()" class="!w-9 !h-9 border border-zinc-200/50 dark:border-zinc-700/50" />
                <!-- Notification Dot -->
                <span class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 border border-white dark:border-zinc-800 rounded-full"></span>
            </a>
        </x-liquid-glass>
        
    </div> --}}

    {{ $slot }}

    <x-mobile-bottom-nav />
    <x-mobile-bottom-sheet />

    @persist('toast')
        <flux:toast.group position="top center" class="pointer-events-none">
            <flux:toast class="pointer-events-auto" />
        </flux:toast.group>
    @endpersist

    @persist('calculator')
        <livewire:calculator />
    @endpersist

    <x-context-menu />

    <livewire:command-menu />

    @fluxScripts
</body>

</html>
