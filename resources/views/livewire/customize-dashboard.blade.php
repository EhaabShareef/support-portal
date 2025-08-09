{{-- Customize Dashboard Modal --}}
<div>
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" 
                 wire:click="closeModal" aria-hidden="true"></div>

            <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                {{-- Modal Header --}}
                <div class="bg-white dark:bg-neutral-800 px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-cog-6-tooth class="h-6 w-6 text-purple-600" />
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100" id="modal-title">
                                Customize Dashboard
                            </h3>
                        </div>
                        <button wire:click="closeModal" 
                                class="text-neutral-400 hover:text-neutral-500 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                        Configure which widgets to display and how they appear on your dashboard.
                    </p>
                </div>

                {{-- Modal Body --}}
                <div class="bg-white dark:bg-neutral-800 px-6 py-4 max-h-96 overflow-y-auto">
                    @if(empty($widgets))
                        <div class="text-center py-8">
                            <x-heroicon-o-squares-plus class="mx-auto h-12 w-12 text-neutral-400" />
                            <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No widgets available</h3>
                            <p class="mt-1 text-sm text-neutral-500">No widgets are configured for your role.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($widgets as $index => $widget)
                                <div class="bg-neutral-50 dark:bg-neutral-700 rounded-lg p-4 border border-neutral-200 dark:border-neutral-600">
                                    <div class="flex items-center justify-between">
                                        {{-- Widget Info --}}
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3">
                                                {{-- Visibility Toggle --}}
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" 
                                                           wire:model.live="widgets.{{ $index }}.visible"
                                                           wire:change="toggleVisibility({{ $index }})"
                                                           class="rounded border-neutral-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                                           {{ !$widget['can_view'] ? 'disabled' : '' }} />
                                                </label>

                                                <div class="flex-1">
                                                    <h4 class="font-medium text-neutral-900 dark:text-neutral-100">
                                                        {{ $widget['name'] }}
                                                    </h4>
                                                    @if($widget['description'])
                                                        <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
                                                            {{ $widget['description'] }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Controls --}}
                                        <div class="flex items-center gap-2">
                                            {{-- Size Selector --}}
                                            <select wire:model.live="widgets.{{ $index }}.size"
                                                    wire:change="changeSize({{ $index }}, $event.target.value)"
                                                    class="text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-800 focus:ring-purple-500 focus:border-purple-500"
                                                    {{ !$widget['visible'] || !$widget['can_view'] ? 'disabled' : '' }}>
                                                @foreach($availableSizes as $sizeKey => $sizeInfo)
                                                    <option value="{{ $sizeKey }}">{{ $sizeInfo['label'] ?? $sizeKey }}</option>
                                                @endforeach
                                            </select>

                                            {{-- Order Controls --}}
                                            <div class="flex items-center gap-1">
                                                <button wire:click="moveUp({{ $index }})"
                                                        class="p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 disabled:opacity-50"
                                                        {{ $index === 0 || !$widget['can_view'] ? 'disabled' : '' }}>
                                                    <x-heroicon-o-chevron-up class="h-4 w-4" />
                                                </button>
                                                <button wire:click="moveDown({{ $index }})"
                                                        class="p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 disabled:opacity-50"
                                                        {{ $index === count($widgets) - 1 || !$widget['can_view'] ? 'disabled' : '' }}>
                                                    <x-heroicon-o-chevron-down class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    @if(!$widget['can_view'])
                                        <div class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                                            <x-heroicon-o-exclamation-triangle class="inline h-3 w-3 mr-1" />
                                            You don't have permission to view this widget.
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="bg-neutral-50 dark:bg-neutral-700 px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3">
                    <button wire:click="saveChanges" 
                            class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition-colors">
                        <x-heroicon-o-check class="h-4 w-4 mr-2" />
                        Save Changes
                    </button>
                    
                    <button wire:click="resetToDefaults" 
                            class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-md transition-colors"
                            onclick="return confirm('Are you sure you want to reset to default settings?')">
                        <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                        Reset to Defaults
                    </button>
                    
                    <button wire:click="closeModal" 
                            class="mt-3 sm:mt-0 w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 text-sm font-medium rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
        </div>
    @endif
</div>