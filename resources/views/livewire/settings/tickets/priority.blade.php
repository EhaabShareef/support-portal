<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-paint-brush class="h-8 w-8" />
                    Priority Color Management
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Customize colors for ticket priority badges</p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="resetToDefaults" 
                    wire:confirm="Are you sure you want to reset all priority colors to their defaults? This cannot be undone."
                    class="inline-flex items-center px-3 py-2 bg-neutral-600 hover:bg-neutral-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                    Reset All
                </button>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if($showFlash)
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
             class="p-4 rounded-lg shadow {{ $flashType === 'success' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200' }}">
            <div class="flex items-center">
                @if($flashType === 'success')
                    <x-heroicon-o-check-circle class="h-5 w-5 mr-2" />
                @else
                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                @endif
                <span>{{ $flashMessage }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Priority List --}}
        <div class="space-y-6">
            {{-- Priority Overview --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="h-8 w-8 bg-purple-100 dark:bg-purple-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-list-bullet class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Priority Overview</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Click on any priority to customize its color</p>
                    </div>
                </div>
                
                <div class="space-y-3">
                    @foreach($this->getPriorityOptions() as $priority => $label)
                        @php
                            $currentColors = $priorityColors[$priority] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
                            // Handle both old string format and new array format
                            if (is_string($currentColors)) {
                                $currentColors = ['bg' => $currentColors, 'text' => $this->ticketColorService->getContrastColor($currentColors)];
                            }
                            $colorDetails = $this->getColorDetails($currentColors);
                            $previewClasses = $this->getPreviewClasses($currentColors);
                        @endphp
                        
                        <div class="flex items-center justify-between p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-all duration-200 cursor-pointer {{ $selectedPriority === $priority ? 'ring-2 ring-sky-500 bg-sky-50 dark:bg-sky-900/20' : '' }}"
                             wire:click="selectPriority('{{ $priority }}')">
                            
                            <div class="flex items-center gap-3">
                                {{-- Priority Icon --}}
                                <div class="h-10 w-10 rounded-lg flex items-center justify-center" style="{{ $colorDetails['bg'] }} {{ $colorDetails['text'] }}">
                                    @switch($priority)
                                        @case('low')
                                            <x-heroicon-o-arrow-down-circle class="h-5 w-5" />
                                            @break
                                        @case('normal')
                                            <x-heroicon-o-arrow-path class="h-5 w-5" />
                                            @break
                                        @case('high')
                                            <x-heroicon-o-arrow-up-circle class="h-5 w-5" />
                                            @break
                                        @case('urgent')
                                            <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
                                            @break
                                        @case('critical')
                                            <x-heroicon-o-fire class="h-5 w-5" />
                                            @break
                                        @default
                                            <x-heroicon-o-flag class="h-5 w-5" />
                                    @endswitch
                                </div>
                                
                                <div>
                                    <h4 class="font-medium text-neutral-800 dark:text-neutral-100 capitalize">{{ $priority }}</h4>
                                    <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $label }}</p>
                                </div>
                            </div>
                            
                            {{-- Current Color Badge --}}
                            <div class="flex items-center gap-2">
                                <div class="h-6 w-6 rounded-full border-2 border-neutral-300 dark:border-neutral-600 flex items-center justify-center" style="background-color: {{ $currentColors['bg'] }};">
                                    <div class="h-3 w-3 rounded-full" style="background-color: {{ $currentColors['text'] }};"></div>
                                </div>
                                <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">BG: {{ $currentColors['bg'] }}</span>
                                <x-heroicon-o-chevron-right class="h-4 w-4 text-neutral-400" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Color Palette Reference --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-8 w-8 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-swatch class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Available Colors</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">All available color options</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($this->getColorPalette() as $hexColor)
                        <div class="flex items-center gap-2 p-2 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-700/50">
                            <div class="h-4 w-4 rounded-full border border-neutral-300 dark:border-neutral-600" style="background-color: {{ $hexColor }};"></div>
                            <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">{{ substr($hexColor, 1) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Color Picker Panel --}}
        <div class="space-y-6">
            @if($showColorPicker && $selectedPriority)
                {{-- Color Selection --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="h-8 w-8 bg-sky-100 dark:bg-sky-900/40 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-paint-brush class="h-4 w-4 text-sky-600 dark:text-sky-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Customize Color</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">Select a color for <span class="font-medium capitalize">{{ $selectedPriority }}</span> priority</p>
                        </div>
                    </div>
                    
                    {{-- Color Preview --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Preview</label>
                        <div class="flex items-center gap-3 p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/50">
                            @php
                                $previewColors = ['bg' => $selectedBgColor, 'text' => $selectedTextColor];
                                $previewColorDetails = $this->getColorDetails($previewColors);
                                $previewClasses = $this->getPreviewClasses($previewColors);
                            @endphp
                            
                            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="{{ $previewColorDetails['bg'] }} {{ $previewColorDetails['text'] }}">
                                @switch($selectedPriority)
                                    @case('low')
                                        <x-heroicon-o-arrow-down-circle class="h-4 w-4" />
                                        @break
                                    @case('normal')
                                        <x-heroicon-o-arrow-path class="h-4 w-4" />
                                        @break
                                    @case('high')
                                        <x-heroicon-o-arrow-up-circle class="h-4 w-4" />
                                        @break
                                    @case('urgent')
                                        <x-heroicon-o-exclamation-triangle class="h-4 w-4" />
                                        @break
                                    @case('critical')
                                        <x-heroicon-o-fire class="h-4 w-4" />
                                        @break
                                    @default
                                        <x-heroicon-o-flag class="h-4 w-4" />
                                @endswitch
                            </div>
                            
                            <span class="px-3 py-1 rounded-full text-sm font-medium capitalize" style="{{ $previewColorDetails['bg'] }} {{ $previewColorDetails['text'] }}">
                                {{ $selectedPriority }} Priority
                            </span>
                        </div>
                    </div>
                    
                    {{-- Custom Color Pickers --}}
                    <div class="mb-6 space-y-4">
                        {{-- Background Color --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Background Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" 
                                       wire:model="customBgColor" 
                                       wire:change="setCustomBgColor($event.target.value)"
                                       class="h-10 w-16 rounded-lg border border-neutral-300 dark:border-neutral-600 cursor-pointer"
                                       value="{{ $customBgColor }}">
                                <input type="text" 
                                       wire:model="customBgColor" 
                                       wire:change="setCustomBgColor($event.target.value)"
                                       class="flex-1 px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200"
                                       placeholder="#f3f4f6">
                            </div>
                        </div>

                        {{-- Text Color --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Text Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" 
                                       wire:model="customTextColor" 
                                       wire:change="setCustomTextColor($event.target.value)"
                                       class="h-10 w-16 rounded-lg border border-neutral-300 dark:border-neutral-600 cursor-pointer"
                                       value="{{ $customTextColor }}">
                                <input type="text" 
                                       wire:model="customTextColor" 
                                       wire:change="setCustomTextColor($event.target.value)"
                                       class="flex-1 px-3 py-2 border border-neutral-300 dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200"
                                       placeholder="#374151">
                            </div>
                        </div>
                    </div>

                    {{-- Quick Color Presets --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Quick Color Presets</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @php
                                $colorPresets = [
                                    ['bg' => '#f3f4f6', 'text' => '#374151', 'name' => 'Gray'],
                                    ['bg' => '#dbeafe', 'text' => '#1e40af', 'name' => 'Blue'],
                                    ['bg' => '#fed7aa', 'text' => '#c2410c', 'name' => 'Orange'],
                                    ['bg' => '#fecaca', 'text' => '#dc2626', 'name' => 'Red'],
                                    ['bg' => '#dcfce7', 'text' => '#166534', 'name' => 'Green'],
                                    ['bg' => '#fef3c7', 'text' => '#a16207', 'name' => 'Yellow'],
                                    ['bg' => '#f3e8ff', 'text' => '#7c3aed', 'name' => 'Purple'],
                                    ['bg' => '#fce7f3', 'text' => '#be185d', 'name' => 'Pink'],
                                    ['bg' => '#ecfdf5', 'text' => '#047857', 'name' => 'Emerald'],
                                ];
                            @endphp
                            @foreach($colorPresets as $preset)
                                <button type="button" 
                                        wire:click="setCustomBgColor('{{ $preset['bg'] }}'); setCustomTextColor('{{ $preset['text'] }}')"
                                        class="relative p-3 rounded-lg border-2 transition-all duration-200 hover:scale-105 {{ $selectedBgColor === $preset['bg'] && $selectedTextColor === $preset['text'] ? 'border-sky-500 ring-2 ring-sky-200 dark:ring-sky-800' : 'border-neutral-200 dark:border-neutral-700 hover:border-neutral-300 dark:hover:border-neutral-600' }}">
                                    
                                    <div class="h-8 w-8 rounded-lg mx-auto flex items-center justify-center" style="background-color: {{ $preset['bg'] }};">
                                        <span class="text-xs font-bold" style="color: {{ $preset['text'] }};">Aa</span>
                                    </div>
                                    
                                    <div class="mt-2 text-center">
                                        <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">{{ $preset['name'] }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-3">
                        <button type="button" 
                                wire:click="updatePriorityColor"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                            <x-heroicon-o-check class="h-4 w-4 mr-2" />
                            Apply Color
                        </button>
                        <button type="button" 
                                wire:click="$set('showColorPicker', false)"
                                class="inline-flex items-center px-3 py-2 bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-300 text-sm font-medium rounded-md transition-all duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            @else
                {{-- Instructions Panel --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-information-circle class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">How to Customize</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">Follow these steps to customize priority colors</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="h-6 w-6 bg-sky-100 dark:bg-sky-900/40 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-sky-600 dark:text-sky-400">1</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Select Priority</h4>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">Click on any priority from the list to start customizing</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="h-6 w-6 bg-sky-100 dark:bg-sky-900/40 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-sky-600 dark:text-sky-400">2</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Choose Color</h4>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">Pick from the available color palette and see a live preview</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="h-6 w-6 bg-sky-100 dark:bg-sky-900/40 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-sky-600 dark:text-sky-400">3</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Apply Changes</h4>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">Click "Apply Color" to save your changes immediately</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Current Colors Summary --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-8 w-8 bg-green-100 dark:bg-green-900/40 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-eye class="h-4 w-4 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Current Colors</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">Overview of all priority colors</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        @foreach($this->getPriorityOptions() as $priority => $label)
                            @php
                                $currentColors = $priorityColors[$priority] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
                                // Handle both old string format and new array format
                                if (is_string($currentColors)) {
                                    $currentColors = ['bg' => $currentColors, 'text' => $this->ticketColorService->getContrastColor($currentColors)];
                                }
                            @endphp
                            <div class="flex items-center justify-between py-2">
                                <span class="text-sm text-neutral-700 dark:text-neutral-300 capitalize">{{ $priority }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 rounded-full border border-neutral-300 dark:border-neutral-600 flex items-center justify-center" style="background-color: {{ $currentColors['bg'] }};">
                                        <div class="h-2 w-2 rounded-full" style="background-color: {{ $currentColors['text'] }};"></div>
                                    </div>
                                    <span class="text-xs font-medium text-neutral-600 dark:text-neutral-400">{{ $currentColors['bg'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
