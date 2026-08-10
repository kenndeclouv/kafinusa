<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <flux:heading size="xl">{{ __('Notifikasi') }}</flux:heading>
            <flux:subheading>{{ __('Kelola dan kirim notifikasi push ke pengguna.') }}</flux:subheading>
        </div>
    </div>

    <div
        class="rounded-2xl bg-zinc-50 dark:bg-white/5 border border-zinc-200 dark:border-white/5 overflow-hidden p-12 text-center flex flex-col items-center justify-center mt-8">
        <div class="w-16 h-16 bg-accent/10 rounded-full flex items-center justify-center mb-6 text-accent">
            <flux:icon.megaphone class="w-8 h-8" />
        </div>
        <flux:heading size="xl">Kirim Notifikasi</flux:heading>
        <flux:subheading class="max-w-md mx-auto mt-3">
            Kirimkan pesan, atau pengumuman penting langsung ke layar HP atau komputer pengguna Anda secara
            instan (real-time).
        </flux:subheading>

        @can('notifications:send')
            <div class="mt-8">
                <flux:button x-on:click="$flux.modal('send-notification-modal').show()" variant="primary"
                    icon="paper-airplane" class="!rounded-full px-6">
                    Buat Notifikasi Baru
                </flux:button>
            </div>
        @endcan
    </div>

    <flux:modal :closable="false" scroll="body" name="send-notification-modal" class="md:w-[32rem] !rounded-3xl">
        <form wire:submit="send" class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg">Kirim Notifikasi</flux:heading>
                <flux:subheading>Isi pesan dan pilih target penerima.</flux:subheading>
            </div>

            <div x-data="{ type: @entangle('target_type') }"
                class="bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 shadow-sm rounded-2xl flex flex-col overflow-hidden">
                <div class="flex flex-col gap-0">
                    <!-- Target Penerima -->
                    <label
                        class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span
                            class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Target
                        </span>
                        <div class="flex-1">
                            <x-searchable-select wire:model.live="target_type" :options="$this->targetTypeOptions" variant="ios"
                                placeholder="Pilih target..." />
                        </div>
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    </label>

                    <!-- Pilihan Role -->
                    <div x-show="type === 'role'" x-cloak>
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Role
                            </span>
                            <div class="flex-1">
                                <x-searchable-select wire:model="target_role" :options="$this->roleOptions" multiple="true" variant="ios"
                                    placeholder="Pilih role..." />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                    </div>

                    <!-- Pilihan User -->
                    <div x-show="type === 'user'" x-cloak>
                        <label
                            class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                            <span
                                class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                                Pengguna
                            </span>
                            <div class="flex-1">
                                <x-searchable-select wire:model="target_user_id" :options="$this->userOptions" searchable="true" multiple="true"
                                    searchPlaceholder="Cari pengguna..." variant="ios"
                                    placeholder="Pilih pengguna..." />
                            </div>
                            <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                        </label>
                    </div>

                    <!-- Judul Notifikasi -->
                    <label
                        class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span
                            class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Judul
                        </span>
                        <div class="flex-1">
                            <input type="text" wire:model="title" placeholder="Contoh: Promo Spesial"
                                class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right"
                                required />
                        </div>
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    </label>

                    <!-- Pesan Notifikasi -->
                    <label
                        class="flex flex-col px-4 py-3 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-full select-none mb-2">
                            Pesan
                        </span>
                        <div class="flex-1">
                            <textarea wire:model="message" placeholder="Tuliskan isi pesan notifikasi..." rows="3"
                                class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-1 resize-none"
                                required></textarea>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Error Messages Container (Optional but good for validation) -->
            <div>
                <x-error-ios name="target_type" />
                <x-error-ios name="target_role" />
                <x-error-ios name="target_user_id" />
                <x-error-ios name="title" />
                <x-error-ios name="message" />
            </div>

            <div class="mt-4 flex flex-col gap-2">
                <flux:button type="submit" variant="primary" class="!rounded-full">Kirim Notifikasi</flux:button>
                <flux:button type="button" x-on:click="$flux.modal('send-notification-modal').close()"
                    variant="outline" class="!rounded-full">Batal</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
