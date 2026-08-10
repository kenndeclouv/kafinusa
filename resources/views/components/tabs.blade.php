{{-- x-tabs: Segmented Control with sliding pill indicator --}}
@props(['active' => null])

@once
    <style>
        .x-tabs-track {
            position: relative;
            display: inline-flex;
            align-items: center;
            padding: 3px;
            border-radius: var(--radius-xl);
            background: rgba(0, 0, 0, 0.07);
            border: 1px solid rgba(0, 0, 0, 0.09);
        }

        .dark .x-tabs-track {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(255, 255, 255, 0.09);
        }

        .x-tabs-pill {
            position: absolute;
            top: 3px;
            bottom: 3px;
            left: 0;
            border-radius: var(--radius-xl);
            pointer-events: none;
            z-index: 0;
            will-change: transform, width;
            transition: transform 0.32s cubic-bezier(0.25, 1.2, 0.5, 1),
                width 0.32s cubic-bezier(0.25, 1.2, 0.5, 1),
                opacity 0.12s ease;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: inset 0 1.5px 0 0 rgba(255, 255, 255, 1),
                inset 0 -1px 0 0 rgba(0, 0, 0, 0.04),
                0 2px 6px rgba(0, 0, 0, 0.14),
                0 1px 2px rgba(0, 0, 0, 0.09);
        }

        .x-tabs-pill::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: var(--radius-xl);
            border: 1px solid rgba(255, 255, 255, 0.5);
            pointer-events: none;
        }

        .dark .x-tabs-pill {
            background: rgba(80, 80, 90, 0.85);
            box-shadow: inset 0 1px 0 0 rgba(255, 255, 255, 0.12),
                inset 0 -1px 0 0 rgba(0, 0, 0, 0.2),
                0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .dark .x-tabs-pill::after {
            border-color: rgba(255, 255, 255, 0.12);
        }

        .x-tabs-btn {
            position: relative;
            z-index: 1;
            padding: 5px 16px;
            border-radius: var(--radius-xl);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            user-select: none;
            background: none;
            border: none;
            outline: none;
            white-space: nowrap;
            transition: color 0.18s ease;
            color: #52525b; /* zinc-600 */
        }
        
        .x-tabs-btn.x-tabs-btn--active {
            color: #09090b; /* zinc-950 */
        }

        .dark .x-tabs-btn {
            color: #a1a1aa; /* zinc-400 */
        }
        
        .dark .x-tabs-btn.x-tabs-btn--active {
            color: #ffffff;
        }
    </style>
@endonce

<div class="x-tabs-track" data-xtabs-active="{{ $active }}" {{ $attributes }} x-data="{
    active: @js((string) $active),
    pill: null,
    init() {
        this.pill = this.$refs.pill;

        // Sync when Livewire re-renders and changes data-xtabs-active
        new MutationObserver(() => {
            const next = this.$el.dataset.xtabsActive;
            if (next && next !== this.active) {
                this.active = next;
                this.sync();
            }
        }).observe(this.$el, { attributes: true, attributeFilter: ['data-xtabs-active'] });

        this.$nextTick(() => {
            this.sync();
            this.snapTo(this.active, true);
        });
    },
    sync() {
        this.$el.querySelectorAll('[data-tab]').forEach(b => {
            b.classList.toggle('x-tabs-btn--active', b.dataset.tab === this.active);
        });
        this.snapTo(this.active, false);
    },
    snapTo(val, instant) {
        const btn = this.$el.querySelector('[data-tab=' + JSON.stringify(val) + ']');
        if (!btn) return;
        if (instant) {
            this.pill.style.transition = 'none';
            this.place(btn);
            this.pill.style.opacity = '1';
            requestAnimationFrame(() => requestAnimationFrame(() => this.pill.style.transition = ''));
        } else {
            this.place(btn);
            this.pill.style.opacity = '1';
        }
    },
    place(el) {
        this.pill.style.transform = 'translateX(' + el.offsetLeft + 'px)';
        this.pill.style.width = el.offsetWidth + 'px';
    },
    select(val, el) {
        this.active = val;
        this.sync();
        this.$dispatch('tab-change', val);
    }
}"
    @mouseleave="snapTo(active, false)">
    <div class="x-tabs-pill" x-ref="pill" wire:ignore></div>
    {{ $slot }}
</div>
