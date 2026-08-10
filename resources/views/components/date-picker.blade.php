{{--
    <x-date-picker type="date"           wire:model="..." />
    <x-date-picker type="datetime-local" wire:model="..." />
    <x-date-picker type="time"           wire:model="..." />
    <x-date-picker type="week"           wire:model="..." />
    <x-date-picker type="month"          wire:model="..." />

    Props:
      type        → date | datetime-local | time | week | month  (default: date)
      placeholder → override default placeholder string
      clearable   → bool (default: true)
      min         → ISO min string matching the type, e.g. '2024-01-01'
      max         → ISO max string
      step        → seconds for time step (e.g. 60 = 1-min steps, 1 = show seconds)
--}}
@props([
    'type' => 'date',
    'placeholder' => null,
    'clearable' => true,
    'min' => null,
    'max' => null,
    'step' => 60,
    'variant' => 'default',
    'size' => 'md',
])

@php
    $modelAttr = $attributes->wire('model');
    $wireProp = $modelAttr ? $modelAttr->value() : null;

    $defaults = [
        'date' => 'Pick a date',
        'datetime-local' => 'Pick date & time',
        'time' => 'Pick a time',
        'week' => 'Pick a week',
        'month' => 'Pick a month',
    ];
    $ph = $placeholder ?? ($defaults[$type] ?? 'Pick a value');
    $showSec = (int) $step < 60;
    $stepMin = max(1, (int) floor((int) $step / 60));

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

    $triggerClasses = $variant === 'ios'
        ? 'group ' . $triggerHeight . ' w-full max-w-full ms-auto border border-zinc-200/50 dark:border-white/5 shadow-xs rounded-lg flex items-stretch p-0 overflow-hidden bg-zinc-100 dark:bg-white/10 hover:bg-zinc-200 dark:hover:bg-white/20 appearance-none text-[14px] text-zinc-700 dark:text-zinc-300 focus:outline-none cursor-pointer'
        : 'group w-full ' . $triggerHeight . ' border rounded-xl flex items-stretch p-0 overflow-hidden disabled:shadow-none dark:shadow-none appearance-none leading-[1.375rem] bg-white dark:bg-white/10 dark:disabled:bg-white/7 text-zinc-700 disabled:text-zinc-500 dark:text-zinc-300 dark:disabled:text-zinc-400 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200 dark:border-white/10 dark:disabled:border-white/5 focus:outline-none focus:ring-2 focus:ring-accent/50 cursor-pointer';

    $textAlignment = 'text-left';
    $iconWrapperClasses = $variant === 'ios'
        ? 'flex items-center justify-center shrink-0 w-6 h-7 mr-1 my-auto rounded-md bg-transparent '
        : 'flex items-center justify-center shrink-0 w-10 h-7 mr-1.5 my-auto rounded-md bg-zinc-100 dark:bg-white/10 group-hover:bg-zinc-200 dark:group-hover:bg-white/20 ';
@endphp

@once
    <style>
        .dp-dropdown-popover {
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

        .dp-dropdown-popover:popover-open {
            display: block;
        }
    </style>
@endonce

<div x-data="{
    type: '{{ addslashes($type) }}',
    open: false,
    panel: 'main', // 'main' | 'year'
    openUpward: false,

    // ── Reactive value (entangled or local) ─────────────────────────────────
    @if ($wireProp) value: $wire.entangle('{{ $wireProp }}').live,
    @else
    value: '', @endif

    // ── Calendar view ───────────────────────────────────────────────────────
    viewYear: new Date().getFullYear(),
    viewMonth: new Date().getMonth(),
    yearPageStart: new Date().getFullYear() - 4,

    // ── Selection state ─────────────────────────────────────────────────────
    selDate: null,
    selH: 0,
    selM: 0,
    selS: 0,

    showSec: {{ $showSec ? 'true' : 'false' }},
    stepMin: {{ $stepMin }},

    monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
    monthShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    dayNames: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],

    // ── Formatting helpers ──────────────────────────────────────────────────

    pad(n, l = 2) { return String(n).padStart(l, '0'); },

    _timeFmt(h, m, s = 0) {
        const suf = h >= 12 ? 'PM' : 'AM';
        const h12 = h % 12 || 12;
        let str = `${this.pad(h12)}:${this.pad(m)}`;
        if (this.showSec) str += `:${this.pad(s)}`;
        return `${str} ${suf}`;
    },

    get label() {
        const v = this.value;
        if (!v) return null;
        if (this.type === 'date') {
            const d = this._isoDate(v);
            return d ? `${this.monthShort[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}` : null;
        }
        if (this.type === 'month') {
            const [y, m] = v.split('-').map(Number);
            return `${this.monthNames[m - 1]} ${y}`;
        }
        if (this.type === 'week') {
            const m = v.match(/^(\d{4})-W(\d+)$/);
            return m ? `Week ${parseInt(m[2])}, ${m[1]}` : null;
        }
        if (this.type === 'time') {
            const [h, mi, s] = v.split(':').map(Number);
            return this._timeFmt(h || 0, mi || 0, s || 0);
        }
        if (this.type === 'datetime-local') {
            const [dp, tp] = v.split('T');
            const d = this._isoDate(dp);
            if (!d || !tp) return null;
            const [h, mi, s] = tp.split(':').map(Number);
            return `${this.monthShort[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}, ${this._timeFmt(h||0, mi||0, s||0)}`;
        }
        return v;
    },

    get isTimeType() { return this.type === 'time'; },
    get isMonthType() { return this.type === 'month'; },
    get isCalType() { return ['date', 'week', 'datetime-local'].includes(this.type); },

    // ── Date helpers ────────────────────────────────────────────────────────

    _isoDate(str) {
        if (!str) return null;
        const p = str.split('-').map(Number);
        return isNaN(p[0]) ? null : new Date(p[0], p[1] - 1, p[2]);
    },

    _dateIso(d) {
        return `${d.getFullYear()}-${this.pad(d.getMonth()+1)}-${this.pad(d.getDate())}`;
    },

    // ISO week: returns { year, week }
    _isoWeek(date) {
        const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
        const day = d.getUTCDay() || 7;
        d.setUTCDate(d.getUTCDate() + 4 - day);
        const ys = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        return { year: d.getUTCFullYear(), week: Math.ceil(((d - ys) / 86400000 + 1) / 7) };
    },

    // Monday of an ISO week (YYYY-Www)
    _weekToDate(str) {
        const m = str.match(/^(\d{4})-W(\d+)$/);
        if (!m) return null;
        const [y, w] = [parseInt(m[1]), parseInt(m[2])];
        const jan4 = new Date(y, 0, 4);
        const dow = jan4.getDay() || 7;
        const mon = new Date(jan4);
        mon.setDate(jan4.getDate() - dow + 1 + (w - 1) * 7);
        return mon;
    },

    // ── Calendar cell logic ─────────────────────────────────────────────────

    get cells() {
        const firstDay = new Date(this.viewYear, this.viewMonth, 1).getDay();
        const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
        const daysInPrev = new Date(this.viewYear, this.viewMonth, 0).getDate();
        const cells = [];
        for (let i = firstDay - 1; i >= 0; i--)
            cells.push({
                day: daysInPrev - i,
                month: this.viewMonth - 1,
                year: this.viewMonth === 0 ? this.viewYear - 1 : this.viewYear,
                outside: true
            });
        for (let d = 1; d <= daysInMonth; d++)
            cells.push({ day: d, month: this.viewMonth, year: this.viewYear, outside: false });
        const fill = 42 - cells.length;
        for (let d = 1; d <= fill; d++)
            cells.push({
                day: d,
                month: this.viewMonth + 1,
                year: this.viewMonth === 11 ? this.viewYear + 1 : this.viewYear,
                outside: true
            });
        return cells;
    },

    // 6 rows of 7 — used for week type
    get rows() {
        return [0, 1, 2, 3, 4, 5].map(i => this.cells.slice(i * 7, i * 7 + 7));
    },

    cellDate(c) { return new Date(c.year, c.month, c.day); },

    isToday(c) {
        const t = new Date();
        return c.day === t.getDate() && c.month === t.getMonth() && c.year === t.getFullYear();
    },

    isCellSelected(c) {
        if (!this.selDate) return false;
        if (this.type === 'week') {
            const cw = this._isoWeek(this.cellDate(c));
            const sw = this._isoWeek(this.selDate);
            return cw.year === sw.year && cw.week === sw.week;
        }
        return c.day === this.selDate.getDate() && c.month === this.selDate.getMonth() && c.year === this.selDate.getFullYear();
    },

    // ── Month grid (for type=month) ─────────────────────────────────────────

    get yearPages() {
        const y = [];
        for (let i = this.yearPageStart; i < this.yearPageStart + 12; i++) y.push(i);
        return y;
    },

    // ── Navigation ──────────────────────────────────────────────────────────

    prevMonth() {
        if (this.viewMonth === 0) {
            this.viewMonth = 11;
            this.viewYear--;
        } else this.viewMonth--;
    },
    nextMonth() {
        if (this.viewMonth === 11) {
            this.viewMonth = 0;
            this.viewYear++;
        } else this.viewMonth++;
    },
    prevYearPage() { this.yearPageStart -= 12; },
    nextYearPage() { this.yearPageStart += 12; },

    // ── Time spinners ───────────────────────────────────────────────────────

    adjH(d) {
        this.selH = (this.selH + d + 24) % 24;
        this._pushTime();
    },
    adjM(d) {
        this.selM = (this.selM + d * this.stepMin + 60) % 60;
        this._pushTime();
    },
    adjS(d) {
        this.selS = (this.selS + d + 60) % 60;
        this._pushTime();
    },
    toggleAP() {
        this.selH = (this.selH + 12) % 24;
        this._pushTime();
    },

    get dH() { return this.pad(this.selH % 12 || 12); },
    get dM() { return this.pad(this.selM); },
    get dS() { return this.pad(this.selS); },
    get isAM() { return this.selH < 12; },

    _pushTime() {
        const t = `${this.pad(this.selH)}:${this.pad(this.selM)}${this.showSec ? ':'+this.pad(this.selS) : ''}`;
        if (this.type === 'time') {
            this.value = t;
        } else if (this.type === 'datetime-local' && this.selDate) {
            this.value = `${this._dateIso(this.selDate)}T${t}`;
        }
    },

    // ── Selection actions ───────────────────────────────────────────────────

    selectCell(c) {
        const d = this.cellDate(c);
        this.selDate = d;
        this.viewYear = d.getFullYear();
        this.viewMonth = d.getMonth();
        if (this.type === 'date') {
            this.value = this._dateIso(d);
            this.closeDropdown();
        } else if (this.type === 'week') {
            const { year, week } = this._isoWeek(d);
            this.value = `${year}-W${this.pad(week)}`;
            this.closeDropdown();
        } else if (this.type === 'datetime-local') {
            // Switch to inline time panel
            this.panel = 'time';
        }
    },

    selectRow(row) {
        // Week type: click any row to select that week
        this.selectCell(row[1] ?? row[0]);
    },

    selectMonthItem(m) {
        // type=month: m is 0-indexed
        this.selDate = new Date(this.viewYear, m, 1);
        this.value = `${this.viewYear}-${this.pad(m + 1)}`;
        this.closeDropdown();
    },

    selectYear(y) {
        this.viewYear = y;
        if (this.type === 'month') {
            this.panel = 'main'; // go back to month grid
        } else {
            this.panel = 'main';
        }
    },

    applyTime() {
        this._pushTime();
        this.closeDropdown();
    },

    goToday() {
        const t = new Date();
        this.selDate = t;
        this.viewYear = t.getFullYear();
        this.viewMonth = t.getMonth();
        if (this.type === 'date') {
            this.value = this._dateIso(t);
            this.closeDropdown();
        }
        if (this.type === 'week') {
            const { year, week } = this._isoWeek(t);
            this.value = `${year}-W${this.pad(week)}`;
            this.closeDropdown();
        }
        if (this.type === 'datetime-local') this.panel = 'time';
    },

    nowTime() {
        const t = new Date();
        this.selH = t.getHours();
        this.selM = t.getMinutes();
        this.selS = t.getSeconds();
        this._pushTime();
        if (this.type === 'time') this.closeDropdown();
    },

    clear() {
        this.value = '';
        this.selDate = null;
        this.selH = 0;
        this.selM = 0;
        this.selS = 0;
        this.closeDropdown();
    },

    _popoverId: null,

    getPopoverEl() {
        return this._popoverId ? document.getElementById(this._popoverId) : null;
    },

    positionPopover() {
        const trigger = this.$refs.trigger;
        const popover = this.getPopoverEl();
        if (!trigger || !popover) return;

        const rect = trigger.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom;
        const spaceAbove = rect.top;
        const dropdownH = 350; // approximate height
        const goUp = spaceBelow < dropdownH && spaceAbove > spaceBelow;

        popover.style.top = (goUp ? rect.top - dropdownH - 4 : rect.bottom + 4) + 'px';

        const dropdownW = this.type === 'week' ? 320 : 288;
        let leftPos = rect.left;
        if (leftPos + dropdownW > window.innerWidth) {
            leftPos = window.innerWidth - dropdownW - 16;
        }
        popover.style.left = Math.max(16, leftPos) + 'px';
    },

    openDropdown() {
        const popover = this.getPopoverEl();
        if (!popover) return;
        this.positionPopover();
        popover.showPopover();
        this.panel = 'main';
        this.open = true;
    },

    closeDropdown() {
        const popover = this.getPopoverEl();
        if (popover) {
            try { popover.hidePopover(); } catch (e) {}
        }
        this.open = false;
    },

    toggle() {
        this.open ? this.closeDropdown() : this.openDropdown();
    },

    // ── Init / value sync ───────────────────────────────────────────────────

    init() {
        const sync = (v) => {
            if (!v) { this.selDate = null; return; }
            if (this.type === 'date') {
                const d = this._isoDate(v);
                if (d) {
                    this.selDate = d;
                    this.viewYear = d.getFullYear();
                    this.viewMonth = d.getMonth();
                }
            } else if (this.type === 'month') {
                const [y, m] = v.split('-').map(Number);
                this.selDate = new Date(y, m - 1, 1);
                this.viewYear = y;
            } else if (this.type === 'week') {
                const d = this._weekToDate(v);
                if (d) {
                    this.selDate = d;
                    this.viewYear = d.getFullYear();
                    this.viewMonth = d.getMonth();
                }
            } else if (this.type === 'time') {
                const [h, mi, s] = v.split(':').map(Number);
                this.selH = h || 0;
                this.selM = mi || 0;
                this.selS = s || 0;
            } else if (this.type === 'datetime-local') {
                const [dp, tp] = v.split('T');
                const d = this._isoDate(dp);
                if (d) {
                    this.selDate = d;
                    this.viewYear = d.getFullYear();
                    this.viewMonth = d.getMonth();
                }
                if (tp) {
                    const [h, mi, s] = tp.split(':').map(Number);
                    this.selH = h || 0;
                    this.selM = mi || 0;
                    this.selS = s || 0;
                }
            }
        };
        sync(this.value);
        this.$watch('value', sync);

        const onScrollOrResize = () => {
            if (this.open) {
                this.positionPopover();
            }
        };
        window.addEventListener('scroll', onScrollOrResize, true);
        window.addEventListener('resize', onScrollOrResize);

        this.$cleanup = () => {
            window.removeEventListener('scroll', onScrollOrResize, true);
            window.removeEventListener('resize', onScrollOrResize);
            const p = this.getPopoverEl();
            if (p) p.remove();
        };
    }
}" @keydown.escape.window="closeDropdown()"
    @click.outside="closeDropdown()"
    {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => 'relative']) }} x-modelable="value">

    <button type="button" x-ref="trigger" @click="toggle()" :aria-expanded="open"
        class="{{ $triggerClasses }}">

        <div class="flex-1 min-w-0 flex items-center {{ $sizeClasses }} {{ $textAlignment }}">
            {{-- Label --}}
            <span class="flex-1 text-left truncate"
                :class="label ? 'text-zinc-800 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-500'"
                x-text="label || '{{ $ph }}'">
            </span>
        </div>

        <div class="{{ $iconWrapperClasses }}">
            {{-- Chevron --}}
            <svg class="w-4 h-4 text-zinc-400 shrink-0 transition-transform duration-150"
                :class="open ? 'rotate-180' : ''" viewBox="0 0 16 16" fill="none">
                <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </div>
    </button>

    <div wire:ignore.self x-ref="popoverEl" x-init="_popoverId = $el.id = 'dp-popover-' + Math.random().toString(36).slice(2)"
        @toggle="if ($event.newState === 'closed') { open = false; panel = 'main'; }" @click.stop popover="manual"
        class="dp-dropdown-popover" @keydown.escape.window="closeDropdown()">
        <div x-show="open" :class="type === 'week' ? 'w-80' : 'w-72'"
            x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="rounded-lg border border-zinc-200 dark:border-white/10
                   bg-white/40 dark:bg-zinc-900/60 backdrop-blur-md shadow-xl overflow-hidden"
            style="display:none;">

            <div class="bg-white dark:bg-white/10 w-full h-full">
            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- YEAR PICKER PANEL (shared by all calendar-based types)          --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="panel === 'year'">
                <div class="flex items-center gap-2 px-4 pt-4 pb-3">
                    <button type="button" @click="prevYearPage()"
                        class="w-7 h-7 flex items-center justify-center rounded-xl text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-accent/50 transition cursor-pointer shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                            <path d="M8 2L4 6L8 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                    <button type="button" @click="panel = 'main'"
                        class="flex-1 flex items-center justify-center gap-1.5 text-sm font-semibold text-zinc-800 dark:text-zinc-100 tracking-wide rounded-xl px-2 py-1 hover:bg-zinc-100 dark:hover:bg-white/10 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0 rotate-180" viewBox="0 0 12 12" fill="none">
                            <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        <span x-text="yearPageStart + ' – ' + (yearPageStart + 11)"></span>
                    </button>
                    <button type="button" @click="nextYearPage()"
                        class="w-7 h-7 flex items-center justify-center rounded-xl text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-accent/50 transition cursor-pointer shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                            <path d="M4 2L8 6L4 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
                <div class="border-t border-zinc-100 dark:border-white/5 mx-3"></div>
                <div class="grid grid-cols-3 gap-1.5 p-3">
                    <template x-for="y in yearPages" :key="y">
                        <button type="button" @click.prevent="selectYear(y)"
                            class="py-2 rounded-xl text-sm font-medium transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-accent/50"
                            :class="{
                                'bg-accent text-white shadow-sm': y === viewYear,
                                'text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-white/10': y !==
                                    viewYear,
                                'ring-1 ring-accent/50 font-semibold': y === new Date().getFullYear() && y !== viewYear,
                            }"
                            x-text="y">
                        </button>
                    </template>
                </div>
                <div class="border-t border-zinc-100 dark:border-white/5 px-4 py-2.5 flex justify-end">
                    <button type="button" @click="panel = 'main'"
                        class="text-sm text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 transition cursor-pointer">Cancel</button>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- MAIN PANEL                                                      --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}

            <div x-show="panel === 'main'">

                {{-- ── type=time: time picker only ────────────────────────────── --}}
                <template x-if="isTimeType">
                    <div>
                        <div
                            class="px-4 pt-4 pb-3 text-sm font-semibold text-zinc-500 dark:text-zinc-400 tracking-wide uppercase text-xs">
                            Time</div>
                        <div class="border-t border-zinc-100 dark:border-white/5 mx-3"></div>
                        <div class="px-4 py-4">
                            @include('components._date-picker-timespinner')
                        </div>
                        <div
                            class="border-t border-zinc-100 dark:border-white/5 px-4 py-2.5 flex justify-between items-center">
                            <button type="button" @click="nowTime()"
                                class="text-sm font-medium text-accent hover:text-accent/80 transition cursor-pointer">Now</button>
                            @if ($clearable)
                                <button type="button" x-show="value" @click="clear()"
                                    class="text-sm text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 transition cursor-pointer">Clear</button>
                            @endif
                        </div>
                    </div>
                </template>

                {{-- ── type=month: month grid ──────────────────────────────────── --}}
                <template x-if="isMonthType">
                    <div>
                        {{-- Header --}}
                        <div class="flex items-center gap-2 px-4 pt-4 pb-3">
                            <button type="button" @click="viewYear--"
                                class="w-7 h-7 flex items-center justify-center rounded-xl text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-accent/50 transition cursor-pointer shrink-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                                    <path d="M8 2L4 6L8 10" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <button type="button" @click="panel = 'year'; yearPageStart = viewYear - 4"
                                class="flex-1 flex items-center justify-center gap-1.5 text-sm font-semibold text-zinc-800 dark:text-zinc-100 tracking-wide rounded-xl px-2 py-1 hover:bg-zinc-100 dark:hover:bg-white/10 transition cursor-pointer">
                                <span x-text="viewYear"></span>
                                <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0" viewBox="0 0 12 12" fill="none">
                                    <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <button type="button" @click="viewYear++"
                                class="w-7 h-7 flex items-center justify-center rounded-xl text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-accent/50 transition cursor-pointer shrink-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                                    <path d="M4 2L8 6L4 10" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="border-t border-zinc-100 dark:border-white/5 mx-3"></div>
                        {{-- Month grid 3×4 --}}
                        <div class="grid grid-cols-3 gap-1.5 p-3">
                            <template x-for="(mn, mi) in monthNames" :key="mi">
                                <button type="button" @click.prevent="selectMonthItem(mi)"
                                    class="py-2 rounded-xl text-sm font-medium transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-accent/50"
                                    :class="{
                                        'bg-accent text-white shadow-sm': selDate && mi === selDate.getMonth() &&
                                            viewYear === selDate.getFullYear(),
                                        'text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-white/10': !(
                                            selDate && mi === selDate.getMonth() && viewYear === selDate
                                            .getFullYear()),
                                        'ring-1 ring-accent/50 font-semibold': mi === new Date().getMonth() &&
                                            viewYear ===
                                            new Date().getFullYear() && !(selDate && mi === selDate.getMonth() &&
                                                viewYear === selDate.getFullYear()),
                                    }"
                                    x-text="monthShort[mi]">
                                </button>
                            </template>
                        </div>
                        <div
                            class="border-t border-zinc-100 dark:border-white/5 px-4 py-2.5 flex justify-between items-center">
                            <button type="button"
                                @click="() => { const t=new Date(); viewYear=t.getFullYear(); selectMonthItem(t.getMonth()); }"
                                class="text-sm font-medium text-accent hover:text-accent/80 transition cursor-pointer">This
                                month</button>
                            @if ($clearable)
                                <button type="button" x-show="value" @click="clear()"
                                    class="text-sm text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 transition cursor-pointer">Clear</button>
                            @endif
                        </div>
                    </div>
                </template>

                {{-- ── type=date|week|datetime-local: calendar ────────────────── --}}
                <template x-if="isCalType">
                    <div>
                        {{-- Header --}}
                        <div class="flex items-center gap-2 px-4 pt-4 pb-3">
                            <button type="button" @click="prevMonth()"
                                class="w-7 h-7 flex items-center justify-center rounded-xl text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-accent/50 transition cursor-pointer shrink-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                                    <path d="M8 2L4 6L8 10" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <button type="button" @click="panel = 'year'; yearPageStart = viewYear - 4"
                                class="flex-1 flex items-center justify-center gap-1.5 text-sm font-semibold text-zinc-800 dark:text-zinc-100 tracking-wide rounded-xl px-2 py-1 hover:bg-zinc-100 dark:hover:bg-white/10 transition cursor-pointer">
                                <span x-text="monthNames[viewMonth] + ' ' + viewYear"></span>
                                <svg class="w-3.5 h-3.5 text-zinc-400 shrink-0" viewBox="0 0 12 12" fill="none">
                                    <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <button type="button" @click="nextMonth()"
                                class="w-7 h-7 flex items-center justify-center rounded-xl text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-accent/50 transition cursor-pointer shrink-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                                    <path d="M4 2L8 6L4 10" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="border-t border-zinc-100 dark:border-white/5 mx-3"></div>

                        {{-- ── date / datetime-local: flat day grid ──────────── --}}
                        <template x-if="type !== 'week'">
                            <div>
                                <div class="grid grid-cols-7 px-3 pt-3">
                                    <template x-for="n in dayNames" :key="n">
                                        <div class="text-center text-xs font-semibold tracking-widest uppercase text-zinc-400 dark:text-zinc-500 pb-2"
                                            x-text="n"></div>
                                    </template>
                                </div>
                                <div class="grid grid-cols-7 px-3 pb-4">
                                    <template x-for="(cell, idx) in cells" :key="idx">
                                        <div class="flex items-center justify-center p-0.5">
                                            <button type="button" @click.prevent="selectCell(cell)"
                                                class="relative w-8 h-8 flex flex-col items-center justify-center rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50 transition-colors duration-100 cursor-pointer"
                                                :class="{
                                                    'text-zinc-400 dark:text-zinc-500': cell.outside && !isCellSelected(
                                                        cell),
                                                    'text-zinc-700 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-white/10':
                                                        !cell.outside && !isCellSelected(cell),
                                                    'hover:bg-zinc-50 dark:hover:bg-white/5': cell.outside && !
                                                        isCellSelected(cell),
                                                    'bg-accent text-white font-semibold shadow-sm': isCellSelected(
                                                        cell),
                                                    'font-semibold': isToday(cell) && !isCellSelected(cell),
                                                }">
                                                <span x-text="cell.day"></span>
                                                <span x-show="isToday(cell) && !isCellSelected(cell)"
                                                    class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-accent pointer-events-none"></span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- ── week: row-based grid with Wk numbers ──────────── --}}
                        <template x-if="type === 'week'">
                            <div class="px-3 pt-3 pb-4">
                                {{-- Header row --}}
                                <div class="grid grid-cols-8 mb-1">
                                    <div
                                        class="text-center text-xs font-semibold tracking-widest uppercase text-zinc-400 dark:text-zinc-500 pb-2">
                                        Wk</div>
                                    <template x-for="n in dayNames" :key="n">
                                        <div class="text-center text-xs font-semibold tracking-widest uppercase text-zinc-400 dark:text-zinc-500 pb-2"
                                            x-text="n"></div>
                                    </template>
                                </div>
                                {{-- Data rows --}}
                                <template x-for="(row, ri) in rows" :key="ri">
                                    <div class="grid grid-cols-8 mb-0.5 rounded-xl cursor-pointer group"
                                        @click.prevent="selectRow(row)"
                                        :class="row.some(c => isCellSelected(c)) ? 'bg-accent/10 dark:bg-accent/15' :
                                            'hover:bg-zinc-50 dark:hover:bg-white/5'">
                                        {{-- Week number --}}
                                        <div class="flex items-center justify-center">
                                            <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-500"
                                                x-text="'W' + _isoWeek(cellDate(row[3])).week"></span>
                                        </div>
                                        {{-- 7 day cells --}}
                                        <template x-for="(cell, ci) in row" :key="ci">
                                            <div class="flex items-center justify-center p-0.5">
                                                <div class="relative w-8 h-8 flex flex-col items-center justify-center rounded-xl text-sm transition-colors duration-100"
                                                    :class="{
                                                        'text-zinc-400 dark:text-zinc-500': cell.outside,
                                                        'text-zinc-700 dark:text-zinc-200': !cell.outside && !
                                                            isCellSelected(cell),
                                                        'text-accent font-bold': isCellSelected(cell),
                                                        'font-semibold': isToday(cell),
                                                    }">
                                                    <span x-text="cell.day"></span>
                                                    <span x-show="isToday(cell) && !isCellSelected(cell)"
                                                        class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-accent pointer-events-none"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Footer --}}
                        <div
                            class="border-t border-zinc-100 dark:border-white/5 px-4 py-2.5 flex justify-between items-center">
                            <button type="button" @click="goToday()"
                                class="text-sm font-medium text-accent hover:text-accent/80 transition cursor-pointer">Today</button>
                            <div class="flex items-center gap-3">
                                <template x-if="type === 'datetime-local' && selDate">
                                    <button type="button" @click="panel = 'time'"
                                        class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition cursor-pointer font-medium">Set
                                        time →</button>
                                </template>
                                @if ($clearable)
                                    <button type="button" x-show="value" @click="clear()"
                                        class="text-sm text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 transition cursor-pointer">Clear</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- TIME PANEL — datetime-local step 2 after date selected          --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            <div x-show="panel === 'time' && type === 'datetime-local'">
                {{-- Header --}}
                <div class="flex items-center gap-2 px-4 pt-4 pb-3">
                    <button type="button" @click="panel = 'main'"
                        class="w-7 h-7 flex items-center justify-center rounded-xl text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-accent/50 transition cursor-pointer shrink-0">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                            <path d="M8 2L4 6L8 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                    <span
                        class="flex-1 text-center text-sm font-semibold text-zinc-800 dark:text-zinc-100 tracking-wide"
                        x-text="selDate ? monthShort[selDate.getMonth()] + ' ' + selDate.getDate() + ', ' + selDate.getFullYear() : 'Set time'">
                    </span>
                    <div class="w-7"></div>
                </div>
                <div class="border-t border-zinc-100 dark:border-white/5 mx-3"></div>
                <div class="px-4 pt-4 pb-2">
                    @include('components._date-picker-timespinner')
                </div>
                <div
                    class="border-t border-zinc-100 dark:border-white/5 px-4 py-2.5 flex justify-between items-center">
                    <button type="button" @click="nowTime()"
                        class="text-sm font-medium text-accent hover:text-accent/80 transition cursor-pointer">Now</button>
                    <button type="button" @click="applyTime()"
                        class="px-3 py-1 text-sm font-medium bg-accent text-white rounded-md hover:bg-accent/90 transition cursor-pointer">Apply</button>
                </div>
            </div>

            </div>{{-- end inner background layer --}}
        </div>{{-- end inner visual wrapper --}}
    </div>{{-- end popover --}}

</div>
