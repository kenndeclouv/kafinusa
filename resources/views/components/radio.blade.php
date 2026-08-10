{{-- resources/views/components/radio.blade.php --}}
@props([
    'name' => null,
    'checked' => false,
    'disabled' => false,
    'id' => null,
    'label' => null,
    'description' => null,
    'value' => null,
])

@php
    $inputId = $id ?? 'radio-' . uniqid();
@endphp

@once
    <style>
        .x-rad-label {
            display: inline-flex;
            align-items: flex-start;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }

        .x-rad-label[data-disabled="true"] {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .x-rad-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
        }

        .x-rad-box {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 9999px; /* rounded-full */
            flex-shrink: 0;
            margin-top: 0.125rem; /* align with text if multiline */
            
            /* OFF State */
            background: rgba(161, 161, 170, 0.15);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(161, 161, 170, 0.3);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dark .x-rad-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        /* ON State via :has() */
        .x-rad-label:has(.x-rad-input:checked) .x-rad-box {
            background: white;
            border-color: var(--color-accent);
            box-shadow: 
                inset 0 1px 2px rgba(0, 0, 0, 0.1),
                0 0 0 3px color-mix(in srgb, var(--color-accent) 25%, transparent);
        }

        .dark .x-rad-label:has(.x-rad-input:checked) .x-rad-box {
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 
                inset 0 1px 2px rgba(0, 0, 0, 0.2),
                0 0 0 3px color-mix(in srgb, var(--color-accent) 25%, transparent);
        }

        .x-rad-label:focus-within .x-rad-box {
            outline: 2px solid var(--color-accent);
            outline-offset: 2px;
        }

        /* Radio Dot Drawing Animation */
        .x-rad-dot {
            width: 0.6rem;
            height: 0.6rem;
            border-radius: 50%;
            background-color: var(--color-accent);
            transform: scale(0);
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .x-rad-label:has(.x-rad-input:checked) .x-rad-dot {
            transform: scale(1);
        }
        
        /* Squish effect on press */
        .x-rad-label:active:has(.x-rad-input:not(:checked)) .x-rad-box {
            transform: scale(0.9);
        }
        
        .x-rad-label:active:has(.x-rad-input:checked) .x-rad-box {
            transform: scale(0.9);
        }
    </style>
@endonce

<label class="x-rad-label" data-disabled="{{ $disabled ? 'true' : 'false' }}" for="{{ $inputId }}">
    <input type="radio" id="{{ $inputId }}" class="x-rad-input"
        @if ($name) name="{{ $name }}" @endif
        @if ($value !== null) value="{{ $value }}" @endif
        @if ($checked) checked @endif
        @if ($disabled) disabled @endif
        {{ $attributes->whereStartsWith(['wire:', 'x-model', 'x-bind', 'x-', '@', ':', 'value']) }}>

    <div class="x-rad-box" aria-hidden="true">
        <div class="x-rad-dot"></div>
    </div>

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
