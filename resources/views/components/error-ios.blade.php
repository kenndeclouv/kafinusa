@props(['name'])

@error($name)
    <p class="px-4 py-1.5 text-xs text-red-500 bg-red-50 dark:bg-red-500/10">{{ $message }}</p>
@enderror
