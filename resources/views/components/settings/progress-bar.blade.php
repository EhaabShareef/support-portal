@props([
    'progress' => 0,
    'status' => 'pending', // pending, in_progress, completed
    'showPercentage' => true,
    'showStatus' => true,
    'size' => 'md', // sm, md, lg
    'animated' => true,
])

@php
    $sizeClasses = [
        'sm' => 'h-2',
        'md' => 'h-3',
        'lg' => 'h-4',
    ];
    
    $statusColors = [
        'pending' => 'bg-neutral-200 dark:bg-neutral-700',
        'in_progress' => 'bg-sky-200 dark:bg-sky-800',
        'completed' => 'bg-green-200 dark:bg-green-800',
    ];
    
    $progressColors = [
        'pending' => 'bg-neutral-400 dark:bg-neutral-500',
        'in_progress' => 'bg-sky-500 dark:bg-sky-400',
        'completed' => 'bg-green-500 dark:bg-green-400',
    ];
    
    $statusLabels = [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
    ];
    
    $statusIcons = [
        'pending' => 'heroicon-o-clock',
        'in_progress' => 'heroicon-o-arrow-path',
        'completed' => 'heroicon-o-check-circle',
    ];
@endphp

<div class="space-y-2">
    <div class="flex items-center justify-between">
        @if($showStatus)
            <div class="flex items-center gap-2">
                <x-dynamic-component :component="$statusIcons[$status]" 
                    class="h-4 w-4 {{ $status === 'completed' ? 'text-green-500' : ($status === 'in_progress' ? 'text-sky-500' : 'text-neutral-400') }}" />
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    {{ $statusLabels[$status] }}
                </span>
            </div>
        @endif
        
        @if($showPercentage)
            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                {{ $progress }}%
            </span>
        @endif
    </div>
    
    <div class="w-full {{ $statusColors[$status] }} rounded-full {{ $sizeClasses[$size] }} overflow-hidden">
        <div 
            class="{{ $progressColors[$status] }} {{ $sizeClasses[$size] }} rounded-full transition-all duration-500 ease-out {{ $animated ? 'animate-pulse' : '' }}"
            style="width: {{ $progress }}%"
        ></div>
    </div>
</div>
