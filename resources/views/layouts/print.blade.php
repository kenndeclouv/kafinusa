<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ filled($title ?? null) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
    <style>
        @media print {
            html, body {
                background-color: white !important;
                color: black !important;
            }
            .no-print { display: none !important; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body class="min-h-screen bg-white text-zinc-900 font-sans print:bg-white print:text-black">
    {{ $slot }}
    @livewireScripts
    @fluxScripts

</body>
</html>
