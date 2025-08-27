@props(['priority'])

@php
    // Get the priority enum to get the proper name and color
    $priorityEnum = \App\Enums\TicketPriority::tryFrom($priority);
    $priorityName = $priorityEnum ? $priorityEnum->label() : ucfirst($priority);
    
    // Get CSS classes using the TicketColorService
    $colorService = app(\App\Services\TicketColorService::class);
    $cssClasses = $colorService->getPriorityClasses($priority);
    
    // Get the icon for the priority
    $icon = $priorityEnum ? $priorityEnum->icon() : 'heroicon-o-arrow-path';
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cssClasses }}">
    <x-dynamic-component :component="$icon" class="h-3 w-3 mr-1" />
    {{ $priorityName }}
</span>
