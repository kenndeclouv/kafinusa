<div>
    <!-- Custom Push Notification Prompt -->
    <div x-data="{ open: false }" x-on:show-push-modal.window="open = true" x-init="if (window.Notification && Notification.permission !== 'denied') {
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(async function(OneSignal) {
            setTimeout(() => {
                if (OneSignal.User && OneSignal.User.PushSubscription) {
                    if (!OneSignal.User.PushSubscription.optedIn && Notification.permission !== 'denied') {
                        window.dispatchEvent(new CustomEvent('show-push-modal'));
                    }
                }
            }, 2000);
        });
    }">
        <flux:modal :closable="false" scroll="body" name="push-subscribe-modal" x-model="open"
            class="md:w-96 !rounded-3xl">
            <div class="space-y-6">
                <div>
                    <div class="w-12 h-12 bg-accent/10 text-accent rounded-xl flex items-center justify-center mb-4">
                        <flux:icon.bell class="w-6 h-6" />
                    </div>
                    <flux:heading size="lg">Nyalakan Notifikasi?</flux:heading>
                    <flux:subheading class="mt-1">
                        Dapatkan pemberitahuan terbaru terkait tugas, pesanan, dan pembaruan penting lainnya secara
                        instan.
                    </flux:subheading>
                </div>

                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2">
                    <flux:button class="!rounded-full" variant="filled" x-on:click="open = false">Nanti Saja
                    </flux:button>
                    <flux:button class="!rounded-full" variant="primary"
                        x-on:click="
                        open = false;
                        OneSignalDeferred.push(async function(OneSignal) {
                            await OneSignal.Notifications.requestPermission();
                        });
                    ">
                        Aktifkan Sekarang</flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
    <!-- ==========================================
         DESKTOP VIEW
         ========================================== -->
    <div class="hidden md:block">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 md:mb-8">
            <div>
                <flux:heading size="xl" class="">Halo, {{ auth()->user()->name }}!</flux:heading>
                <flux:subheading>Berikut adalah ringkasan pekerjaan Anda hari ini.</flux:subheading>
            </div>
        </div>

        @canany(['markets:read', 'customers:read', 'orders:read', 'order_books:read'])
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @can('markets:read')
                    <!-- Stat 1 -->
                    <div class="bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 p-3 rounded-xl">
                                <flux:icon.building-storefront class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Pasar</p>
                                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['total_markets'] }}
                                </h3>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('customers:read')
                    <!-- Stat 2 -->
                    <div class="bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 p-3 rounded-xl">
                                <flux:icon.users class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pelanggan Aktif</p>
                                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">
                                    {{ $this->stats['total_customers'] }}</h3>
                            </div>
                        </div>
                    </div>
                @endcan

                @canany(['orders:read', 'order_books:read'])
                    <!-- Stat 3 -->
                    <div class="bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 p-3 rounded-xl">
                                <flux:icon.shopping-bag class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pesanan Hari Ini</p>
                                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->stats['orders_today'] }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <!-- Stat 4 -->
                    <div class="bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div
                                class="bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 p-3 rounded-xl">
                                <flux:icon.scale class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Tonase Hari Ini</p>
                                <h3 class="text-2xl font-bold text-zinc-900 dark:text-white">
                                    {{ formatWeight($this->stats['weight_today']) }}</h3>
                            </div>
                        </div>
                    </div>
                @endcanany
            </div>
        @endcanany

        @can('order_books:read')
            <div class="mt-8 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <flux:heading size="lg">Strategi & Kinerja Bisnis</flux:heading>
                    <p class="text-sm text-zinc-500">Pantau performa distribusi dan penjualan Anda.</p>
                </div>
                <div class="w-48">
                    <x-searchable-select wire:model.live="timeRange" size="sm" :searchable="false" :options="[
                        '1' => 'Hari Ini',
                        '7' => '7 Hari Terakhir',
                        '14' => '14 Hari Terakhir',
                        '30' => '30 Hari Terakhir',
                        'all' => 'Semua Waktu'
                    ]" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Trend Line Chart -->
                <div class="bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                    <div class="mb-6">
                        <flux:heading size="md">Tren Transaksi (Tonase vs Pesanan)</flux:heading>
                    </div>
                    <x-chart :series="$this->trendData" type="area" height="260" />
                </div>

                <!-- Top 5 Markets -->
                <div class="bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                    <div class="mb-6">
                        <flux:heading size="md">Top 5 Pasar Teraktif (Jml. Pesanan)</flux:heading>
                    </div>
                    <x-chart :data="$this->topMarketsData" type="bar" color="#f59e0b" label="Pesanan" height="260" />
                </div>

                <!-- Top 5 Sales -->
                <div class="bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                    <div class="mb-6">
                        <flux:heading size="md">Top 5 Sales Teratas (Jml. Pesanan)</flux:heading>
                    </div>
                    <x-chart :data="$this->topSalesData" type="bar" color="#8b5cf6" label="Pesanan" height="260" />
                </div>

                <!-- Top 5 Items -->
                <div class="bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-2xl p-6 shadow-sm">
                    <div class="mb-6">
                        <flux:heading size="md">Top 5 Barang Terlaris (Qty)</flux:heading>
                    </div>
                    <x-chart :data="$this->topItemsData" type="bar" color="#ec4899" label="Qty" height="260" />
                </div>
            </div>
        @endcan

        <div class="mt-8">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-4">
                <div>
                    <flux:heading size="lg">Tugas & Jadwal Anda</flux:heading>
                    <p class="text-sm text-zinc-500 mt-1">Order book yang ditugaskan kepada Anda untuk hari ini dan
                        besok.</p>
                </div>
                @canany(['order_books:read', 'order_books:read-self'])
                    <a class="mt-4 md:mt-0 text-sm font-semibold text-accent hover:text-accent/80 transition-colors"
                        href="{{ route('order-books.index') }}" wire:navigate>Lihat Semua Riwayat</a>
                @endcanany
            </div>

            <div
                class="rounded-2xl bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <flux:table
                        class="[&_th:first-child]:!ps-6 [&_td:first-child]:!ps-6 [&_th:last-child]:!pe-6 [&_td:last-child]:!pe-6">
                        <flux:table.columns>
                            <flux:table.column>Pasar Tujuan</flux:table.column>
                            <flux:table.column>Jadwal</flux:table.column>
                            <flux:table.column>Jumlah Pesanan</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Aksi</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @forelse ($this->myTasks as $book)
                                <flux:table.row :key="$book->id">
                                    <flux:table.cell>
                                        <div class="flex items-center gap-3">
                                            <div class="bg-zinc-100 dark:bg-zinc-800 p-2 rounded-lg">
                                                <flux:icon.building-storefront class="w-5 h-5 text-zinc-500" />
                                            </div>
                                            <div>
                                                <span
                                                    class="font-medium text-zinc-900 dark:text-white">{{ $book->market->name }}</span>
                                                <div class="text-xs text-zinc-500">{{ $book->market->code }}</div>
                                            </div>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span
                                            class="font-medium {{ $book->book_date == now()->format('Y-m-d') ? 'text-rose-600 dark:text-rose-400' : 'text-zinc-900 dark:text-white' }}">
                                            {{ $book->book_date == now()->format('Y-m-d') ? 'Hari Ini' : 'Besok' }}
                                        </span>
                                        <div class="text-xs text-zinc-500">
                                            {{ \Carbon\Carbon::parse($book->book_date)->format('d M Y') }}</div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge color="sky" size="sm">{{ $book->orders_count }} Pesanan
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if ($book->status === 'draft')
                                            <flux:badge color="zinc" size="sm">Draft</flux:badge>
                                        @elseif($book->status === 'locked_for_delivery')
                                            <flux:badge color="amber" size="sm">Dikunci & Kirim</flux:badge>
                                        @else
                                            <flux:badge color="emerald" size="sm">Selesai</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:button href="{{ route('order-books.show', $book->id) }}" wire:navigate
                                            size="sm" variant="primary" icon="arrow-right">Buka & Kelola
                                        </flux:button>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12">
                                        <div class="flex flex-col items-center justify-center text-center">
                                            <div class="rounded-full bg-zinc-100 dark:bg-zinc-800 p-3 mb-4">
                                                <flux:icon.check-circle class="w-6 h-6 text-emerald-500" />
                                            </div>
                                            <flux:heading size="md">Tidak Ada Tugas</flux:heading>
                                            <flux:text class="mt-1 text-sm text-zinc-500">Belum ada buku order yang
                                                ditugaskan kepada Anda untuk hari ini dan besok.</flux:text>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         MOBILE VIEW
         ========================================== -->
    <div class="md:hidden block mt-2 pb-12 relative">

        <!-- Background Circle Decoration -->
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-[50%] w-[200vw] h-[200vw] bg-accent rounded-full -z-10 shadow-sm">
        </div>

        <!-- Header: Profile & Bell -->
        {{-- <div class="flex items-center justify-between mb-6 pt-2">
            <div class="w-12 h-12 rounded-full overflow-hidden bg-accent/20 ring-2 ring-accent dark:ring-accent/80 shadow-sm flex items-center justify-center">
                @if (auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="Profile" class="w-full h-full object-cover">
                @else
                    <span class="text-accent-content font-bold text-lg">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                @endif
            </div>
            <div class="w-12 h-12 rounded-full bg-white/20 dark:bg-black/20 backdrop-blur-sm shadow-sm border border-white/20 flex items-center justify-center relative cursor-pointer active:scale-95 transition-transform">
                <flux:icon.bell class="w-6 h-6 text-white" />
                <span class="absolute top-3 right-3.5 w-2 h-2 bg-red-500 rounded-full border border-white dark:border-accent"></span>
            </div>
        </div> --}}

        <!-- Greeting -->
        <div class="mb-6 mt-8 text-center">
            <h1 class="text-[32px] font-medium text-white tracking-tight leading-[1.1]">
                {{ match (true) {
                    now()->format('H') < 11 => 'Selamat Pagi,',
                    now()->format('H') < 15 => 'Selamat Siang,',
                    now()->format('H') < 18 => 'Selamat Sore,',
                    default => 'Selamat Malam,',
                } }}<br>
                <span class="text-white/80">{{ auth()->user()->name }}</span>
            </h1>
        </div>

        <!-- Search Bar -->
        {{-- <div class="relative mb-6">
            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                <flux:icon.magnifying-glass class="w-5 h-5 text-zinc-400 dark:text-zinc-500" />
            </div>
            <input type="text" class="block w-full pl-12 pr-14 py-4 bg-white dark:bg-zinc-900 border-0 rounded-[32px] shadow-lg shadow-black/10 text-zinc-900 dark:text-white placeholder-zinc-400 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-accent transition-all font-medium text-[15px]" placeholder="Search">
            <div class="absolute inset-y-0 right-3 flex items-center">
                <div class="p-2 bg-transparent text-zinc-500 dark:text-zinc-400">
                    <flux:icon.adjustments-horizontal class="w-5 h-5" />
                </div>
            </div>
        </div> --}}
        <!-- Liquid Pill Banner (Task Count) -->
        @if ($this->myTasks->count() > 0)
            <a href="{{ route('order-books.index') }}" wire:navigate
                class="block mt-8 mb-8 rounded-[32px] bg-accent border border-white/20 p-2 flex items-center justify-between shadow-lg shadow-accent/20 active:scale-[0.98] transition-transform relative overflow-hidden group">
                <div
                    class="absolute top-0 right-0 w-32 h-32 bg-white/20 rounded-full blur-2xl transform translate-x-10 -translate-y-10 group-hover:bg-white/30 transition-all">
                </div>
                <div class="flex items-center gap-3 pl-2 relative z-10">
                    <div
                        class="w-11 h-11 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-sm">
                        <flux:icon.document-text class="w-5 h-5 text-white" />
                    </div>
                    <span class="text-[15px] font-semibold text-white">Anda punya {{ $this->myTasks->count() }} tugas
                        hari ini</span>
                </div>
                <div class="w-10 h-10 mr-1 rounded-full flex items-center justify-center text-white relative z-10">
                    <flux:icon.arrow-right class="w-5 h-5" />
                </div>
            </a>
        @endif


        <!-- Tasks List Section -->
        @if ($this->myTasks->count() > 0)
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-[17px] font-semibold text-zinc-900 dark:text-white tracking-tight">Detail Tugas Anda
                </h3>
            </div>

            <div class="flex flex-col gap-3">
                @forelse ($this->myTasks as $book)
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-[24px] p-4 border border-zinc-200 dark:border-zinc-800 shadow-sm relative overflow-hidden flex flex-col gap-3">

                        <!-- Header -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 bg-zinc-100 dark:bg-zinc-800 rounded-2xl flex items-center justify-center text-zinc-600 dark:text-zinc-300">
                                    <flux:icon.building-storefront class="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-[16px] text-zinc-900 dark:text-white leading-none mb-1">
                                        {{ $book->market->name }}</h4>
                                    <p class="text-[11px] font-semibold text-zinc-400 uppercase tracking-wider">
                                        {{ $book->market->code }}</p>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div>
                                @if ($book->status === 'draft')
                                    <span
                                        class="px-3 py-1 rounded-full text-[10px] font-bold bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 uppercase tracking-wider">Draft</span>
                                @elseif($book->status === 'locked_for_delivery')
                                    <span
                                        class="px-3 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 uppercase tracking-wider">Dikirim</span>
                                @else
                                    <span
                                        class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 uppercase tracking-wider">Selesai</span>
                                @endif
                            </div>
                        </div>

                        <!-- Details Row -->
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl p-3 flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-semibold text-zinc-500 uppercase tracking-wider mb-0.5">
                                    Pesanan
                                </p>
                                <p class="text-sm font-bold text-zinc-900 dark:text-white">{{ $book->orders_count }}
                                    Item
                                </p>
                            </div>
                            <div class="w-px h-8 bg-zinc-200 dark:bg-zinc-700"></div>
                            <div class="text-right">
                                <p class="text-[10px] font-semibold text-zinc-500 uppercase tracking-wider mb-0.5">
                                    Jadwal
                                </p>
                                <p
                                    class="text-sm font-bold {{ $book->book_date == now()->format('Y-m-d') ? 'text-accent' : 'text-zinc-900 dark:text-white' }}">
                                    {{ $book->book_date == now()->format('Y-m-d') ? 'Hari Ini' : 'Besok' }}</p>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <a href="{{ route('order-books.show', $book->id) }}" wire:navigate
                            class="w-full py-3.5 bg-accent hover:brightness-110 text-white rounded-full font-semibold text-[15px] flex items-center justify-center gap-2 transition-colors active:scale-[0.98] shadow-sm">
                            Buka Tugas
                        </a>
                    </div>
                @empty
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-[32px] p-8 border border-zinc-200 dark:border-zinc-800 flex flex-col items-center justify-center text-center mt-4">
                        <div
                            class="w-16 h-16 bg-accent/10 rounded-full flex items-center justify-center text-accent mb-4">
                            <flux:icon.check-circle class="w-8 h-8" />
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-1">Semua Beres!</h3>
                        <p class="text-sm text-zinc-500">Tidak ada tugas hari ini.</p>
                    </div>
                @endforelse
            </div>
        @endif

        @canany(['markets:read', 'customers:read', 'orders:read', 'order_books:read'])
            <!-- Stats Section -->
            <div class="mb-4 mt-4 flex items-center justify-between">
                <h3 class="text-[17px] font-semibold text-zinc-900 dark:text-white tracking-tight">Statistik</h3>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-8">
                @can('markets:read')
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-3xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col justify-between aspect-square">
                        <div class="w-10 h-10 bg-accent/10 text-accent rounded-2xl flex items-center justify-center mb-2">
                            <flux:icon.building-storefront class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-[28px] font-bold text-zinc-900 dark:text-white tracking-tight leading-none mb-1">
                                {{ $this->stats['total_markets'] }}</h3>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total Pasar</p>
                        </div>
                    </div>
                @endcan

                @can('customers:read')
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-3xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col justify-between aspect-square">
                        <div
                            class="w-10 h-10 bg-[#dcfce7] dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center mb-2">
                            <flux:icon.users class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-[28px] font-bold text-zinc-900 dark:text-white tracking-tight leading-none mb-1">
                                {{ $this->stats['total_customers'] }}</h3>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Pelanggan Aktif</p>
                        </div>
                    </div>
                @endcan

                @canany(['orders:read', 'order_books:read'])
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-3xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col justify-between aspect-square">
                        <div
                            class="w-10 h-10 bg-[#ffedd5] dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 rounded-2xl flex items-center justify-center mb-2">
                            <flux:icon.shopping-bag class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-[28px] font-bold text-zinc-900 dark:text-white tracking-tight leading-none mb-1">
                                {{ $this->stats['orders_today'] }}</h3>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Pesanan Hari Ini</p>
                        </div>
                    </div>

                    <div
                        class="bg-accent rounded-3xl p-5 border border-white/20 shadow-lg shadow-accent/20 flex flex-col justify-between aspect-square text-white">
                        <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center mb-2 backdrop-blur-sm">
                            <flux:icon.scale class="w-5 h-5 text-white" />
                        </div>
                        <div>
                            <h3 class="text-[26px] font-bold text-white tracking-tight leading-none mb-1">
                                {{ formatWeight($this->stats['weight_today']) }}</h3>
                            <p class="text-xs font-medium text-white/80">Tonase Hari Ini</p>
                        </div>
                    </div>
                @endcanany
            </div>

            @can('order_books:read')
                <div class="mb-4 mt-8 flex flex-col items-start gap-3">
                    <div>
                        <h3 class="text-[17px] font-semibold text-zinc-900 dark:text-white tracking-tight">Strategi Bisnis</h3>
                    </div>
                    <div class="w-full">
                        <x-searchable-select wire:model.live="timeRange" :searchable="false" :options="[
                            '1' => 'Hari Ini',
                            '7' => '7 Hari Terakhir',
                            '14' => '14 Hari Terakhir',
                            '30' => '30 Hari Terakhir',
                            'all' => 'Semua Waktu'
                        ]" />
                    </div>
                </div>

                <div class="flex flex-col gap-4 mb-8">
                    <!-- Trend -->
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-3xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-sm">
                        <div class="mb-4">
                            <h3 class="text-[15px] font-bold text-zinc-900 dark:text-white">Tren Transaksi</h3>
                        </div>
                        <x-chart :series="$this->trendData" type="area" height="220" />
                    </div>

                    <!-- Top 5 Markets -->
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-3xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-sm">
                        <div class="mb-4">
                            <h3 class="text-[15px] font-bold text-zinc-900 dark:text-white">Top 5 Pasar Teraktif</h3>
                        </div>
                        <x-chart :data="$this->topMarketsData" type="bar" color="#f59e0b" label="Pesanan" height="220" />
                    </div>

                    <!-- Top 5 Sales -->
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-3xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-sm">
                        <div class="mb-4">
                            <h3 class="text-[15px] font-bold text-zinc-900 dark:text-white">Top 5 Sales Teratas</h3>
                        </div>
                        <x-chart :data="$this->topSalesData" type="bar" color="#8b5cf6" label="Pesanan" height="220" />
                    </div>

                    <!-- Top 5 Items -->
                    <div
                        class="bg-white dark:bg-zinc-900 rounded-3xl p-5 border border-zinc-200 dark:border-zinc-800 shadow-sm">
                        <div class="mb-4">
                            <h3 class="text-[15px] font-bold text-zinc-900 dark:text-white">Top 5 Barang Terlaris</h3>
                        </div>
                        <x-chart :data="$this->topItemsData" type="bar" color="#ec4899" label="Qty" height="220" />
                    </div>
                </div>
            @endcan
        @endcanany
    </div>
</div>
