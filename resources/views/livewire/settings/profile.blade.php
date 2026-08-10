<section class="w-full max-w-lg mx-auto pb-6 mt-0" x-data="{ currentTab: 'profile' }" @tab-change.window="currentTab = $event.detail">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Pengaturan') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Kelola pengaturan profil dan akun Anda') }}
        </flux:subheading>
    </div>

    <flux:heading class="sr-only">{{ __('Pengaturan profil') }}</flux:heading>

    <!-- Avatar and Name Section -->
    <div class="flex flex-col items-center justify-center text-center mb-6 mt-2">
        <label class="relative cursor-pointer group shrink-0 block">
            @php
                $imgSrc = $photo ? $photo->temporaryUrl() : auth()->user()->avatarUrl();
            @endphp
            @if ($imgSrc)
                <img src="{{ $imgSrc }}" alt="{{ auth()->user()->name }}"
                    class="w-32 h-32 rounded-full object-cover shadow-md ring-4 ring-white dark:ring-zinc-900 transition group-hover:opacity-75" />
            @else
                <div
                    class="w-32 h-32 rounded-full bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 text-4xl font-semibold shadow-md ring-4 ring-white dark:ring-zinc-900 transition group-hover:opacity-75">
                    {{ auth()->user()->initials() }}
                </div>
            @endif

            <!-- Active Badge -->
            <div
                class="absolute bottom-1 right-1 w-6 h-6 bg-accent border-2 border-white dark:border-zinc-900 rounded-full flex items-center justify-center">
                <flux:icon.check class="w-3 h-3 text-white" />
            </div>

            <!-- Hover Overlay -->
            <div
                class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition rounded-full">
                <div class="bg-black/50 text-white p-2 rounded-full backdrop-blur-sm">
                    <flux:icon.camera class="w-6 h-6" />
                </div>
            </div>

            <input type="file" wire:model="photo" accept="image/*" class="sr-only" />
        </label>

        <h2 class="mt-5 text-2xl font-semibold text-zinc-900 dark:text-white">{{ auth()->user()->name }}</h2>
    </div>

    <!-- Tabs Segmented Control -->
    <div class="flex justify-center mb-8" wire:ignore>
        <x-tabs active="profile" @tab-change="currentTab = $event.detail">
            <x-tab value="profile">Profil</x-tab>
            <x-tab value="security">Keamanan</x-tab>
            <x-tab value="appearance">Tampilan</x-tab>
        </x-tabs>
    </div>

    <!-- Profile Content -->
    <div x-show="currentTab === 'profile'" x-transition>


        <form wire:submit="updateProfileInformation" class="mb-8 w-full space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Profil') }}</flux:heading>
                <flux:subheading>{{ __('Perbarui nama dan alamat email Anda') }}</flux:subheading>
            </div>

            <flux:error name="photo" />

            <!-- iOS Style Connected List for Profile -->
            <div
                class="mt-4 bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">
                <!-- Name -->
                <label
                    class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        {{ __('Nama') }}
                    </span>
                    <div class="flex-1">
                        <input wire:model="name" type="text" required autocomplete="name" placeholder="John Doe"
                            class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                    </div>
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </label>
                <x-error-ios name="name" />

                <!-- Email -->
                <label
                    class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                    <span class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                        {{ __('Email') }}
                    </span>
                    <div class="flex-1">
                        <input wire:model="email" type="email" required autocomplete="email"
                            placeholder="john@example.com"
                            class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                    </div>
                </label>
                <x-error-ios name="email" />
            </div>

            <div class="mt-6 flex flex-col gap-2">
                <flux:button class="!rounded-full w-full" variant="primary" type="submit">{{ __('Simpan') }}
                </flux:button>
            </div>
        </form>

        <flux:separator variant="subtle" class="my-8" />

        <div class="mt-8 mb-8" x-data="{
            isSubscribed: false,
            init() {
                if (window.OneSignalDeferred) {
                    OneSignalDeferred.push(async (OneSignal) => {
                        setTimeout(() => {
                            if (OneSignal.User && OneSignal.User.PushSubscription) {
                                this.isSubscribed = OneSignal.User.PushSubscription.optedIn;
                            }
                        }, 500);
                        
                        OneSignal.User.PushSubscription.addEventListener('change', (event) => {
                            this.isSubscribed = event.current.optedIn;
                        });
                    });
                }
            },
            toggleSubscription() {
                if (!window.OneSignalDeferred) return;
                const targetState = this.isSubscribed;
                
                OneSignalDeferred.push(async (OneSignal) => {
                    if (!targetState) {
                        await OneSignal.User.PushSubscription.optOut();
                    } else {
                        await OneSignal.Notifications.requestPermission();
                        if (Notification.permission === 'granted') {
                            await OneSignal.User.PushSubscription.optIn();
                        } else {
                            this.isSubscribed = false;
                        }
                    }
                });
            }
        }">
            <div class="mb-3 pl-2">
                <flux:heading class="!font-semibold">{{ __('Notifikasi') }}</flux:heading>
                <flux:subheading>{{ __('Kelola preferensi notifikasi perangkat Anda') }}</flux:subheading>
            </div>

            <div class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden p-4 sm:p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <span class="text-[15px] font-medium text-zinc-900 dark:text-white block">{{ __('Notifikasi Perangkat') }}</span>
                        <span class="text-[13px] text-zinc-500 dark:text-zinc-400 block mt-0.5">{{ __('Terima peringatan pesanan baru dan tugas langsung di perangkat Anda.') }}</span>
                    </div>
                    <div class="shrink-0">
                        <x-switch x-model="isSubscribed" @change="toggleSubscription()" />
                    </div>
                </div>
            </div>
        </div>

        <flux:separator variant="subtle" class="my-8" />

        <div class="mt-8 mb-8">
            <div class="mb-3 pl-2">
                <flux:heading class="text-red-600 dark:text-red-500 !font-semibold">{{ __('Zona Berbahaya') }}
                </flux:heading>
                <flux:subheading>{{ __('Aksi sesi dan tindakan yang tidak bisa dibatalkan') }}</flux:subheading>
            </div>

            <div
                class="bg-red-50/30 dark:bg-red-500/5 rounded-3xl border border-red-500/30 dark:border-red-500/30 shadow-xs flex flex-col relative overflow-hidden">
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit"
                        class="w-full flex flex-row items-center justify-between px-4 py-3 relative group transition-colors focus-within:bg-red-50 dark:focus-within:bg-red-500/10 hover:bg-red-50 dark:hover:bg-red-500/10 cursor-pointer text-left">
                        <span class="text-[15px] font-medium text-red-600 dark:text-red-400 select-none">
                            {{ __('Keluar') }}
                        </span>
                        <flux:icon.arrow-right-start-on-rectangle
                            class="w-5 h-5 text-red-600 dark:text-red-400 opacity-70 group-hover:opacity-100" />
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-red-500/20 dark:bg-red-500/20"></div>
                    </button>
                </form>

                <div>
                    <flux:modal.trigger name="confirm-user-deletion">
                        <button type="button" x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                            class="w-full flex flex-row items-center justify-between px-4 py-3 relative group transition-colors focus-within:bg-red-50 dark:focus-within:bg-red-500/10 hover:bg-red-50 dark:hover:bg-red-500/10 cursor-pointer text-left">
                            <span class="text-[15px] font-medium text-red-600 dark:text-red-400 select-none">
                                {{ __('Hapus akun') }}
                            </span>
                            <flux:icon.trash
                                class="w-5 h-5 text-red-600 dark:text-red-400 opacity-70 group-hover:opacity-100" />
                        </button>
                    </flux:modal.trigger>

                    <flux:modal :closable="false" scroll="body" name="confirm-user-deletion"
                        :show="$errors->isNotEmpty()" focusable class="max-w-lg !rounded-3xl">
                        <form method="POST" wire:submit="deleteUser" class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('Apakah Anda yakin ingin menghapus akun Anda?') }}
                                </flux:heading>

                                <flux:subheading>
                                    {{ __('Setelah akun Anda dihapus, semua sumber daya dan datanya akan dihapus secara permanen. Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun Anda secara permanen.') }}
                                </flux:subheading>
                            </div>

                            <flux:input wire:model="delete_password" :label="__('Kata Sandi')" type="password" viewable />

                            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                                <flux:modal.close>
                                    <flux:button variant="filled">{{ __('Batal') }}</flux:button>
                                </flux:modal.close>

                                <flux:button variant="danger" type="submit" class="!rounded-full">
                                    {{ __('Hapus akun') }}</flux:button>
                            </div>
                        </form>
                    </flux:modal>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Content -->
    <div x-show="currentTab === 'security'" x-transition x-cloak>
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Perbarui kata sandi') }}</flux:heading>
                <flux:subheading>{{ __('Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman') }}
                </flux:subheading>
            </div>

            <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">

                <!-- iOS Style Connected List for Security -->
                <div
                    class="mt-4 bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">
                    <!-- Current Password -->
                    <label
                        class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span
                            class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/2 shrink-0 py-2 select-none">
                            {{ __('Kata sandi saat ini') }}
                        </span>
                        <div class="flex-1">
                            <input wire:model="current_password" type="password" required
                                autocomplete="current-password" placeholder="••••••••"
                                class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                        </div>
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    </label>
                    <x-error-ios name="current_password" />

                    <!-- New Password -->
                    <label
                        class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span
                            class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/2 shrink-0 py-2 select-none">
                            {{ __('Kata sandi baru') }}
                        </span>
                        <div class="flex-1">
                            <input wire:model="password" type="password" required autocomplete="new-password"
                                placeholder="••••••••"
                                class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                        </div>
                        <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                    </label>
                    <x-error-ios name="password" />

                    <!-- Confirm Password -->
                    <label
                        class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span
                            class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/2 shrink-0 py-2 select-none">
                            {{ __('Konfirmasi kata sandi') }}
                        </span>
                        <div class="flex-1">
                            <input wire:model="password_confirmation" type="password" required
                                autocomplete="new-password" placeholder="••••••••"
                                class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                        </div>
                    </label>
                    <x-error-ios name="password_confirmation" />
                </div>

                <div class="mt-6 flex flex-col gap-2">
                    <flux:button class="!rounded-full w-full" variant="primary" type="submit"
                        data-test="update-password-button">{{ __('Simpan') }}</flux:button>
                </div>
            </form>
        </div>
    </div>

    <!-- Appearance Content -->
    <div x-show="currentTab === 'appearance'" x-transition x-cloak>
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Tampilan') }}</flux:heading>
                <flux:subheading>{{ __('Perbarui pengaturan tampilan untuk akun Anda') }}</flux:subheading>
            </div>

            <div class="mt-4 bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden"
                x-data>
                <label
                    class="flex flex-row items-center justify-between px-4 py-3 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-pointer hover:bg-zinc-50 dark:hover:bg-white/[0.07]">
                    <div class="flex items-center gap-3">
                        <flux:icon.sun class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                        <span
                            class="text-[15px] font-medium text-zinc-900 dark:text-white select-none">{{ __('Terang') }}</span>
                    </div>
                    <input type="radio" value="light" x-model="$flux.appearance" class="sr-only" />
                    <flux:icon.check class="w-5 h-5 text-accent" x-show="$flux.appearance === 'light'" />
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </label>

                <label
                    class="flex flex-row items-center justify-between px-4 py-3 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-pointer hover:bg-zinc-50 dark:hover:bg-white/[0.07]">
                    <div class="flex items-center gap-3">
                        <flux:icon.moon class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                        <span
                            class="text-[15px] font-medium text-zinc-900 dark:text-white select-none">{{ __('Gelap') }}</span>
                    </div>
                    <input type="radio" value="dark" x-model="$flux.appearance" class="sr-only" />
                    <flux:icon.check class="w-5 h-5 text-accent" x-show="$flux.appearance === 'dark'" />
                    <div class="absolute bottom-0 right-4 left-4 h-px bg-zinc-200 dark:bg-white/10"></div>
                </label>

                <label
                    class="flex flex-row items-center justify-between px-4 py-3 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-pointer hover:bg-zinc-50 dark:hover:bg-white/[0.07]">
                    <div class="flex items-center gap-3">
                        <flux:icon.computer-desktop class="w-5 h-5 text-zinc-500 dark:text-zinc-400" />
                        <span
                            class="text-[15px] font-medium text-zinc-900 dark:text-white select-none">{{ __('Sistem') }}</span>
                    </div>
                    <input type="radio" value="system" x-model="$flux.appearance" class="sr-only" />
                    <flux:icon.check class="w-5 h-5 text-accent" x-show="$flux.appearance === 'system'" />
                </label>
            </div>
        </div>
    </div>
</section>
