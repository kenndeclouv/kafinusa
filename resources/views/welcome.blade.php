<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ERD Order Management - Welcome</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @fluxAppearance

    <style>
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        .perspective-1000 {
            perspective: 1000px;
        }
    </style>
</head>
<body class="bg-zinc-50 dark:bg-[#0a0a0a] text-zinc-900 dark:text-white font-sans antialiased min-h-screen flex flex-col relative selection:bg-sky-500/30">

    <!-- Background effects -->
    <div class="fixed inset-0 -z-10 h-full w-full bg-zinc-50 dark:bg-[#0a0a0a] bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(120,119,198,0.2),rgba(255,255,255,0))] dark:bg-[radial-gradient(ellipse_80%_80%_at_50%_-20%,rgba(14,165,233,0.15),rgba(255,255,255,0))]"></div>
    <div class="fixed top-[-10rem] -z-10 left-[calc(50%-30rem)] w-[60rem] h-[60rem] rounded-full bg-sky-500/10 blur-[120px] pointer-events-none"></div>

    <!-- Navigation -->
    <nav class="w-full flex items-center justify-between px-6 py-4 max-w-7xl mx-auto backdrop-blur-md sticky top-0 z-50 border-b border-transparent dark:border-white/5 transition-all">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-sky-500 flex items-center justify-center text-white font-bold shadow-lg shadow-sky-500/30">
                <flux:icon.book-open variant="micro" class="w-5 h-5 text-white" />
            </div>
            <span class="font-bold text-xl tracking-tight text-zinc-900 dark:text-white">Order<span class="text-sky-500">Master</span></span>
        </div>
        
        <div class="flex items-center gap-4">
            @if (Route::has('login'))
                @auth
                    <flux:button href="{{ route('dashboard') }}" wire:navigate variant="primary" class="rounded-full px-6">Buka Dashboard</flux:button>
                @else
                    <flux:button href="{{ route('login') }}" wire:navigate variant="subtle" class="rounded-full">Log in</flux:button>
                @endauth
            @endif
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="flex-1 flex flex-col items-center justify-center px-6 pt-24 pb-32 text-center max-w-5xl mx-auto relative z-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/10 text-sky-600 dark:text-sky-400 text-sm font-medium mb-8 border border-sky-500/20 backdrop-blur-sm animate-pulse-slow">
            <span class="flex h-2 w-2 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-500"></span>
            </span>
            <span>Versi 1.0 Telah Rilis!</span>
        </div>

        <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-zinc-900 dark:text-white mb-6 leading-tight">
            Sistem Kelola Pesanan <br class="hidden md:block">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-blue-600">Lebih Cepat & Akurat</span>
        </h1>
        
        <p class="text-lg md:text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto mb-10 leading-relaxed">
            Platform modern untuk mencatat daftar barang, mengelola pelanggan, dan memantau pergerakan tonase di setiap pasar dengan antarmuka yang memanjakan mata.
        </p>
        
        <div class="flex flex-col sm:flex-row items-center gap-4">
            @auth
                <flux:button href="{{ route('dashboard') }}" wire:navigate variant="primary"  icon-trailing="arrow-right" class="rounded-full shadow-lg shadow-sky-500/25 px-8 w-full sm:w-auto">
                    Buka Dashboard
                </flux:button>
            @else
                <flux:button href="{{ route('login') }}" wire:navigate variant="primary"  icon-trailing="arrow-right" class="rounded-full shadow-lg shadow-sky-500/25 px-8 w-full sm:w-auto">
                    Mulai Sekarang
                </flux:button>
            @endauth
            <flux:button href="#" variant="subtle"  class="rounded-full px-8 w-full sm:w-auto">
                Pelajari Lebih Lanjut
            </flux:button>
        </div>

        <!-- Dashboard Preview Mockup -->
        <div class="mt-20 relative w-full perspective-1000 hidden md:block">
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-50 via-transparent dark:from-[#0a0a0a] z-20 pointer-events-none h-[120%] -bottom-20"></div>
            <div class="rounded-2xl border border-zinc-200 dark:border-white/10 bg-white/50 dark:bg-zinc-900/50 backdrop-blur-md p-2 shadow-2xl overflow-hidden transition-all duration-700 ease-out cursor-pointer" 
                 style="transform: rotateX(10deg) translateY(2rem) scale(0.95);" 
                 onmouseover="this.style.transform='rotateX(0deg) translateY(0) scale(1)'" 
                 onmouseout="this.style.transform='rotateX(10deg) translateY(2rem) scale(0.95)'">
                <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-white/5 bg-zinc-50 dark:bg-zinc-950 aspect-[16/9] flex items-center justify-center relative">
                    <!-- Fake UI Mockup -->
                    <div class="absolute top-0 left-0 w-full h-12 border-b border-zinc-200 dark:border-white/5 flex items-center px-4 gap-2">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                        </div>
                        <div class="mx-auto w-1/3 h-5 rounded-md bg-zinc-200 dark:bg-white/5"></div>
                    </div>
                    <div class="w-full h-full pt-12 flex">
                        <div class="w-48 border-r border-zinc-200 dark:border-white/5 p-4 flex flex-col gap-3">
                            <div class="w-full h-6 rounded-md bg-zinc-200 dark:bg-white/5"></div>
                            <div class="w-3/4 h-6 rounded-md bg-zinc-200 dark:bg-white/5"></div>
                            <div class="w-5/6 h-6 rounded-md bg-zinc-200 dark:bg-white/5"></div>
                        </div>
                        <div class="flex-1 p-6 flex flex-col gap-4">
                            <div class="w-1/3 h-8 rounded-md bg-zinc-200 dark:bg-white/10 mb-4"></div>
                            <div class="w-full h-12 rounded-lg bg-zinc-200 dark:bg-white/5"></div>
                            <div class="w-full h-12 rounded-lg bg-zinc-200 dark:bg-white/5"></div>
                            <div class="w-full h-12 rounded-lg bg-zinc-200 dark:bg-white/5"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Features Section -->
    <section class="w-full max-w-7xl mx-auto px-6 py-24 relative z-10">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-zinc-900 dark:text-white mb-4">Fitur Unggulan</h2>
            <p class="text-zinc-600 dark:text-zinc-400">Dirancang untuk kecepatan dan efisiensi manajemen data Anda.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="p-8 rounded-3xl bg-white dark:bg-zinc-900/40 border border-zinc-200 dark:border-white/5 hover:border-sky-500/30 dark:hover:border-sky-500/30 transition-colors group">
                <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <flux:icon.table-cells class="w-6 h-6 text-sky-600 dark:text-sky-400" />
                </div>
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">Tabel Interaktif</h3>
                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">
                    Pengelolaan pesanan dengan tabel responsif, kolom sticky, dan penghitungan total tonase otomatis yang memudahkan analisis data.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="p-8 rounded-3xl bg-white dark:bg-zinc-900/40 border border-zinc-200 dark:border-white/5 hover:border-sky-500/30 dark:hover:border-sky-500/30 transition-colors group">
                <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <flux:icon.users class="w-6 h-6 text-sky-600 dark:text-sky-400" />
                </div>
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">Manajemen Pelanggan</h3>
                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">
                    Data pelanggan dan pasar tertata rapi. Cegah duplikasi entri pesanan dengan sistem validasi cerdas kami.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="p-8 rounded-3xl bg-white dark:bg-zinc-900/40 border border-zinc-200 dark:border-white/5 hover:border-sky-500/30 dark:hover:border-sky-500/30 transition-colors group">
                <div class="w-12 h-12 rounded-2xl bg-sky-50 dark:bg-sky-500/10 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                    <flux:icon.moon class="w-6 h-6 text-sky-600 dark:text-sky-400" />
                </div>
                <h3 class="text-xl font-semibold text-zinc-900 dark:text-white mb-3">Dark Mode Premium</h3>
                <p class="text-zinc-600 dark:text-zinc-400 text-sm leading-relaxed">
                    Tampilan tema gelap yang elegan dan ramah di mata, dirancang khusus dengan palet warna yang menenangkan.
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="w-full border-t border-zinc-200 dark:border-white/5 py-8 mt-auto relative z-10">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                &copy; {{ date('Y') }} OrderMaster. All rights reserved.
            </p>
            <div class="flex items-center gap-4 text-sm text-zinc-500 dark:text-zinc-400">
                <a href="#" class="hover:text-zinc-900 dark:hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-zinc-900 dark:hover:text-white transition-colors">Terms of Service</a>
            </div>
        </div>
    </footer>

    @fluxScripts
</body>
</html>
