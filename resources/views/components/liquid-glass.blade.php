@props([
    'blur' => 4,
    'depth' => 30,
    'tint' => 'rgba(255, 255, 255, 0.08)',
    'darkTint' => 'rgba(255, 255, 255, 0.05)',
])

@php
    $uuid = 'glass-' . \Illuminate\Support\Str::random(8);
@endphp

<style>
    .{{ $uuid }} {
        position: relative;
        background: {{ $tint }};
        isolation: isolate;
    }

    /* Dark mode */
    .dark .{{ $uuid }} {
        background: {{ $darkTint }};
    }

    /* Specular highlight layer — top-edge glass shine + subtle inner glow */
    .{{ $uuid }}::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 0;
        border-radius: inherit;
        pointer-events: none;
        box-shadow:
            inset 0 1.5px 0 0 rgba(255, 255, 255, 0.55),
            inset 0 -1px 0 0 rgba(255, 255, 255, 0.08),
            inset 1px 0 0 0 rgba(255, 255, 255, 0.12),
            inset -1px 0 0 0 rgba(255, 255, 255, 0.08),
            inset 0 4px 8px 0 rgba(255, 255, 255, 0.08);
    }

    /* Backdrop + liquid displacement layer */
    .{{ $uuid }}::after {
        content: '';
        position: absolute;
        z-index: -1;
        inset: 0;
        border-radius: inherit;
        pointer-events: none;
        -webkit-backdrop-filter: blur({{ $blur }}px) saturate(1) brightness(1.05);
        backdrop-filter: blur({{ $blur }}px) saturate(1) brightness(1.05);
        -webkit-filter: url(#{{ $uuid }}-filter);
        filter: url(#{{ $uuid }}-filter);
        overflow: hidden;
    }
</style>

<svg style="display: none">
    <filter id="{{ $uuid }}-filter" x="0%" y="0%" width="100%" height="100%" filterUnits="objectBoundingBox">
        <!-- 1. PEMBUAT GELOMBANG (feTurbulence) -->
        <!-- baseFrequency: Ukuran gelombang. 0.01 = ombak besar/lebar. 0.05 = gelombang rapat/pasir -->
        <!-- numOctaves: Detail gelombang. 1 = halus, 3 = banyak detail pecah-pecah (lebih berat di GPU) -->
        <feTurbulence type="fractalNoise" baseFrequency="0.01 0.01" numOctaves="1" seed="5" result="turbulence" />

        <!-- 2. PENGATUR KONTRAS (feComponentTransfer) -->
        <!-- Mengubah warna gelombang jadi data kedalaman (bump map). Biarkan default. -->
        <feComponentTransfer in="turbulence" result="mapped">
            <feFuncR type="gamma" amplitude="1" exponent="10" offset="0.5" />
            <feFuncG type="gamma" amplitude="0" exponent="1" offset="0" />
            <feFuncB type="gamma" amplitude="0" exponent="1" offset="0.5" />
        </feComponentTransfer>

        <!-- 3. PENGHALUS (feGaussianBlur) -->
        <!-- stdDeviation: Tingkat kelembutan kaca. 3 = sangat cair/mulus. 1 = kasar/tajam. -->
        <feGaussianBlur in="turbulence" stdDeviation="3" result="softMap" />

        <!-- 4. PANTULAN CAHAYA (feSpecularLighting) -->
        <!-- surfaceScale: Intensitas cahaya. Makin besar makin terang/kontras pantulannya. -->
        <!-- specularExponent: Tingkat "licin" permukaan. 100 = kaca basah. 20 = plastik doff. -->
        <feSpecularLighting in="softMap" surfaceScale="5" specularConstant="1" specularExponent="100"
            lighting-color="white" result="specLight">
            <!-- Posisi lampu 3D (X, Y, Z) -->
            <fePointLight x="-200" y="-200" z="300" />
        </feSpecularLighting>

        <!-- 5. PENGGABUNG CAHAYA (feComposite) -->
        <feComposite in="specLight" operator="arithmetic" k1="0" k2="1" k3="1" k4="0"
            result="litImage" />

        <!-- 6. EFEK MELENGKUNG/DISTORSI (feDisplacementMap) -->
        <!-- scale: Kekuatan distorsi (menggunakan variabel $depth). 150 = bengkok parah. 10 = getar halus. -->
        <feDisplacementMap in="SourceGraphic" in2="softMap" scale="{{ $depth }}" xChannelSelector="R"
            yChannelSelector="G" />
    </filter>
</svg>

<div {{ $attributes->merge(['class' => $uuid]) }}>
    <div class="relative z-10 w-full h-full">
        {{ $slot }}
    </div>
</div>
