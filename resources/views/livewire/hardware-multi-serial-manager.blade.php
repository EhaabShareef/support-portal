<div class="space-y-6 p-6">
    {{-- Header --}}
    <div class="text-center mb-6">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 mb-2">
            Serial Number Assignment
        </h3>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
            Add serial numbers for hardware items that require them
        </p>
    </div>

    {{-- Global Messages --}}
    @if (session()->has('error'))
        <div class="bg-red-100 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-md text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Hardware Items Requiring Serials --}}
    @if(empty($hardwareItems))
        <div class="text-center py-8">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-green-100 dark:bg-green-900/20 rounded-full">
                <x-heroicon-o-check-circle class="h-8 w-8 text-green-600 dark:text-green-400" />
            </div>
            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">No Serial Numbers Required</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">None of the added hardware items require serial numbers.</p>
            <button wire:click="skipToComplete" 
                    class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                Continue to Completion
            </button>
        </div>
    @else
        <div class="space-y-6">
            @foreach($hardwareItems as $index => $hardware)
                <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md overflow-hidden">
                    {{-- Hardware Header --}}
                    <div class="bg-gradient-to-r from-neutral-50 to-neutral-100 dark:from-neutral-800 dark:to-neutral-700 px-6 py-4 border-b border-neutral-200 dark:border-neutral-600">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <h4 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                                        {{ $hardware['type_name'] }}
                                    </h4>
                                    @if($this->isHardwareComplete($index))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300">
                                            <x-heroicon-o-check class="h-3 w-3 mr-1" />
                                            Complete
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300">
                                            <x-heroicon-o-clock class="h-3 w-3 mr-1" />
                                            In Progress
                                        </span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap gap-4 text-sm text-neutral-600 dark:text-neutral-400">
                                    @if($hardware['brand'])
                                        <span><strong>Brand:</strong> {{ $hardware['brand'] }}</span>
                                    @endif
                                    @if($hardware['model'])
                                        <span><strong>Model:</strong> {{ $hardware['model'] }}</span>
                                    @endif
                                    <span><strong>Quantity:</strong> {{ $hardware['quantity'] }}</span>
                                    <span><strong>Serials Added:</strong> {{ count($existingSerials[$index] ?? []) }}/{{ $hardware['quantity'] }}</span>
                                </div>
                                @if($hardware['remarks'])
                                    <div class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                                        <strong>Remarks:</strong> {{ $hardware['remarks'] }}
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Progress Ring --}}
                            <div class="relative w-16 h-16">
                                @php
                                    $progress = count($existingSerials[$index] ?? []);
                                    $total = $hardware['quantity'];
                                    $percentage = $total > 0 ? ($progress / $total) * 100 : 0;
                                    $circumference = 2 * 3.14159 * 22; // radius = 22
                                    $strokeDashoffset = $circumference - ($percentage / 100) * $circumference;
                                @endphp
                                <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 48 48">
                                    <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="4" fill="none" class="text-neutral-300 dark:text-neutral-600" />
                                    <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="4" fill="none" 
                                            class="{{ $this->isHardwareComplete($index) ? 'text-green-500' : 'text-sky-500' }}"
                                            stroke-linecap="round"
                                            stroke-dasharray="{{ $circumference }}"
                                            stroke-dashoffset="{{ $strokeDashoffset }}"
                                            style="transition: stroke-dashoffset 0.5s ease;" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-sm font-semibold {{ $this->isHardwareComplete($index) ? 'text-green-600 dark:text-green-400' : 'text-sky-600 dark:text-sky-400' }}">
                                        {{ $progress }}/{{ $total }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Serial Management Content --}}
                    <div class="p-6">
                        {{-- Hardware-specific Messages --}}
                        @if (session()->has("success_$index"))
                            <div class="mb-4 bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-md text-sm">
                                {{ session("success_$index") }}
                            </div>
                        @endif

                        @if (session()->has("error_$index"))
                            <div class="mb-4 bg-red-100 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-md text-sm">
                                {{ session("error_$index") }}
                            </div>
                        @endif

                        {{-- Existing Serials --}}
                        @if(!empty($existingSerials[$index]))
                            <div class="mb-4">
                                <h5 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Added Serial Numbers:</h5>
                                <div class="space-y-2">
                                    @foreach($existingSerials[$index] as $serialId => $serial)
                                        <div class="flex items-center justify-between px-3 py-2 bg-neutral-100 dark:bg-neutral-800 rounded-md">
                                            <span class="font-mono text-sm text-neutral-900 dark:text-neutral-100">{{ $serial }}</span>
                                            <button wire:click="removeSerial({{ $index }}, {{ $serialId }})"
                                                    class="p-1 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors duration-200">
                                                <x-heroicon-o-trash class="h-4 w-4" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Add New Serial Form --}}
                        @if(!$this->isHardwareComplete($index))
                            <form wire:submit.prevent="addSerial({{ $index }})" class="flex gap-3">
                                <div class="flex-1">
                                    <input type="text" 
                                           wire:model="serialInputs.{{ $index }}" 
                                           placeholder="Enter serial number"
                                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 font-mono">
                                    @error("serialInputs.$index")
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <button type="submit" 
                                        class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-colors duration-200 whitespace-nowrap">
                                    <x-heroicon-o-plus class="inline h-4 w-4 mr-1" />
                                    Add Serial
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Continue Button --}}
        <div class="flex items-center justify-between pt-6 border-t border-neutral-200 dark:border-neutral-700">
            <div class="text-sm text-neutral-600 dark:text-neutral-400">
                @if($this->allHardwareComplete())
                    All hardware serial numbers have been added.
                @else
                    @php
                        $totalComplete = 0;
                        $totalRequired = count($hardwareItems);
                        foreach($hardwareItems as $index => $hardware) {
                            if($this->isHardwareComplete($index)) {
                                $totalComplete++;
                            }
                        }
                    @endphp
                    {{ $totalComplete }}/{{ $totalRequired }} hardware items completed
                @endif
            </div>
            
            <div class="flex items-center gap-3">
                <button wire:click="skipToComplete" 
                        class="px-4 py-2 bg-neutral-500 hover:bg-neutral-600 text-white text-sm rounded-md transition-colors duration-200">
                    Skip & Continue
                </button>
                
                <button wire:click="continue" 
                        class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-colors duration-200 {{ $this->allHardwareComplete() ? '' : 'opacity-75' }}">
                    @if($this->allHardwareComplete())
                        Complete Setup
                    @else
                        Continue (Incomplete)
                    @endif
                    <x-heroicon-o-arrow-right class="inline h-4 w-4 ml-1" />
                </button>
            </div>
        </div>
    @endif
</div>
