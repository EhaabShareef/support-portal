<div class="h-full p-4 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <x-heroicon-o-calendar-days class="h-4 w-4 text-green-500" />
            <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Monthly Activity</h3>
        </div>
        <button wire:click="refreshData" 
                class="p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
            <x-heroicon-o-arrow-path class="h-3 w-3" />
        </button>
    </div>

    {{-- Widget Content --}}
    @if($hasError)
        <div class="flex items-center justify-center h-32 text-red-500">
            <div class="text-center">
                <x-heroicon-o-exclamation-triangle class="h-6 w-6 mx-auto mb-1" />
                <p class="text-xs">Failed to load</p>
            </div>
        </div>
    @elseif(!$dataLoaded)
        {{-- Loading State --}}
        <div class="space-y-2" aria-busy="true" role="status" aria-label="Loading monthly activity">
            <div class="animate-pulse">
                <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded mb-2"></div>
                <div class="grid grid-cols-5 gap-1">
                    @for($row = 0; $row < 7; $row++)
                        @for($col = 0; $col < 5; $col++)
                            <div class="h-3 bg-neutral-300 dark:bg-neutral-600 rounded"></div>
                        @endfor
                    @endfor
                </div>
            </div>
        </div>
    @else
        @php
            $weeks = collect($heatmapData)->groupBy('week');
            $maxWeeks = 5;
        @endphp
        
        {{-- Heatmap Grid --}}
        <div class="space-y-2">
            {{-- Day Labels --}}
            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-2 text-center">
                {{ now()->format('F Y') }}
            </div>
            
            {{-- Week Grid --}}
            <div class="grid grid-cols-{{ min($weeks->count(), $maxWeeks) }} gap-1">
                @foreach($weeks->take($maxWeeks) as $weekNum => $weekDays)
                    <div class="grid grid-rows-7 gap-1">
                        @for($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++)
                            @php
                                $dayData = $weekDays->first(function($day) use ($dayOfWeek) {
                                    return \Carbon\Carbon::parse($day['date'])->dayOfWeek === $dayOfWeek;
                                });
                                
                                if ($dayData) {
                                    $count = $dayData['count'];
                                    $colorClass = match(true) {
                                        $count === 0 => 'bg-neutral-200 dark:bg-neutral-700',
                                        $count === 1 => 'bg-green-200 dark:bg-green-800',
                                        $count <= 3 => 'bg-green-300 dark:bg-green-700',
                                        $count <= 6 => 'bg-green-400 dark:bg-green-600',
                                        default => 'bg-green-500 dark:bg-green-500'
                                    };
                                    $title = $dayData['date'] . ': ' . $count . ' tickets';
                                } else {
                                    $colorClass = 'bg-transparent';
                                    $title = '';
                                }
                            @endphp
                            <div class="w-3 h-3 rounded {{ $colorClass }}"
                                 @if($title) 
                                     tabindex="0"
                                     title="{{ $title }}"
                                     aria-label="{{ $title }}"
                                 @endif>
                            </div>
                        @endfor
                    </div>
                @endforeach
            </div>
            
            {{-- Legend --}}
            <div class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400 mt-3">
                <span>Less</span>
                <div class="flex gap-1">
                    <div class="w-2 h-2 rounded bg-neutral-200 dark:bg-neutral-700"></div>
                    <div class="w-2 h-2 rounded bg-green-200 dark:bg-green-800"></div>
                    <div class="w-2 h-2 rounded bg-green-300 dark:bg-green-700"></div>
                    <div class="w-2 h-2 rounded bg-green-400 dark:bg-green-600"></div>
                    <div class="w-2 h-2 rounded bg-green-500 dark:bg-green-500"></div>
                </div>
                <span>More</span>
            </div>
        </div>
    @endif
</div>