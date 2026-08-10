<ui-toast {{ $attributes->except('class') }}>
    <template>
        <div {{ $attributes->only(['class'])->class('max-w-sm in-[ui-toast-group]:max-w-auto in-[ui-toast-group]:w-xs sm:in-[ui-toast-group]:w-sm') }} data-variant="" data-flux-toast-dialog>
            <div class="p-4 flex rounded-3xl bg-white dark:bg-zinc-900 shadow-[0_8px_40px_rgba(0,0,0,0.12)] border border-zinc-200/50 dark:border-white/10">
                <div class="flex-1 flex gap-4 overflow-hidden items-start">
                    
                    {{-- Icon Container --}}
                    <div class="shrink-0 w-12 h-12 rounded-[1rem] flex items-center justify-center 
                        bg-zinc-50 dark:bg-zinc-800/50
                        [[data-flux-toast-dialog][data-variant=success]_&]:bg-green-50 dark:[[data-flux-toast-dialog][data-variant=success]_&]:bg-green-500/10
                        [[data-flux-toast-dialog][data-variant=warning]_&]:bg-amber-50 dark:[[data-flux-toast-dialog][data-variant=warning]_&]:bg-amber-500/10
                        [[data-flux-toast-dialog][data-variant=info]_&]:bg-cyan-50 dark:[[data-flux-toast-dialog][data-variant=info]_&]:bg-cyan-500/10
                        [[data-flux-toast-dialog][data-variant=danger]_&]:bg-red-50 dark:[[data-flux-toast-dialog][data-variant=danger]_&]:bg-red-500/10
                    ">
                        {{-- Success icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="hidden [[data-flux-toast-dialog][data-variant=success]_&]:block size-6 text-green-500 dark:text-green-400">
                            <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14Zm3.844-8.791a.75.75 0 0 0-1.188-.918l-3.7 4.79-1.649-1.833a.75.75 0 1 0-1.114 1.004l2.25 2.5a.75.75 0 0 0 1.15-.043l4.25-5.5Z" clip-rule="evenodd" />
                        </svg>

                        {{-- Warning icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="hidden [[data-flux-toast-dialog][data-variant=warning]_&]:block size-6 text-amber-500 dark:text-amber-400">
                            <path fill-rule="evenodd" d="M6.701 2.25c.577-1 2.02-1 2.598 0l5.196 9a1.5 1.5 0 0 1-1.299 2.25H2.804a1.5 1.5 0 0 1-1.3-2.25l5.197-9ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 1 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                        </svg>

                        {{-- Info icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="hidden [[data-flux-toast-dialog][data-variant=info]_&]:block size-6 text-cyan-500 dark:text-cyan-400">
                            <path fill-rule="evenodd" d="M15 8A7 7 0 1 1 1 8a7 7 0 0 1 14 0ZM9 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM6.75 8a.75.75 0 0 0 0 1.5h.75v1.75a.75.75 0 0 0 1.5 0v-2.5A.75.75 0 0 0 8.25 8h-1.5Z" clip-rule="evenodd" />
                        </svg>

                        {{-- Danger icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="hidden [[data-flux-toast-dialog][data-variant=danger]_&]:block size-6 text-red-500 dark:text-red-400">
                            <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    {{-- Text Content --}}
                    <div class="flex-1 min-w-0 pt-0.5">
                        {{-- Heading --}}
                        <div class="font-bold text-base tracking-tight text-zinc-900 dark:text-white [&:not(:empty)]:mb-1">
                            <slot name="heading"></slot>
                        </div>

                        {{-- Text --}}
                        <div class="font-medium text-[13px] leading-snug text-zinc-500 dark:text-zinc-400 pr-2">
                            <slot name="text"></slot>
                        </div>
                    </div>

                    {{-- Close / Timestamp --}}
                    <ui-close class="flex items-start shrink-0">
                        <button type="button" class="text-sm font-medium text-zinc-400 hover:text-zinc-900 dark:text-zinc-500 dark:hover:text-white transition-colors pt-1 px-1" as="button">
                            now
                        </button>
                    </ui-close>
                </div>
            </div>
        </div>
    </template>
</ui-toast>
