@props(['ticket'])

@if($ticket->department->departmentGroup && $ticket->department->departmentGroup->name === 'Hardware' && $ticket->hardware->count() > 0)
    @php
        $progress = $ticket->hardware_progress;
    @endphp
    
    <div class="bg-gradient-to-r from-sky-50 to-blue-50 dark:from-sky-900/20 dark:to-blue-900/20 border border-sky-200 dark:border-sky-700 rounded-lg p-4 mb-4">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-semibold text-sky-900 dark:text-sky-100 flex items-center">
                <x-heroicon-o-cpu-chip class="h-4 w-4 mr-2" />
                Hardware Progress
            </h4>
            <div class="text-xs text-sky-700 dark:text-sky-300">
                {{ $progress['percentage'] }}% Complete
            </div>
        </div>
        
        {{-- Progress Bar --}}
        <div class="w-full bg-sky-200 dark:bg-sky-800 rounded-full h-3 mb-3">
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 h-3 rounded-full transition-all duration-500 ease-out" 
                 style="width: {{ $progress['percentage'] }}%"></div>
        </div>
        
        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <div class="text-lg font-bold text-sky-900 dark:text-sky-100">{{ $progress['total'] }}</div>
                <div class="text-xs text-sky-600 dark:text-sky-400">Total Units</div>
            </div>
            <div>
                <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ $progress['fixed'] }}</div>
                <div class="text-xs text-green-600 dark:text-green-400">Fixed</div>
            </div>
            <div>
                <div class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $progress['pending'] }}</div>
                <div class="text-xs text-amber-600 dark:text-amber-400">Pending</div>
            </div>
        </div>
        
        {{-- Quick Actions --}}
        @can('update', $ticket)
            <div class="mt-3 pt-3 border-t border-sky-200 dark:border-sky-700">
                <button wire:click="$dispatch('link-hardware:toggle')" 
                        class="text-xs text-sky-700 dark:text-sky-300 hover:text-sky-900 dark:hover:text-sky-100 font-medium">
                    Update Progress →
                </button>
            </div>
        @endcan
    </div>
@endif
