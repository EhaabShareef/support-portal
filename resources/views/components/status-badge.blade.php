@props(['status'])

@php
    // Get the status model to get the proper name and color
    $statusModel = \App\Models\TicketStatus::where('key', $status)->first();
    $statusName = $statusModel ? $statusModel->name : ucfirst(str_replace('_', ' ', $status));
    $hexColor = $statusModel ? $statusModel->color : '#6b7280';
    
    // Convert hex to RGB for better contrast calculation
    $hex = ltrim($hexColor, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Calculate luminance to determine text color
    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    $textColor = $luminance > 0.5 ? '#000000' : '#ffffff';
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
      style="background-color: {{ $hexColor }}; color: {{ $textColor }};">
    {{ $statusName }}
</span>
