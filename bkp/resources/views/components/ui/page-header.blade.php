{{-- resources/views/components/ui/page-header.blade.php --}}
@props([
    'title',
    'subtitle' => null,
    'icon' => 'heroicon-o-building-office-2',
    'searchAction' => null,
    'searchQuery' => null,
    'createRoute' => null,
    'createLabel' => '+ New',
])

<div class="flex flex-col lg:flex-row items-start lg:items-center justify-between mb-6 space-y-4 lg:space-y-0">
    {{-- Left: Title + subtitle --}}
    <div class="space-y-1">
        <span class="flex items-center space-x-2 mb-2">
            <x-dynamic-component :component="$icon" class="h-8 w-8" />
            <h1 class="text-3xl font-bold">{{ $title }}</h1>
        </span>
        @if ($subtitle)
            <p class="text-neutral-600 dark:text-neutral-400 text-xs">{{ $subtitle }}</p>
        @endif
    </div>

    {{-- Right: Search + Create --}}
    <div class="flex items-center space-x-4 w-full lg:w-auto">
        @if ($searchAction)
            <form method="GET" action="{{ $searchAction }}" class="flex-1">
                <input name="search" value="{{ $searchQuery }}" type="text" placeholder="Search"
                    class="w-full px-4 py-2 rounded-md bg-neutral-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 placeholder-neutral-500 border border-neutral-400" />
            </form>
        @endif

        @if ($createRoute)
            <a href="{{ $createRoute }}"
                class="bg-neutral-900 hover:bg-neutral-800 text-white px-4 py-2 rounded-md">
                {{ $createLabel }}
            </a>
        @endif
    </div>
</div>