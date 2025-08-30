@props(['title', 'description', 'icon', 'hasUnsavedChanges' => false])

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <x-dynamic-component :component="$icon" class="h-5 w-5" />
                {{ $title }}
            </h2>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">{{ $description }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($hasUnsavedChanges)
                <span class="text-sm text-orange-600 dark:text-orange-400 flex items-center gap-1">
                    <x-heroicon-o-exclamation-triangle class="h-4 w-4" />
                    Unsaved changes
                </span>
            @endif
            <button wire:click="saveSettings" 
                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                <x-heroicon-o-check class="h-4 w-4 mr-2" />
                Save Changes
            </button>
            <button wire:click="resetToDefaults" 
                wire:confirm="Are you sure you want to reset all settings to their defaults? This cannot be undone."
                class="inline-flex items-center px-3 py-2 bg-neutral-600 hover:bg-neutral-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                Reset All
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if($showFlash ?? false)
        <div x-data="{ show: true }" 
             x-init="setTimeout(() => show = false, 5000)" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-y-0" 
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="p-4 rounded-lg shadow {{ $flashType === 'success' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200' : ($flashType === 'error' ? 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200' : 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-200') }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    @if($flashType === 'success')
                        <x-heroicon-o-check-circle class="h-5 w-5 mr-2" />
                    @elseif($flashType === 'error')
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                    @else
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                    @endif
                    {{ $flashMessage }}
                </div>
                <button wire:click="hideFlash" class="text-current hover:opacity-75">
                    <x-heroicon-o-x-mark class="h-4 w-4" />
                </button>
            </div>
        </div>
    @endif

    {{-- Content --}}
    <div class="space-y-6">
        {{ $slot }}
    </div>
</div>

{{-- Unsaved Changes Warning --}}
@if($hasUnsavedChanges)
<script>
window.addEventListener('beforeunload', function (e) {
    e.preventDefault();
    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
});
</script>
@endif
