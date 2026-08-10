{{-- resources/views/components/checkbox.blade.php --}}
@props([
    'name' => null,
    'checked' => false,
    'disabled' => false,
    'id' => null,
    'label' => null,
    'description' => null,
])

@php
    $inputId = $id ?? 'checkbox-' . uniqid();
@endphp

@once
    <style>
        .x-chk-label {
            display: inline-flex;
            align-items: flex-start;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }

        .x-chk-label[data-disabled="true"] {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .x-chk-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            pointer-events: none;
        }

        .x-chk-box {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 0.375rem; /* rounded-md */
            flex-shrink: 0;
            margin-top: 0.125rem; /* align with text if multiline */
            
            /* OFF State */
            background: rgba(161, 161, 170, 0.15);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(161, 161, 170, 0.3);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .dark .x-chk-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        /* ON State via :has() */
        .x-chk-label:has(.x-chk-input:checked) .x-chk-box {
            background: var(--color-accent);
            border-color: var(--color-accent);
            box-shadow: 
                inset 0 1px 2px rgba(0, 0, 0, 0.1),
                0 0 0 3px color-mix(in srgb, var(--color-accent) 25%, transparent);
        }

        .x-chk-label:focus-within .x-chk-box {
            outline: 2px solid var(--color-accent);
            outline-offset: 2px;
        }

        /* Checkmark Icon Drawing Animation */
        .x-chk-icon {
            width: 0.8rem;
            height: 0.8rem;
            fill: none;
            stroke: white;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-dasharray: 16;
            stroke-dashoffset: 16;
            transition: stroke-dashoffset 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .x-chk-label:has(.x-chk-input:checked) .x-chk-icon {
            stroke-dashoffset: 0;
        }
        
        /* Squish effect on press */
        .x-chk-label:active:has(.x-chk-input:not(:checked)) .x-chk-box {
            transform: scale(0.9);
        }
        
        .x-chk-label:active:has(.x-chk-input:checked) .x-chk-box {
            transform: scale(0.9);
        }
    </style>
@endonce

<label class="x-chk-label" data-disabled="{{ $disabled ? 'true' : 'false' }}" for="{{ $inputId }}">
    <input type="checkbox" id="{{ $inputId }}" class="x-chk-input"
        @if ($name) name="{{ $name }}" @endif
        @if ($checked) checked @endif
        @if ($disabled) disabled @endif
        {{ $attributes->whereStartsWith(['wire:', 'x-model', 'x-bind', 'x-', '@', ':', 'value']) }}>

    <div class="x-chk-box" aria-hidden="true">
        <svg class="x-chk-icon" viewBox="0 0 12 10">
            <polyline points="2 5.5 4.5 8 10 2"></polyline>
        </svg>
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
