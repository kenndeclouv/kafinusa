@props([
    'disabled' => false,
    'label' => null,
    'description' => null,
    'variant' => 'inside', // inside, outside, ios
])

@php
    $inputId = $attributes->get('id') ?? 'stepper-' . uniqid();
@endphp

<div x-data="{
    increment() {
        if ($refs.input.disabled) return;
        let old = $refs.input.value;
        $refs.input.stepUp();
        if (old !== $refs.input.value) {
            $refs.input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    },
    decrement() {
        if ($refs.input.disabled) return;
        let old = $refs.input.value;
        $refs.input.stepDown();
        if (old !== $refs.input.value) {
            $refs.input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
}" class="flex flex-col space-y-1 w-full">
    @if ($label)
        <label for="{{ $inputId }}" class="text-sm font-medium text-zinc-900 dark:text-white">
            {{ $label }}
        </label>
    @endif

    <div class="flex items-center {{ $variant === 'outside' ? 'gap-2' : '' }}">
        @if ($variant === 'outside')
            <!-- Outside Variant -->
            <div class="flex-1 relative">
                <input id="{{ $inputId }}" x-ref="input" type="number" {{ $disabled ? 'disabled' : '' }}
                    {{ $attributes->whereStartsWith(['wire:', 'x-', '@', 'name', 'value', 'min', 'max', 'step', 'placeholder']) }}
                    class="block w-full h-10 rounded-xl border border-zinc-200 border-b-zinc-300/80 dark:border-white/10 bg-white dark:bg-white/10 px-3 py-2 text-sm text-zinc-900 dark:text-zinc-300 shadow-sm outline-none focus:border-zinc-400 focus:ring-0 dark:focus:border-zinc-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors
                           [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
            </div>

            <!-- Stepper Buttons (Apple style outside) -->
            <div
                class="flex flex-col items-center bg-zinc-100 dark:bg-white/5 rounded-xl overflow-hidden border border-zinc-200 border-b-zinc-300/80 dark:border-white/10 w-11 shrink-0 h-10 shadow-sm {{ $disabled ? 'opacity-50 pointer-events-none' : '' }}">
                <button type="button" @click="increment"
                    class="flex items-center justify-center w-full flex-1 hover:bg-zinc-200 dark:hover:bg-white/10 active:bg-zinc-300 dark:active:bg-white/20 transition-colors cursor-pointer"
                    aria-label="Increase" tabindex="-1">
                    <svg class="w-3.5 h-3.5 text-zinc-700 dark:text-zinc-300 mt-0.5" fill="none" viewBox="0 0 24 24"
                        stroke-width="3" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                    </svg>
                </button>
                <div class="h-px w-3/4 bg-zinc-200 dark:bg-white/10 shrink-0"></div>
                <button type="button" @click="decrement"
                    class="flex items-center justify-center w-full flex-1 hover:bg-zinc-200 dark:hover:bg-white/10 active:bg-zinc-300 dark:active:bg-white/20 transition-colors cursor-pointer"
                    aria-label="Decrease" tabindex="-1">
                    <svg class="w-3.5 h-3.5 text-zinc-700 dark:text-zinc-300 mb-0.5" fill="none" viewBox="0 0 24 24"
                        stroke-width="3" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
            </div>
        @elseif ($variant === 'ios')
            <!-- iOS Variant -->
            <div
                class="flex-1 relative flex items-stretch rounded-lg border border-zinc-200/50 dark:border-white/5 bg-zinc-100 dark:bg-white/10 shadow-xs focus-within:border-zinc-400 focus-within:ring-0 dark:focus-within:border-zinc-500 overflow-hidden transition-colors h-[34px]">

                <input id="{{ $inputId }}" x-ref="input" type="number" {{ $disabled ? 'disabled' : '' }}
                    {{ $attributes->whereStartsWith(['wire:', 'x-', '@', 'name', 'value', 'min', 'max', 'step', 'placeholder']) }}
                    class="block w-full border-none bg-transparent px-3 py-0 text-[14px] text-zinc-900 dark:text-zinc-300 outline-none focus:ring-0 disabled:opacity-50 disabled:cursor-not-allowed text-center
                           [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />

                <div
                    class="flex flex-col items-center bg-zinc-200/50 dark:bg-white/5 border-l border-zinc-200/50 dark:border-white/5 w-8 shrink-0 {{ $disabled ? 'opacity-50 pointer-events-none' : '' }}">
                    <button type="button" @click="increment"
                        class="flex items-center justify-center w-full flex-1 hover:bg-zinc-300/50 dark:hover:bg-white/10 active:bg-zinc-400/50 dark:active:bg-white/20 transition-colors cursor-pointer"
                        aria-label="Increase" tabindex="-1">
                        <svg class="w-3.5 h-3.5 text-zinc-700 dark:text-zinc-300 mt-0.5" fill="none"
                            viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                        </svg>
                    </button>
                    <div class="h-px w-full bg-zinc-300/50 dark:bg-white/10 shrink-0"></div>
                    <button type="button" @click="decrement"
                        class="flex items-center justify-center w-full flex-1 hover:bg-zinc-300/50 dark:hover:bg-white/10 active:bg-zinc-400/50 dark:active:bg-white/20 transition-colors cursor-pointer"
                        aria-label="Decrease" tabindex="-1">
                        <svg class="w-3.5 h-3.5 text-zinc-700 dark:text-zinc-300 mb-0.5" fill="none"
                            viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                </div>
            </div>
        @else
            <!-- Inside Variant -->
            <div
                class="flex-1 relative flex items-stretch rounded-xl border border-zinc-200 border-b-zinc-300/80 dark:border-white/10 bg-white dark:bg-white/10 shadow-sm focus-within:border-zinc-400 focus-within:ring-0 dark:focus-within:border-zinc-500 overflow-hidden transition-colors h-10">

                <input id="{{ $inputId }}" x-ref="input" type="number" {{ $disabled ? 'disabled' : '' }}
                    {{ $attributes->whereStartsWith(['wire:', 'x-', '@', 'name', 'value', 'min', 'max', 'step', 'placeholder']) }}
                    class="block w-full border-none bg-transparent px-3 py-2 text-sm text-zinc-900 dark:text-zinc-300 outline-none focus:ring-0 disabled:opacity-50 disabled:cursor-not-allowed
                           [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />

                <div
                    class="flex flex-col items-center bg-zinc-100 dark:bg-white/5 border-l border-zinc-200 dark:border-white/10 w-9 shrink-0 {{ $disabled ? 'opacity-50 pointer-events-none' : '' }}">
                    <button type="button" @click="increment"
                        class="flex items-center justify-center w-full flex-1 hover:bg-zinc-200 dark:hover:bg-white/10 active:bg-zinc-300 dark:active:bg-white/20 transition-colors cursor-pointer"
                        aria-label="Increase" tabindex="-1">
                        <svg class="w-3.5 h-3.5 text-zinc-700 dark:text-zinc-300 mt-0.5" fill="none"
                            viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                        </svg>
                    </button>
                    <div class="h-px w-full bg-zinc-200 dark:bg-white/10 shrink-0"></div>
                    <button type="button" @click="decrement"
                        class="flex items-center justify-center w-full flex-1 hover:bg-zinc-200 dark:hover:bg-white/10 active:bg-zinc-300 dark:active:bg-white/20 transition-colors cursor-pointer"
                        aria-label="Decrease" tabindex="-1">
                        <svg class="w-3.5 h-3.5 text-zinc-700 dark:text-zinc-300 mb-0.5" fill="none"
                            viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if ($description)
        <span class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ $description }}</span>
    @endif

    @error($attributes->get('name') ?? ($attributes->get('wire:model') ?? $attributes->get('wire:model.live')))
        <span class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</span>
    @enderror
</div>
