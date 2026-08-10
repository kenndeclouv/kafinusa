{{--
Shared time-spinner partial.
Used inside date-picker.blade.php (both type=time and datetime-local).
Requires Alpine context from parent date-picker x-data.
--}}
<div class="flex items-center justify-center gap-1">

    {{-- ── Hour ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-col items-center gap-1">
        <button type="button" @click="adjH(1)"
            class="w-8 h-7 flex items-center justify-center rounded-lg text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-white/10 transition cursor-pointer">
            <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                <path d="M2 8L6 4L10 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </button>
        <div class="w-12 h-10 flex items-center justify-center rounded-lg border border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/5 text-lg font-semibold tabular-nums text-zinc-800 dark:text-zinc-100 select-none"
            x-text="dH"></div>
        <button type="button" @click="adjH(-1)"
            class="w-8 h-7 flex items-center justify-center rounded-lg text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-white/10 transition cursor-pointer">
            <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </button>
    </div>

    {{-- Colon --}}
    <span class="text-xl font-bold text-zinc-300 dark:text-zinc-600 mb-0.5 select-none">:</span>

    {{-- ── Minute ────────────────────────────────────────────────────── --}}
    <div class="flex flex-col items-center gap-1">
        <button type="button" @click="adjM(1)"
            class="w-8 h-7 flex items-center justify-center rounded-lg text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-white/10 transition cursor-pointer">
            <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                <path d="M2 8L6 4L10 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </button>
        <div class="w-12 h-10 flex items-center justify-center rounded-lg border border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/5 text-lg font-semibold tabular-nums text-zinc-800 dark:text-zinc-100 select-none"
            x-text="dM"></div>
        <button type="button" @click="adjM(-1)"
            class="w-8 h-7 flex items-center justify-center rounded-lg text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-white/10 transition cursor-pointer">
            <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </button>
    </div>

    {{-- ── Seconds (shown only when step < 60) ──────────────────────── --}} <template x-if="showSec">
        <div class="flex items-center gap-1">
            <span class="text-xl font-bold text-zinc-300 dark:text-zinc-600 mb-0.5 select-none">:</span>
            <div class="flex flex-col items-center gap-1">
                <button type="button" @click="adjS(1)"
                    class="w-8 h-7 flex items-center justify-center rounded-lg text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-white/10 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                        <path d="M2 8L6 4L10 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
                <div class="w-12 h-10 flex items-center justify-center rounded-lg border border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/5 text-lg font-semibold tabular-nums text-zinc-800 dark:text-zinc-100 select-none"
                    x-text="dS"></div>
                <button type="button" @click="adjS(-1)"
                    class="w-8 h-7 flex items-center justify-center rounded-lg text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-white/10 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 12 12" fill="none">
                        <path d="M2 4L6 8L10 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </template>

    {{-- ── AM / PM toggle ────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-1 ml-1">
        <button type="button" @click="if (!isAM) toggleAP()"
            class="h-[52px] px-3 rounded-t-lg text-sm font-semibold transition cursor-pointer border"
            :class="isAM
                ?
                'bg-accent text-white border-accent shadow-sm' :
                'text-zinc-500 dark:text-zinc-400 border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/5 hover:bg-zinc-100 dark:hover:bg-white/10'">
            AM
        </button>
        <button type="button" @click="if (isAM) toggleAP()"
            class="h-[52px] px-3 rounded-b-lg text-sm font-semibold transition cursor-pointer border"
            :class="!isAM
                ?
                'bg-accent text-white border-accent shadow-sm' :
                'text-zinc-500 dark:text-zinc-400 border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/5 hover:bg-zinc-100 dark:hover:bg-white/10'">
            PM
        </button>
    </div>

</div>
