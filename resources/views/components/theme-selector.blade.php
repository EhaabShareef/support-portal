@props(['currentTheme' => null])

@php
    $themeService = app(\App\Services\ThemeService::class);
    $themes = $themeService->getAvailableThemes();
    $current = $currentTheme ?? $themeService->getCurrentTheme();
    $allowSelection = config('theme.settings.allow_user_theme_selection', false);
@endphp

@if($allowSelection && count($themes) > 1)
<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" 
            class="btn-secondary flex items-center gap-2"
            aria-label="Select theme">
        <x-heroicon-o-swatch class="h-4 w-4" />
        <span class="hidden sm:inline">{{ $themes[$current]['name'] ?? 'Theme' }}</span>
        <x-heroicon-o-chevron-down class="h-4 w-4" />
    </button>

    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-64 bg-white dark:bg-neutral-800 rounded-md shadow-lg border border-neutral-200 dark:border-neutral-700 z-50">
        
        <div class="py-1">
            <div class="px-3 py-2 text-body-secondary font-medium border-b border-neutral-200 dark:border-neutral-700">
                Choose Theme
            </div>
            
            @foreach($themes as $themeKey => $theme)
                <button wire:click="setTheme('{{ $themeKey }}')"
                        class="w-full px-3 py-2 text-left hover:bg-neutral-100 dark:hover:bg-neutral-700 flex items-center justify-between group">
                    <div>
                        <div class="text-body font-medium">{{ $theme['name'] }}</div>
                        <div class="text-body-tertiary text-xs">{{ $theme['description'] }}</div>
                    </div>
                    
                    <!-- Theme color preview -->
                    <div class="flex gap-1">
                        <div class="w-3 h-3 rounded-full border border-neutral-300" 
                             style="background-color: rgb({{ $theme['primary']['500'] }})"></div>
                        <div class="w-3 h-3 rounded-full border border-neutral-300" 
                             style="background-color: rgb({{ $theme['accent']['500'] }})"></div>
                    </div>
                    
                    @if($themeKey === $current)
                        <x-heroicon-o-check class="h-4 w-4 text-accent-600 ml-2" />
                    @endif
                </button>
            @endforeach
        </div>
    </div>
</div>
@endif