@props(['status'])

@php
    // Get the status model to get the proper name and color
    $statusModel = \App\Models\TicketStatus::where('key', $status)->first();
    $statusName = $statusModel ? $statusModel->name : ucfirst(str_replace('_', ' ', $status));
    
    // Get CSS classes using the TicketColorService
    $colorService = app(\App\Services\TicketColorService::class);
    $cssClasses = $colorService->getStatusClasses($status);
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cssClasses }}">
    {{ $statusName }}
</span>
