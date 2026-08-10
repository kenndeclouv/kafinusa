<div class="flex items-start flex-col md:flex-row">
    <!-- Sidebar / Navlist -->
    <div class="me-10 w-full pb-4 md:w-[220px] order-2 md:order-1 mt-8 md:mt-0">
        <flux:navlist aria-label="{{ __('Settings') }}">
            <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('security.edit')" wire:navigate>{{ __('Security') }}</flux:navlist.item>
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden order-2 md:order-1 mb-8" />

    <!-- Main Content -->
    <div class="flex-1 self-stretch w-full order-1 md:order-2">
        <flux:heading class="hidden md:block">{{ $heading ?? '' }}</flux:heading>
        <flux:subheading class="hidden md:block">{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg mx-auto md:mx-0">
            {{ $slot }}
        </div>
    </div>
</div>
