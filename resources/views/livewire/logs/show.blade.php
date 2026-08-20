<div x-data="{
    search: '',
    typeFilter: 'all',
    get filteredEntries() {
        return this.allEntries.filter(e => {
            if (this.typeFilter !== 'all' && e.type !== this.typeFilter) return false;
            if (this.search && !e.message.toLowerCase().includes(this.search.toLowerCase()) && !e.full_entry.toLowerCase().includes(this.search.toLowerCase())) return false;
            return true;
        });
    },
    allEntries: @js($entries).map(e => ({ ...e, type: String(e.type).toLowerCase(), env: String(e.env).toLowerCase() })),
}">
    {{-- Tailwind Safelist: classes used dynamically in Alpine :class must exist in markup for JIT --}}
    <div
        class="hidden
        bg-red-50/50 bg-red-100 bg-red-500 bg-red-500/20 bg-red-50/30
        text-red-700 text-red-400
        border-red-200 border-red-500/20
        dark:bg-red-950/20 dark:bg-red-500/20 dark:bg-red-950/10
        dark:text-red-400 dark:border-red-500/20

        bg-amber-50/50 bg-amber-100 bg-amber-500 bg-amber-500/20 bg-amber-50/30
        text-amber-700 text-amber-400
        border-amber-200 border-amber-500/20
        dark:bg-amber-950/20 dark:bg-amber-500/20 dark:bg-amber-950/10
        dark:text-amber-400 dark:border-amber-500/20

        bg-sky-50/50 bg-sky-100 bg-sky-500 bg-sky-500/20 bg-sky-50/30
        text-sky-700 text-sky-400
        border-sky-200 border-sky-500/20
        dark:bg-sky-950/20 dark:bg-sky-500/20 dark:bg-sky-950/10
        dark:text-sky-400 dark:border-sky-500/20
    ">
    </div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">Log Details</flux:heading>
            <flux:subheading class="font-mono text-xs mt-1">{{ $filename }}</flux:subheading>
        </div>

        <flux:button href="{{ route('logs.index') }}" wire:navigate.hover icon="arrow-left" variant="ghost">
            Back to Logs
        </flux:button>
    </div>

    {{-- Search & Filter Bar --}}
    <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 mb-6">
        <div class="flex flex-wrap items-end gap-3">
            <flux:field class="flex-1 min-w-48">
                <flux:label>Search Logs</flux:label>
                <flux:input x-model.debounce.300ms="search" placeholder="Filter by message content..."
                    icon="magnifying-glass" />
            </flux:field>

            {{-- Type Filter Pills --}}
            <div
                class="flex items-center gap-1.5 p-1 rounded-xl bg-zinc-100/50 dark:bg-white/5 border border-zinc-200 dark:border-white/5">
                <template x-for="t in ['all', 'error', 'warning', 'info']" :key="t">
                    <button @click="typeFilter = t" type="button"
                        :class="typeFilter === t ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm' :
                            'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'"
                        class="px-3 py-2 rounded-lg text-xs font-semibold transition-all cursor-pointer uppercase"
                        x-text="t"></button>
                </template>
            </div>

            <div class="text-xs text-zinc-500 font-medium py-2">
                <span x-text="filteredEntries.length"></span> / <span x-text="allEntries.length"></span> entries
            </div>
        </div>
    </div>

    {{-- Log Entries --}}
    <div class="space-y-2">
        <template x-for="(entry, index) in filteredEntries" :key="index">
            <div x-data="{ expanded: false }" class="rounded-xl border overflow-hidden transition-all"
                :class="{
                    'bg-red-50/50 dark:bg-red-950/20 border-red-200 dark:border-red-500/20': entry.type === 'error',
                    'bg-amber-50/50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-500/20': entry
                        .type === 'warning',
                    'bg-sky-50/50 dark:bg-sky-950/20 border-sky-200 dark:border-sky-500/20': entry.type === 'info',
                    'bg-white dark:bg-white/5 border-zinc-200 dark:border-white/5': !['error', 'warning', 'info']
                        .includes(entry.type),
                }">
                <button @click="expanded = !expanded"
                    class="w-full text-left px-4 py-3 flex items-center justify-between hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer gap-3">
                    <div class="flex items-center gap-3 overflow-hidden min-w-0">
                        {{-- Type indicator dot --}}
                        <span class="shrink-0 w-2 h-2 rounded-full"
                            :class="{
                                'bg-red-500': entry.type === 'error',
                                'bg-amber-500': entry.type === 'warning',
                                'bg-sky-500': entry.type === 'info',
                                'bg-zinc-400': !['error', 'warning', 'info'].includes(entry.type),
                            }"></span>

                        {{-- Type Badge --}}
                        <span class="shrink-0 px-2 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider"
                            :class="{
                                'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400': entry
                                    .type === 'error',
                                'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400': entry
                                    .type === 'warning',
                                'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-400': entry
                                    .type === 'info',
                                'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400': !['error', 'warning',
                                    'info'
                                ].includes(entry.type),
                            }"
                            x-text="entry.type"></span>

                        {{-- Env Badge --}}
                        <span
                            class="shrink-0 px-2 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-500"
                            x-text="entry.env"></span>

                        {{-- Timestamp --}}
                        <span class="shrink-0 font-mono text-xs text-zinc-400 dark:text-zinc-600"
                            x-text="entry.timestamp"></span>

                        {{-- Message --}}
                        <span class="truncate text-sm text-zinc-700 dark:text-zinc-300"
                            x-text="entry.message.substring(0, 120) + (entry.message.length > 120 ? '…' : '')"></span>
                    </div>

                    <flux:icon name="chevron-down"
                        class="w-4 h-4 shrink-0 text-zinc-400 transition-transform duration-200"
                        x-bind:class="expanded ? 'rotate-180' : ''" />
                </button>

                <div x-show="expanded" x-collapse x-cloak class="border-t px-4 py-4"
                    :class="{
                        'border-red-200 dark:border-red-500/20 bg-black': entry
                            .type === 'error',
                        'border-amber-200 dark:border-amber-500/20 bg-black': entry
                            .type === 'warning',
                        'border-sky-200 dark:border-sky-500/20 bg-black': entry
                            .type === 'info',
                        'border-zinc-200 dark:border-white/5 bg-black': !['error', 'warning', 'info']
                            .includes(entry.type),
                    }">
                    <pre class="whitespace-pre-wrap font-mono text-xs leading-relaxed overflow-x-auto text-zinc-600 dark:text-zinc-400"
                        x-text="entry.full_entry"></pre>
                </div>
            </div>
        </template>

        {{-- Empty: no matches --}}
        <template x-if="filteredEntries.length === 0 && allEntries.length > 0">
            <div
                class="text-center py-12 bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/5 rounded-2xl">
                <div
                    class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <flux:icon name="magnifying-glass" class="w-8 h-8 text-zinc-400" />
                </div>
                <flux:heading size="lg">No matching entries</flux:heading>
                <flux:text class="mt-2 text-zinc-500">Try adjusting your search or filter.</flux:text>
            </div>
        </template>

        {{-- Empty: no entries at all --}}
        <template x-if="allEntries.length === 0">
            <div
                class="text-center py-12 bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/5 rounded-2xl">
                <div
                    class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <flux:icon name="document-text" class="w-8 h-8 text-zinc-400" />
                </div>
                <flux:heading size="lg">Empty Log File</flux:heading>
                <flux:text class="mt-2 text-zinc-500">This log file contains no parseable entries.</flux:text>
            </div>
        </template>
    </div>
</div>
