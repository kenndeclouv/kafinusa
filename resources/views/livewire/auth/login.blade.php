<x-layouts::auth :title="__('Masuk')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Masuk ke akun Anda')" :description="__('Masukkan email dan kata sandi Anda di bawah ini untuk masuk')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- iOS Style Connected List -->
            <div>
                <div
                    class="bg-white dark:bg-white/5 rounded-3xl border border-zinc-200 dark:border-white/10 shadow-xs flex flex-col relative overflow-hidden">

                    <!-- Email Address -->
                    <label
                        class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text border-b border-zinc-200 dark:border-white/10">
                        <span
                            class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Email
                        </span>
                        <div class="flex-1">
                            <input name="email" type="email" required autofocus autocomplete="email"
                                placeholder="email@example.com" value="{{ old('email') }}"
                                class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                        </div>
                    </label>

                    <!-- Password -->
                    <label
                        class="flex flex-row items-center px-4 py-1.5 relative group transition-colors focus-within:bg-zinc-50 dark:focus-within:bg-white/[0.07] cursor-text">
                        <span
                            class="text-[15px] font-medium text-zinc-900 dark:text-white w-1/3 shrink-0 py-2 select-none">
                            Kata Sandi
                        </span>
                        <div class="flex-1">
                            <input name="password" type="password" required autocomplete="current-password"
                                placeholder="Kata Sandi"
                                class="w-full bg-transparent border-none outline-none focus:ring-0 text-[15px] text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 px-0 py-2 text-right" />
                        </div>
                    </label>
                </div>

                @error('email')
                    <p class="text-sm text-red-500 mt-2 px-2">{{ $message }}</p>
                @enderror
                @error('password')
                    <p class="text-sm text-red-500 mt-2 px-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between px-2 -mt-2">
                <x-checkbox name="remember" :label="__('Ingat saya')" :checked="old('remember')" />

                @if (Route::has('password.request'))
                    <flux:link class="text-sm" :href="route('password.request')" wire:navigate>
                        {{ __('Lupa kata sandi?') }}
                    </flux:link>
                @endif
            </div>

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full !rounded-full" data-test="login-button">
                    {{ __('Masuk') }}
                </flux:button>
            </div>

            <!-- Install App Button (Alpine.js handled PWA) -->
            <div x-data="{ deferredPrompt: null, showInstallBtn: false }" 
                 @beforeinstallprompt.window="
                    event.preventDefault();
                    deferredPrompt = event;
                    showInstallBtn = true;
                 " 
                 x-show="showInstallBtn" 
                 style="display: none;" 
                 class="mt-2">
                 
                <div class="relative flex items-center py-2 mb-2">
                    <div class="grow border-t border-zinc-200 dark:border-white/10"></div>
                    <span class="shrink-0 px-3 text-xs text-zinc-500 dark:text-zinc-400">Atau</span>
                    <div class="grow border-t border-zinc-200 dark:border-white/10"></div>
                </div>

                <flux:button variant="outline" type="button" class="w-full !rounded-full" 
                    @click="
                        if (deferredPrompt) {
                            deferredPrompt.prompt();
                            deferredPrompt.userChoice.then((choiceResult) => {
                                if (choiceResult.outcome === 'accepted') {
                                    showInstallBtn = false;
                                }
                                deferredPrompt = null;
                            });
                        }
                    ">
                    <flux:icon.arrow-down-on-square class="w-5 h-5 mr-2" />
                    {{ __('Install Aplikasi di HP') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
