<div class="h-full p-4 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md overflow-x-auto">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <x-heroicon-o-calendar-days class="h-4 w-4 text-green-500" />
            <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Yearly Activity</h3>
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
        <div class="space-y-2" aria-busy="true" role="status" aria-label="Loading yearly activity">
            <div class="animate-pulse">
                <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded mb-2"></div>
                <div class="grid grid-cols-12 gap-1">
                    @for($row = 0; $row < 7; $row++)
                        @for($col = 0; $col < 52; $col++)
                            <div class="h-2 bg-neutral-300 dark:bg-neutral-600 rounded"></div>
                        @endfor
                    @endfor
                </div>
            </div>
        </div>
    @else
        @php
            $weeks = collect($heatmapData)->groupBy('weekOfYear');
            $maxWeeks = min(53, $weeks->count());
        @endphp
        
        {{-- Heatmap Grid --}}
        <div class="space-y-2">
            {{-- Year Label --}}
            <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-2 text-center">
                {{ now()->format('Y') }}
            </div>
            
            {{-- Month Labels --}}
            <div class="grid grid-cols-{{ $maxWeeks }} gap-px text-xs text-neutral-500 dark:text-neutral-400 mb-1" style="grid-template-columns: repeat({{ $maxWeeks }}, minmax(0, 1fr));">
                @php
                    $currentMonth = '';
                    $monthPositions = [];
                @endphp
                @foreach($weeks->take($maxWeeks) as $weekNum => $weekDays)
                    @php
                        $firstDay = $weekDays->first();
                        $month = \Carbon\Carbon::parse($firstDay['date'])->format('M');
                        if ($month !== $currentMonth && \Carbon\Carbon::parse($firstDay['date'])->day <= 7) {
                            $currentMonth = $month;
                            echo '<div class="text-center">' . $month . '</div>';
                        } else {
                            echo '<div></div>';
                        }
                    @endphp
                @endforeach
            </div>
            
            {{-- Day Labels --}}
            <div class="flex items-start gap-1">
                <div class="grid grid-rows-7 gap-px text-xs text-neutral-500 dark:text-neutral-400 mr-1">
                    <div></div><div>Mon</div><div></div><div>Wed</div><div></div><div>Fri</div><div></div>
                </div>
                
                {{-- Week Grid --}}
                <div class="grid grid-cols-{{ $maxWeeks }} gap-px flex-1" style="grid-template-columns: repeat({{ $maxWeeks }}, minmax(0, 1fr));">
                    @foreach($weeks->take($maxWeeks) as $weekNum => $weekDays)
                        <div class="grid grid-rows-7 gap-px">
                            @for($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++)
                                @php
                                    $dayData = $weekDays->first(function($day) use ($dayOfWeek) {
                                        return $day['dayOfWeek'] === $dayOfWeek;
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
                                <div class="w-2 h-2 rounded {{ $colorClass }}"
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