@blaze(fold: true, safe: ['name'])

@props([
    'dismissible' => null,
    'position' => null,
    'closable' => null,
    'trigger' => null,
    'variant' => null,
    'scroll' => null,
    'flyout' => null,
    'name' => null,
])

@php
    // Blaze doesn't support View::share, this supplements it...
$__livewire = $__env->shared('__livewire');

if ($variant === 'flyout') {
    $flyout = true;
    $variant = null;
}

$closable ??= $variant === 'bare' ? false : true;
$overflow = $scroll === 'body' && !$flyout;

if ($flyout) {
    $classes = Flux::classes()
        ->add(
            match ($variant) {
                default => match ($position) {
                    // For bottom flyout we intentionally use 100% instead of 100vw because Firefox includes scrollbar gutter in vw...
                    'bottom'
                        => 'fixed m-0 p-8 min-w-[100%] overflow-y-auto mt-auto [--flux-flyout-translate:translateY(50px)] border-t',
                    'left'
                        => 'fixed m-0 p-8 max-h-dvh min-h-dvh md:[:where(&)]:min-w-[25rem] overflow-y-auto mr-auto [--flux-flyout-translate:translateX(-50px)] border-e rtl:mr-0 rtl:ml-auto rtl:[--flux-flyout-translate:translateX(50px)]',
                    default
                        => 'fixed m-0 p-8 max-h-dvh min-h-dvh md:[:where(&)]:min-w-[25rem] overflow-y-auto ml-auto [--flux-flyout-translate:translateX(50px)] border-s rtl:ml-0 rtl:mr-auto rtl:[--flux-flyout-translate:translateX(-50px)]',
                },
                'floating' => match ($position) {
                    // For bottom flyout we intentionally use 100% instead of 100vw because Firefox includes scrollbar gutter in vw...
                    'bottom'
                        => 'fixed m-2 p-8 min-w-[calc(100%-1rem)] overflow-y-auto mt-auto [--flux-flyout-translate:translateY(50px)]',
                    'left'
                        => 'fixed m-2 p-8 max-h-[calc(100dvh-1rem)] min-h-[calc(100dvh-1rem)] md:[:where(&)]:min-w-[25rem] overflow-y-auto mr-auto [--flux-flyout-translate:translateX(-50px)] rtl:mr-0 rtl:ml-auto rtl:[--flux-flyout-translate:translateX(50px)]',
                    default
                        => 'fixed m-2 p-8 max-h-[calc(100dvh-1rem)] min-h-[calc(100dvh-1rem)] md:[:where(&)]:min-w-[25rem] overflow-y-auto ml-auto [--flux-flyout-translate:translateX(50px)] rtl:ml-0 rtl:mr-auto rtl:[--flux-flyout-translate:translateX(-50px)]',
                },
                'bare' => '',
            },
        )
        ->add(
            match ($variant) {
                default => 'bg-white dark:bg-zinc-800 border-transparent dark:border-zinc-700',
                'floating' => 'bg-white dark:bg-zinc-800 ring ring-black/5 dark:ring-zinc-700 shadow-lg rounded-xl',
                'bare' => 'bg-transparent',
            },
        );
} elseif ($overflow) {
    $classes = Flux::classes();

    $contentClasses = Flux::classes()
        ->add('relative')
        ->add(
            match ($variant) {
                default
                    => 'max-md:pt-4 max-md:px-4 max-md:pb-6 md:p-6 md:[:where(&)]:w-[45vw] [:where(&)]:max-w-4xl [:where(&)]:min-w-[320px] shadow-lg max-md:!rounded-b-none max-md:!rounded-t-[2rem] md:rounded-xl max-md:!w-full max-md:!fixed max-md:!bottom-0 max-md:!inset-x-0 max-md:!m-0 max-md:!max-w-none',
                'bare' => '',
            },
        )
        ->add(
            match ($variant) {
                default
                    => 'bg-white dark:bg-zinc-800 ring ring-black/5 dark:ring-zinc-700 max-md:shadow-[0_-10px_40px_rgba(0,0,0,0.1)] dark:max-md:shadow-[0_-10px_40px_rgba(0,0,0,0.5)]',
                'bare' => 'bg-transparent',
            },
        );
} else {
    $classes = Flux::classes()
        ->add(
            match ($variant) {
                default
                    => 'max-md:pt-4 max-md:px-4 max-md:pb-6 md:p-6 md:[:where(&)]:w-[45vw] [:where(&)]:max-w-4xl [:where(&)]:min-w-[320px] shadow-lg max-md:!rounded-b-none max-md:!rounded-t-[2rem] md:rounded-xl max-md:!w-full max-md:!fixed max-md:!bottom-0 max-md:!inset-x-0 max-md:!m-0 max-md:!max-w-none',
                'bare' => '',
            },
        )
        ->add(
            match ($variant) {
                default
                    => 'bg-white dark:bg-zinc-800 ring ring-black/5 dark:ring-zinc-700 max-md:shadow-[0_-10px_40px_rgba(0,0,0,0.1)] dark:max-md:shadow-[0_-10px_40px_rgba(0,0,0,0.5)]',
                'bare' => 'bg-transparent',
            },
        );
}

// Support adding the .self modifier to the wire:model directive...
if (($wireModel = $attributes->wire('model')) && $wireModel->directive && !$wireModel->hasModifier('self')) {
    unset($attributes[$wireModel->directive]);

    $wireModel->directive .= '.self';

    $attributes = $attributes->merge([$wireModel->directive => $wireModel->value]);
}

if ($attributes['@close'] ?? null) {
    $attributes['wire:close'] = $attributes['@close'];

    unset($attributes['@close']);
}

if ($attributes['@cancel'] ?? null) {
    $attributes['wire:cancel'] = $attributes['@cancel'];

    unset($attributes['@cancel']);
}

if ($dismissible === false) {
    $attributes = $attributes->merge(['disable-click-outside' => '']);
}

[$contentAttributes, $attributes] = Flux::splitAttributes($attributes, ['autofocus', 'class', 'style']);
[$dialogAttributes, $attributes] = Flux::splitAttributes($attributes, [
    'wire:close',
    'x-on:close',
    'wire:cancel',
    'x-on:cancel',
    ]);

    if (!$overflow) {
        $dialogAttributes = $dialogAttributes->merge($contentAttributes->getAttributes());
    }
@endphp

<ui-modal {{ $attributes }} data-flux-modal x-data="{
    startY: 0,
    currentY: 0,
    dragging: false,
    expanded: false,
    dragOffset: 0,
    threshold: 100,
    get transformStyle() {
        if (!this.dragging) return '';
        let y = this.dragOffset;
        if (this.expanded && y < 0) y = 0; // Prevent dragging up beyond expanded state
        return `transform: translateY(${y}px) !important; transition: none !important;`;
    },
    startDrag(e) {
        if (window.innerWidth >= 768) return;
        this.startY = e.touches ? e.touches[0].clientY : e.clientY;
        this.dragging = true;
        this.dragOffset = 0;
    },
    onDrag(e) {
        if (!this.dragging) return;
        e.preventDefault(); // Prevent pull-to-refresh or scrolling
        this.currentY = e.touches ? e.touches[0].clientY : e.clientY;
        this.dragOffset = this.currentY - this.startY;
    },
    endDrag(e) {
        if (!this.dragging) return;
        this.dragging = false;

        if (this.expanded) {
            if (this.dragOffset > this.threshold * 2) {
                this.closeModal();
            } else if (this.dragOffset > this.threshold) {
                this.expanded = false;
            }
        } else {
            if (this.dragOffset > this.threshold) {
                this.closeModal();
            } else if (this.dragOffset < -this.threshold) {
                this.expanded = true;
            }
        }
        this.dragOffset = 0;
    },
    closeModal() {
        this.expanded = false;
        let modalName = this.$el.querySelector('dialog').getAttribute('data-modal');
        if (modalName) {
            $dispatch('modal-close', { name: modalName });
        } else {
            this.$el.querySelector('dialog').close();
        }
    }
}" @mousemove.window="onDrag" @mouseup.window="endDrag"
    @touchmove.window="onDrag" @touchend.window="endDrag">
    <style>
        @media (max-width: 768px) {

            /* Target dialog element for the slide animation */
            ui-modal[data-flux-modal]>dialog {
                transition: transform 0.5s cubic-bezier(0.2, 0.8, 0.2, 1), height 0.5s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.5s cubic-bezier(0.2, 0.8, 0.2, 1), display 0.5s allow-discrete, overlay 0.5s allow-discrete !important;
                animation: none !important;
            }

            ui-modal[data-flux-modal]>dialog[open] {
                transform: translateY(0) scale(1) !important;
                opacity: 1 !important;
            }

            /* Override Flux's JS closing states and native close */
            ui-modal[data-flux-modal]>dialog:not([open]),
            ui-modal[data-flux-modal]>dialog[data-closing],
            ui-modal[data-flux-modal]>dialog[closing],
            ui-modal[data-flux-modal]>dialog.closing {
                transform: translateY(100%) scale(1) !important;
                opacity: 0 !important;
            }

            @starting-style {
                ui-modal[data-flux-modal]>dialog[open] {
                    transform: translateY(100%) scale(1) !important;
                    opacity: 0 !important;
                }
            }

            /* Disable internal Flux animations to prevent conflicting fade/scale */
            ui-modal[data-flux-modal] [data-flux-modal-content] {
                transition: none !important;
                transform: none !important;
                animation: none !important;
            }

            /* Enforce Bottom Sheet Anchoring & Shape (Overrides any user classes) */
            ui-modal[data-flux-modal] .flex.min-h-full {
                padding: 0 !important;
                align-items: flex-end !important;
            }
            ui-modal[data-flux-modal] > dialog,
            ui-modal[data-flux-modal] [data-flux-modal-content] {
                margin-top: auto !important;
                margin-bottom: 0 !important;
                border-bottom-left-radius: 0 !important;
                border-bottom-right-radius: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            /* Expanded state styles */
            ui-modal[data-flux-modal]>dialog.is-expanded {
                margin-top: 0.5rem !important;
                height: calc(100dvh - 0.5rem) !important;
                max-height: none !important;
            }

            ui-modal[data-flux-modal]>dialog.is-expanded .flex.items-end {
                align-items: flex-start !important;
                padding-top: 0.5rem !important;
            }

            ui-modal[data-flux-modal]>dialog.is-expanded [data-flux-modal-content] {
                height: calc(100dvh - 0.5rem) !important;
                max-height: none !important;
                margin-top: 0 !important;
            }
        }
    </style>
    <?php if ($trigger): ?>
    {{ $trigger }}
    <?php endif; ?>

    <dialog wire:ignore.self {{-- This needs to be here because the dialog element adds a "close" attribute that isn't durable... --}} {{ $dialogAttributes->class($classes) }}
        :class="{ 'is-expanded': expanded }" :style="transformStyle" <?php if ($name): ?> data-modal="{{ $name }}"
        <?php endif; ?> <?php if ($flyout): ?> data-flux-flyout <?php endif; ?> <?php if ($overflow): ?>
        data-flux-modal-overflow <?php endif; ?> @unblaze(scope: ['name' => $name]) x-data="fluxModal(@js($scope['name']), @js(isset($__livewire) ? $__livewire->getId() : null))"
        @endunblaze x-on:modal-show.document="handleShow($event)"
        x-on:modal-close.document="handleClose($event)">
        <?php if ($overflow): ?>
        <div class="flex min-h-full items-end md:items-center justify-center max-md:!p-0 md:p-4 sm:p-6">
            <div {{ $contentAttributes->class($contentClasses) }} data-flux-modal-content>
                <!-- Grab Handle (Mobile Only) -->
                <?php if ($variant !== 'bare'): ?>
                <div @mousedown="startDrag" @touchstart="startDrag"
                    class="w-12 h-1.5 bg-zinc-300 dark:bg-zinc-600 rounded-full mx-auto mb-4 md:hidden shrink-0 cursor-grab active:cursor-grabbing">
                </div>
                <?php endif; ?>

                {{ $slot }}

                <?php if ($closable): ?>
                <div class="absolute top-0 end-0 mt-4 me-4">
                    <flux:modal.close>
                        <flux:button variant="ghost" icon="x-mark" size="sm" aria-label="{{ __('Close modal') }}"
                            class="text-zinc-400! hover:text-zinc-800! dark:text-zinc-500! dark:hover:text-white!">
                        </flux:button>
                    </flux:modal.close>
                </div>
                <?php endif; ?>
                
                <!-- Anti-float Gap Filler -->
                <div class="absolute top-[98%] inset-x-0 h-[100dvh] bg-white dark:bg-zinc-800 md:hidden border-none pointer-events-none"></div>
            </div>
        </div>
        <?php else: ?>
        <!-- Grab Handle (Mobile Only) -->
        <?php if ($variant !== 'bare'): ?>
        <div @mousedown="startDrag" @touchstart="startDrag"
            class="w-12 h-1.5 bg-zinc-300 dark:bg-zinc-700 rounded-full mx-auto mb-5 md:hidden shrink-0 cursor-grab active:cursor-grabbing">
        </div>
        <?php endif; ?>

        {{ $slot }}

        <?php if ($closable): ?>
        <div class="absolute top-0 end-0 mt-4 me-4">
            <flux:modal.close>
                <flux:button variant="ghost" icon="x-mark" size="sm" aria-label="{{ __('Close modal') }}"
                    class="text-zinc-400! hover:text-zinc-800! dark:text-zinc-500! dark:hover:text-white!">
                </flux:button>
            </flux:modal.close>
        </div>
        <?php endif; ?>
        
        <!-- Anti-float Gap Filler -->
        <div class="absolute top-[98%] inset-x-0 h-[100dvh] bg-white dark:bg-zinc-800 md:hidden border-none pointer-events-none"></div>
        <?php endif; ?>
    </dialog>
</ui-modal>
