<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-dvh bg-accent antialiased flex flex-col lg:flex-row relative overflow-x-hidden">

    <!-- Mobile Decorative Background -->
    <div class="fixed inset-0 bg-accent lg:hidden -z-10">
        <!-- Abstract decorative shapes -->
        <div class="absolute -top-12 -left-12 w-64 h-64 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute top-10 -right-20 w-80 h-80 rounded-full bg-white/20 blur-3xl"></div>
        <div class="absolute top-32 left-10 w-48 h-48 rounded-full bg-white/10 blur-xl"></div>
    </div>

    <!-- Mobile Top Area (Logo) -->
    <div class="lg:hidden flex flex-col items-center justify-center flex-1 py-12 relative z-10">
        <x-app-logo-icon class="h-16 w-16 fill-current text-white mb-4 drop-shadow-md" />
        <h1 class="text-3xl font-bold text-white tracking-tight drop-shadow-md">{{ config('app.name', 'Laravel') }}</h1>
    </div>

    <!-- Desktop Left Side (hidden on mobile) -->
    <div class="hidden lg:flex w-1/2 relative flex-col p-10 text-white z-10 min-h-dvh">
        <div class="absolute inset-0 bg-zinc-900">
            <img src="{{ asset('images/auth-bg.png') }}"
                class="absolute inset-0 h-full w-full object-cover opacity-50 mix-blend-luminosity"
                alt="Industrial Background" />
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-900/90 via-zinc-900/10 to-zinc-900/40"></div>
        </div>
        <a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-medium" wire:navigate>
            <span class="flex h-10 w-10 items-center justify-center rounded-md">
                <x-app-logo-icon class="me-2 h-7 fill-current text-white" />
            </span>
            {{ config('app.name', 'Laravel') }}
        </a>
        @php
            [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
        @endphp
        <div class="relative z-20 mt-auto">
            <blockquote class="space-y-2">
                <flux:heading size="lg" class="text-white">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                <footer>
                    <flux:heading class="text-zinc-300">{{ trim($author) }}</flux:heading>
                </footer>
            </blockquote>
        </div>
    </div>

    <!-- Right Side (Form Area) -->
    <div class="w-full lg:w-1/2 flex flex-col justify-end lg:justify-center lg:min-h-dvh lg:bg-white lg:dark:bg-neutral-950 relative z-20 shrink-0">
        <!-- The Card -->
        <div class="block w-full lg:max-w-md lg:mx-auto bg-white dark:bg-zinc-900 lg:bg-transparent lg:dark:bg-transparent rounded-t-[40px] lg:rounded-none px-6 pt-14 pb-10 lg:p-12 shadow-[0_-10px_40px_rgba(0,0,0,0.2)] lg:shadow-none relative">
            {{ $slot }}
        </div>
    </div>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>
