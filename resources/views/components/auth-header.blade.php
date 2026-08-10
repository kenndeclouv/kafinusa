@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center mb-2">
    <h1 class="text-3xl font-bold text-accent dark:text-accent-400 mb-2">{{ $title }}</h1>
    <flux:subheading>{{ $description }}</flux:subheading>
</div>
