@props([
    'label' => null,
    'options' => [],
    'placeholder' => 'Select an option',
    'searchable' => true,
    'searchPlaceholder' => 'Search...',
    'multiple' => false,
    'disabled' => false,
    'size' => 'base',
    'variant' => 'default',
])

@if ($label)
    <flux:field>
        <flux:label>{{ $label }}</flux:label>
@endif

@php
    $normalised = [];
    foreach ($options as $key => $val) {
        if (is_array($val)) {
            $normalised[] = [
                'value' => $val['value'] ?? $key,
                'label' => $val['label'] ?? ($val['value'] ?? $key),
                'icon' => $val['icon'] ?? null,
                'disabled' => (bool) ($val['disabled'] ?? false),
            ];
        } else {
            $normalised[] = ['value' => $key, 'label' => $val, 'icon' => null, 'disabled' => false];
        }
    }
    $optionsJson = json_encode($normalised);

    $modelAttr = $attributes->wire('model');
    $wireProp = $modelAttr ? $modelAttr->value() : null;
    $isMultiple = (bool) $multiple;

    $sizeClasses = match ($size) {
        'xs' => 'ps-2 pe-2 text-xs',
        'sm' => 'ps-2.5 pe-2.5 text-sm',
        'lg' => 'ps-4 pe-4 text-base',
        default => $variant === 'ios' ? 'px-3 pe-1 text-[14px]' : 'ps-3 pe-3 text-base sm:text-sm',
    };

    $triggerHeight = match ($size) {
        'xs' => 'min-h-6 py-1',
        'sm' => 'min-h-8 py-1',
        'lg' => 'min-h-12 py-2',
        default => $variant === 'ios' ? 'min-h-[34px] py-1' : 'min-h-10 py-1.5',
    };

    $triggerClasses =
        $variant === 'ios'
            ? 'group ' .
                $triggerHeight .
                ' w-full max-w-full ms-auto border border-zinc-200/50 dark:border-white/5 shadow-xs rounded-lg flex items-stretch p-0 overflow-hidden bg-zinc-100 dark:bg-white/10 hover:bg-zinc-200 dark:hover:bg-white/20 appearance-none text-[14px] text-zinc-700 dark:text-zinc-300 disabled:text-zinc-500 dark:disabled:text-zinc-400 focus:outline-none ' .
                ($disabled ? 'cursor-not-allowed' : 'cursor-pointer')
            : 'group w-full ' .
                $triggerHeight .
                ' border rounded-xl flex items-stretch p-0 overflow-hidden disabled:shadow-none dark:shadow-none appearance-none leading-[1.375rem] bg-white dark:bg-white/10 dark:disabled:bg-white/7 text-zinc-700 disabled:text-zinc-500 dark:text-zinc-300 dark:disabled:text-zinc-400 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200 dark:border-white/10 dark:disabled:border-white/5 data-invalid:shadow-none data-invalid:border-red-500 dark:data-invalid:border-red-500 disabled:data-invalid:border-red-500 dark:disabled:data-invalid:border-red-500 focus:outline-none focus:ring-2 focus:ring-accent/50 focus:border-accent ' .
                ($disabled ? 'cursor-not-allowed' : 'cursor-pointer');

    $textAlignment = 'text-left';
    $iconWrapperClasses =
        $variant === 'ios'
            ? 'flex items-center justify-center shrink-0 w-6 h-7 mr-1 my-auto rounded-md bg-transparent ' .
                ($disabled ? 'opacity-50 pointer-events-none' : '')
            : 'flex items-center justify-center shrink-0 w-10 h-7 mr-1.5 my-auto rounded-md bg-zinc-100 dark:bg-white/10 group-hover:bg-zinc-200 dark:group-hover:bg-white/20 ' .
                ($disabled ? 'opacity-50 pointer-events-none' : '');
@endphp

@once
    <style>
        /*
        * The dropdown uses `popover` API which promotes it to the top-layer,
        * sitting above <dialog> elements. No z-index tricks needed.
        * We style it to look like a regular div — no default popover margin/border.
        */
        .ss-dropdown-popover {
            display: none;
            position: fixed;
            inset: unset;
            margin: 0;
            padding: 0;
            border: none;
            background: transparent;
            overflow: visible;
            width: auto;
            max-width: none;
            max-height: none;
            color: inherit;
        }

        .ss-dropdown-popover:popover-open {
            display: block;
        }
    </style>
@endonce

<div x-modelable="value" x-data="{
    open: false,
    search: '',
    multiple: {{ $isMultiple ? 'true' : 'false' }},
    @if ($wireProp) value: $wire.entangle('{{ $wireProp }}').live,
    @else
    value: null, @endif
    options: {{ $optionsJson }},
    searchable: {{ $searchable ? 'true' : 'false' }},

    get filtered() {
        if (!this.searchable || this.search.trim() === '') return this.options;
        const q = this.search.toLowerCase();
        return this.options.filter(o => o.label.toLowerCase().includes(q));
    },

    get selectedLabel() {
        if (this.multiple) return null;
        if (this.value === '' || this.value === null || this.value === undefined) return null;
        const found = this.options.find(o => String(o.value) === String(this.value));
        return found ? found.label : null;
    },

    get selectedIcon() {
        if (this.multiple) return null;
        if (this.value === '' || this.value === null || this.value === undefined) return null;
        const found = this.options.find(o => String(o.value) === String(this.value));
        return found ? found.icon : null;
    },

    get selectedItems() {
        if (!this.multiple) return [];
        const vals = Array.isArray(this.value) ? this.value : [];
        return vals.map(v => this.options.find(o => String(o.value) === String(v))).filter(Boolean);
    },

    isSelected(val) {
        if (this.multiple) {
            const vals = Array.isArray(this.value) ? this.value : [];
            return vals.some(v => String(v) === String(val));
        }
        return String(this.value) === String(val);
    },

    select(val) {
        const opt = this.options.find(o => String(o.value) === String(val));
        if (opt && opt.disabled) return;
        if (this.multiple) {
            const vals = Array.isArray(this.value) ? [...this.value] : [];
            const idx = vals.findIndex(v => String(v) === String(val));
            idx === -1 ? vals.push(val) : vals.splice(idx, 1);
            this.value = vals;
        } else {
            this.value = val;
            this.closeDropdown();
        }
    },

    removeTag(val) {
        if (!this.multiple) return;
        const vals = Array.isArray(this.value) ? [...this.value] : [];
        this.value = vals.filter(v => String(v) !== String(val));
    },

    getPopoverEl() {
        return this.$refs.popoverEl;
    },

    positionPopover() {
        const trigger = this.$refs.trigger;
        const popover = this.getPopoverEl();
        if (!trigger || !popover) return;

        const rect = trigger.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom;
        const spaceAbove = rect.top;
        const dropdownH = 280;
        const goUp = spaceBelow < dropdownH && spaceAbove > spaceBelow;

        popover.style.top = (goUp ? rect.top - dropdownH - 4 : rect.bottom + 4) + 'px';
        popover.style.left = rect.left + 'px';
        popover.style.width = rect.width + 'px';
    },

    openDropdown() {
        const popover = this.getPopoverEl();
        if (!popover) return;
        this.positionPopover();
        try { popover.showPopover(); } catch(e) {}
        this.open = true;
    },

    closeDropdown() {
        const popover = this.getPopoverEl();
        if (popover) {
            try { popover.hidePopover(); } catch (e) {}
        }
        this.open = false;
        this.search = '';
    },

    toggleOpen() {
        this.open ? this.closeDropdown() : this.openDropdown();
    },

    init() {
        const popoverEl = this.getPopoverEl();
        if (popoverEl) {
            popoverEl.addEventListener('toggle', (e) => {
                if (e.newState === 'closed') {
                    this.open = false;
                    this.search = '';
                }
            });
        }

        this.$watch('value', () => {
            this.$el.dispatchEvent(new Event('input', { bubbles: true }));
        });
        
        // Ensure options are updated when Livewire morphs the element
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-options') {
                    this.options = JSON.parse(this.$el.getAttribute('data-options') || '[]');
                }
            });
        });
        observer.observe(this.$el, { attributes: true });

        const reposition = () => { if (this.open) this.positionPopover(); };
        window.addEventListener('scroll', reposition, true);
        window.addEventListener('resize', reposition);

        this.$cleanup = () => {
            observer.disconnect();
            window.removeEventListener('scroll', reposition, true);
            window.removeEventListener('resize', reposition);
            const p = this.getPopoverEl();
            if (p) p.remove();
        };
    },
}" @keydown.escape.window="closeDropdown()" data-options="{{ $optionsJson }}" @if ($wireProp)
    data-kythia-model="{{ $wireProp }}"
    @endif
    @click.outside="closeDropdown()"
    {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => 'relative w-full ' . ($variant === 'ios' ? 'flex justify-end' : '')]) }}>

    @php
        // Attempt to resolve initial state to prevent any FOUC before Alpine initializes
        $initialValue = null;
        $initialLabel = $placeholder;
        $initialIcon = null;

        if ($wireProp && isset($__livewire)) {
            $initialValue = data_get($__livewire, $wireProp);
        } elseif (isset($value)) {
            $initialValue = $value;
        }

        if ($initialValue !== null && $initialValue !== '' && !is_array($initialValue)) {
            foreach ($normalised as $opt) {
                if ((string) $opt['value'] === (string) $initialValue) {
                    $initialLabel = $opt['label'];
                    $initialIcon = $opt['icon'];
                    break;
                }
            }
        }

        $initialTextClass =
            $initialValue !== null && $initialValue !== '' && !is_array($initialValue)
                ? 'text-zinc-700 dark:text-zinc-300'
                : 'text-zinc-400 dark:text-zinc-500';
    @endphp

    {{-- ── Trigger button ── --}}
    <button type="button" x-ref="trigger" @click="toggleOpen()" :aria-expanded="open" {{ $disabled ? 'disabled' : '' }}
        class="{{ $triggerClasses }}">

        <div class="flex-1 min-w-0 flex items-center {{ $sizeClasses }} {{ $textAlignment }}">
            {{-- Single Select Mode --}}
            <div x-show="!multiple" class="flex items-center gap-2 truncate w-full">
                @if ($initialIcon)
                    <img x-show="selectedIcon" :src="selectedIcon || '{{ $initialIcon }}'"
                        class="w-5 h-5 rounded object-cover shrink-0" alt="" />
                @else
                    <img x-cloak x-show="selectedIcon" :src="selectedIcon"
                        class="w-5 h-5 rounded object-cover shrink-0" alt="" />
                @endif
                <span x-text="selectedLabel ?? '{{ $placeholder }}'"
                    :class="selectedLabel ? 'truncate text-zinc-700 dark:text-zinc-300' :
                        'text-zinc-400 dark:text-zinc-500 truncate'"
                    class="truncate w-full {{ $initialTextClass }}">
                    {{ $initialLabel }}
                </span>
            </div>

            {{-- Multiple Select Mode --}}
            <div x-show="multiple" x-cloak class="flex flex-wrap gap-1.5 flex-1 min-w-0">
                <span x-show="selectedItems.length === 0"
                    class="text-zinc-400 dark:text-zinc-500">{{ $placeholder }}</span>
                <template x-for="item in selectedItems" :key="item.value">
                    <span
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-sm font-medium bg-zinc-200 dark:bg-white/15 text-zinc-700 dark:text-zinc-200">
                        <span x-text="item.label"></span>
                        <button type="button" @click.stop="removeTag(item.value)"
                            class="text-zinc-400 cursor-pointer hover:text-zinc-600 dark:hover:text-white transition leading-none">
                            <svg class="w-3 h-3" viewBox="0 0 12 12" fill="none">
                                <path d="M2 2L10 10M10 2L2 10" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" />
                            </svg>
                        </button>
                    </span>
                </template>
            </div>
        </div>

        <div class="{{ $iconWrapperClasses }}">
            <svg class="w-4 h-4 text-zinc-400 shrink-0 transition-transform duration-150"
                :class="open ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none">
                <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </div>
    </button>

    {{--
        Dropdown using the Popover API.
        popover="manual" means we control show/hide ourselves (no auto-dismiss on outside click
    --}}
    <div wire:ignore.self x-ref="popoverEl"
        @toggle="if ($event.newState === 'closed') { open = false; search = ''; }" @click.stop popover="manual"
        class="ss-dropdown-popover min-w-[10rem]" @keydown.escape.window="closeDropdown()">
        {{-- Inner wrapper carries all visual styling — keeps rounded corners intact --}}
        <div
            class="rounded-lg border border-zinc-200 dark:border-white/10 bg-white/40 dark:bg-zinc-900/60 backdrop-blur-md shadow-xl overflow-hidden">
            {{-- Search --}}
            <template x-if="searchable">
                <div class="p-2 border-b border-zinc-100 bg-white dark:bg-white/10 dark:border-white/10">
                    <div class="relative flex items-center">
                        <svg class="absolute left-2.5 w-3.5 h-3.5 text-zinc-400 pointer-events-none" viewBox="0 0 16 16"
                            fill="none">
                            <circle cx="7" cy="7" r="4.5" stroke="currentColor" stroke-width="1.5" />
                            <path d="M10.5 10.5L13.5 13.5" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" />
                        </svg>
                        <input type="text" x-model="search" x-ref="searchInput" data-no-dirty
                            placeholder="{{ $searchPlaceholder }}"
                            class="w-full pl-7 pr-2.5 py-1.5 text-sm border rounded-lg
                               bg-zinc-50 dark:bg-white/10 border-zinc-200 dark:border-white/10
                               text-zinc-900 dark:text-zinc-100
                               placeholder-zinc-400 dark:placeholder-zinc-500
                               focus:outline-none focus:ring-2 focus:ring-accent/50">
                    </div>
                </div>
            </template>

            {{-- Options --}}
            <ul class="max-h-56 overflow-y-auto py-1.5 bg-zinc-50 dark:bg-white/10" role="listbox">
                <template x-for="option in filtered" :key="option.value">
                    <li @click.prevent="!option.disabled && select(option.value)" role="option"
                        :aria-selected="isSelected(option.value)"
                        :class="{
                            'mx-1.5 flex items-center justify-between gap-2 px-3 py-2 text-sm select-none transition-colors rounded-lg': true,
                            'cursor-not-allowed opacity-40': option.disabled,
                            'cursor-pointer hover:bg-zinc-100 dark:hover:bg-white/10 text-zinc-800 dark:text-zinc-200':
                                !option.disabled,
                            'font-medium text-zinc-900 dark:text-white bg-zinc-50 dark:bg-white/5': isSelected(option
                                .value) && !option.disabled,
                        }">
                        <div class="flex items-center gap-2 truncate">
                            <template x-if="option.icon">
                                <img :src="option.icon" class="w-5 h-5 rounded object-cover shrink-0"
                                    alt="" />
                            </template>
                            <span x-text="option.label" class="truncate"></span>
                        </div>
                        <svg x-show="isSelected(option.value)" class="w-3.5 h-3.5 text-accent shrink-0"
                            viewBox="0 0 16 16" fill="none">
                            <path d="M3 8.5L6.5 12L13 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </li>
                </template>

                <li x-show="filtered.length === 0"
                    class="px-3 py-4 text-sm text-center text-zinc-400 dark:text-zinc-500 select-none">
                    No options found
                </li>
            </ul>
        </div>
        {{-- end inner visual wrapper --}}
    </div>
    {{-- end popover --}}
</div>

@if ($label)
    @if ($wireProp)
        <flux:error name="{{ $wireProp }}" />
    @endif
    </flux:field>
@endif
