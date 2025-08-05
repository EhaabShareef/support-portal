@props([
    'type' => 'default', // success, warning, error, default
    'title' => '',
    'message' => '',
])

@php
    $colors = [
        'success' => 'bg-green-100 text-green-800 border-green-300',
        'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'error' => 'bg-red-100 text-red-800 border-red-300',
        'default' => 'bg-neutral-100 text-neutral-800 border-neutral-300',
    ];

    $icons = [
        'success' => 'check-circle',
        'warning' => 'exclamation-triangle',
        'error' => 'x-circle',
        'default' => null,
    ];

    $icon = $icons[$type] ?? null;
    $classes = $colors[$type] ?? $colors['default'];
@endphp

<div class="flex items-center py-2 px-3 border rounded-md mt-1 mb-2 {{ $classes }}">
    @if ($icon)
        <x-dynamic-component :component="'heroicon-o-' . $icon" class="size-6 stroke-1 mr-2 flex-shrink-0" />
    @endif
    <div>
        @if ($title)
            <div class="text-sm font-semibold leading-tight">{{ $title }}</div>
        @endif
        <div class="text-xs">{{ $message }}</div>
    </div>
</div>
