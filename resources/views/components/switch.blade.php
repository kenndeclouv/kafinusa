{{-- resources/views/components/switch.blade.php --}}
{{--
    Usage:
    <x-switch wire:model="myVar" />
    <x-switch wire:model="myVar" size="sm" />
    <x-switch wire:model="myVar" size="md" />   ← default
    <x-switch wire:model="myVar" size="lg" />
--}}

@props([
    'name' => null,
    'checked' => false,
    'disabled' => false,
    'id' => null,
    'size' => 'md',
    'label' => null,
    'description' => null,
])

@php
    $inputId = $id ?? 'switch-' . uniqid();
@endphp

@once
    <style>
        /* ═══════════════════════════════════════════════
               x-switch — Neo-brutalism + Liquid Glass
               Visual state driven by CSS :has() — no JS timing issues.
            ═══════════════════════════════════════════════ */

        /* ── Size tokens (default = md) ── */
        .x-switch-label {
            --sw-track-w: 3.375rem;
            /* 54px */
            --sw-track-h: 1.5rem;
            /* 24px */
            --sw-pad: 2px;
            --sw-knob-w: 2rem;
            /* 32px */
            --sw-knob-h: 1.25rem;
            /* 20px */
            --sw-tx: 1.125rem;
            /* 54 - 4 - 32 = 18px */
            --sw-icon-bar-w: 0.5rem;
            --sw-icon-bar-h: 0.65rem;
            --sw-icon-ring: 0.55rem;
            --sw-icon-inset: 0.4rem;

            position: relative;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }

        .x-switch-label[data-size="sm"] {
            --sw-track-w: 2.75rem;
            /* 44px */
            --sw-track-h: 1.25rem;
            /* 20px */
            --sw-pad: 2px;
            --sw-knob-w: 1.5rem;
            /* 24px */
            --sw-knob-h: 1rem;
            /* 16px */
            --sw-tx: 1rem;
            /* 44 - 4 - 24 = 16px */
            --sw-icon-bar-w: 0.35rem;
            --sw-icon-bar-h: 0.5rem;
            --sw-icon-ring: 0.4rem;
            --sw-icon-inset: 0.3rem;
        }

        .x-switch-label[data-size="lg"] {
            --sw-track-w: 4.25rem;
            /* 68px */
            --sw-track-h: 1.875rem;
            /* 30px */
            --sw-pad: 3px;
            --sw-knob-w: 2.5rem;
            /* 40px */
            --sw-knob-h: 1.5rem;
            /* 24px */
            --sw-tx: 1.375rem;
            /* 68 - 6 - 40 = 22px */
            --sw-icon-bar-w: 0.6rem;
            --sw-icon-bar-h: 0.8rem;
            --sw-icon-ring: 0.65rem;
            --sw-icon-inset: 0.5rem;
        }

        .x-switch-label[data-disabled="true"] {
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* ── Native checkbox: hidden ── */
        .x-switch-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
        }

        /* ── Track ── */
        .x-switch-track {
            position: relative;
            display: inline-flex;
            align-items: center;
            width: var(--sw-track-w);
            height: var(--sw-track-h);
            border-radius: 9999px;
            padding: var(--sw-pad);
            flex-shrink: 0;
            transition:
                background 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                box-shadow 0.35s cubic-bezier(0.4, 0, 0.2, 1);

            /* OFF */
            background: rgba(161, 161, 170, 0.20);
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.12);
        }

        .dark .x-switch-track {
            background: rgba(255, 255, 255, 0.08);
        }

        /* ── ON state via :has() — always in sync, no JS timing issues ── */
        .x-switch-label:has(.x-switch-input:checked) .x-switch-track {
            background: var(--color-accent);
            box-shadow:
                inset 0 1px 3px rgba(0, 0, 0, 0.10),
                0 0 0 3px color-mix(in srgb, var(--color-accent) 25%, transparent);
        }

        /* Focus ring */
        .x-switch-label:focus-within .x-switch-track {
            outline: 2px solid var(--color-accent);
            outline-offset: 2px;
        }

        /* ── Power-I icon (left, shows ON) ── */
        .x-switch-icon-on {
            position: absolute;
            left: var(--sw-icon-inset);
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 1;
        }

        .x-switch-label:has(.x-switch-input:checked) .x-switch-icon-on {
            opacity: 1;
        }

        .x-switch-icon-on svg {
            width: var(--sw-icon-bar-w);
            height: var(--sw-icon-bar-h);
            stroke: rgba(255, 255, 255, 0.9);
        }

        /* ── Power-O icon (right, shows when OFF) ── */
        .x-switch-icon-off {
            position: absolute;
            right: var(--sw-icon-inset);
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            opacity: 1;
            transition: opacity 0.2s;
            z-index: 1;
        }

        .x-switch-label:has(.x-switch-input:checked) .x-switch-icon-off {
            opacity: 0;
        }

        .x-switch-icon-off svg {
            width: var(--sw-icon-ring);
            height: var(--sw-icon-ring);
            stroke: rgba(100, 100, 120, 0.55);
        }

        .dark .x-switch-icon-off svg {
            stroke: rgba(255, 255, 255, 0.30);
        }

        /* ── Knob — pill liquid glass ── */
        .x-switch-knob {
            position: relative;
            width: var(--sw-knob-w);
            height: var(--sw-knob-h);
            border-radius: 9999px;
            flex-shrink: 0;
            z-index: 2;
            transform-origin: left center;
            /* Bounce lebih lebay dan durasi sedikit lebih lama biar lebih kerasa */
            transition: transform 0.55s cubic-bezier(0.34, 1.56, 0.64, 1), width 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            background: rgba(255, 255, 255, 0.82);
            -webkit-backdrop-filter: blur(12px) saturate(1.3) brightness(1.08);
            backdrop-filter: blur(12px) saturate(1.3) brightness(1.08);
            isolation: isolate;
        }

        /* Specular + drop shadow */
        .x-switch-knob::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            box-shadow:
                inset 0 1.5px 0 0 rgba(255, 255, 255, 0.95),
                inset 0 -1px 0 0 rgba(0, 0, 0, 0.06),
                0 3px 8px rgba(0, 0, 0, 0.22),
                0 1px 2px rgba(0, 0, 0, 0.14);
        }

        /* Outer glass rim */
        .x-switch-knob::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            border: 1px solid rgba(255, 255, 255, 0.55);
        }

        /* ON — slide right */
        .x-switch-label:has(.x-switch-input:checked) .x-switch-knob {
            transform: translateX(var(--sw-tx));
            transform-origin: right center;
        }

        /* Press squish OFF */
        .x-switch-label:active:has(.x-switch-input:not(:checked)) .x-switch-knob {
            transform: scaleX(1.32);
            transition: transform 0.15s ease-out;
        }

        /* Press squish ON */
        .x-switch-label:active:has(.x-switch-input:checked) .x-switch-knob {
            transform: translateX(var(--sw-tx)) scaleX(1.32);
            transition: transform 0.15s ease-out;
        }
    </style>
@endonce

<label class="x-switch-label" data-size="{{ $size }}" data-disabled="{{ $disabled ? 'true' : 'false' }}"
    for="{{ $inputId }}">
    {{-- Hidden native checkbox --}}
    <input type="checkbox" id="{{ $inputId }}" class="x-switch-input"
        @if ($name) name="{{ $name }}" @endif
        @if ($attributes->has('value')) value="{{ $attributes->get('value') }}" @endif
        @if ($checked) checked @endif @if ($disabled) disabled @endif
        {{ $attributes->whereStartsWith(['wire:', 'x-model', 'x-bind', 'x-', '@', ':']) }}>

    {{-- Visual Track —— visual state is driven purely by CSS :has(), no JS needed ── --> --}}
    <div class="x-switch-track" role="switch" aria-checked="{{ $checked ? 'true' : 'false' }}">

        {{-- Power ON icon: I (left) --}}
        <span class="x-switch-icon-on">
            <svg viewBox="0 0 6 10" fill="none" stroke-width="2.2" stroke-linecap="round">
                <line x1="3" y1="1" x2="3" y2="9" />
            </svg>
        </span>

        {{-- Power OFF icon: O (right) --}}
        <span class="x-switch-icon-off">
            <svg viewBox="0 0 10 10" fill="none" stroke-width="2" stroke-linecap="round">
                <circle cx="5" cy="5" r="3.5" />
            </svg>
        </span>

        {{-- Pill knob --}}
        <div class="x-switch-knob"></div>
    </div>

    {{-- Label & Description --}}
    @if ($label || $description)
        <div class="ml-3 flex flex-col justify-center">
            @if ($label)
                <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $label }}</span>
            @endif
            @if ($description)
                <span class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 leading-tight">{{ $description }}</span>
            @endif
        </div>
    @endif
</label>

@once
    <script>
        (function() {
            'use strict';

            // Only sync aria-checked for accessibility (visual is handled by CSS :has())
            function syncAria(input) {
                var label = input.closest('.x-switch-label');
                var track = label ? label.querySelector('.x-switch-track') : null;
                if (!track) return;
                track.setAttribute('aria-checked', input.checked ? 'true' : 'false');
            }

            function initSwitches() {
                document.querySelectorAll('.x-switch-input').forEach(function(input) {
                    syncAria(input);
                    if (input._xSwitchBound) return;
                    input._xSwitchBound = true;
                    input.addEventListener('change', function() {
                        syncAria(input);
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSwitches);
            } else {
                initSwitches();
            }

            document.addEventListener('livewire:commit', function() {
                requestAnimationFrame(initSwitches);
            });
            document.addEventListener('livewire:navigated', function() {
                requestAnimationFrame(initSwitches);
            });
        })
        ();
    </script>
@endonce
