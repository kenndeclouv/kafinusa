<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head', ['title' => View::yieldContent('title')])
</head>
<body class="min-h-dvh bg-zinc-50 dark:bg-zinc-950 flex flex-col items-center justify-center p-6 text-center antialiased">
    <div class="max-w-md w-full bg-white dark:bg-zinc-900 rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.12)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.5)] border border-zinc-200 dark:border-white/10 flex flex-col items-center">
        @yield('icon')
        <h1 class="text-4xl font-bold text-zinc-900 dark:text-white mb-2 tracking-tight">@yield('code')</h1>
        <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200 mb-2">@yield('message')</h2>
        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-8">@yield('description')</p>
        
        @hasSection('action')
            @yield('action')
        @else
            <a href="{{ url('/') }}" class="w-full py-3 px-4 bg-accent hover:opacity-90 text-white rounded-full font-medium transition-colors text-center shadow-md">
                Kembali ke Beranda
            </a>
        @endif
    </div>
</body>
</html>
