@props(['title', 'description' => ''])

<div class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg p-6">
    @if($title)
        <div class="mb-6">
            <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">{{ $title }}</h3>
            @if($description)
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">{{ $description }}</p>
            @endif
        </div>
    @endif
    
    {{ $slot }}
</div>
