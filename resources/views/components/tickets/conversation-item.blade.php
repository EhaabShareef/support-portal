@props(['item', 'ticket'])

@php
    $isSystemMessage = $item->is_system_message ?? false;
    $messageType = 'default';
    $avatarClass = 'bg-neutral-500';
    $iconName = 'heroicon-o-chat-bubble-left-right';
    $borderClass = '';
    
    // Determine system message type and styling
    if ($isSystemMessage) {
        $message = strtolower($item->message);
        if (str_contains($message, 'closed')) {
            $messageType = 'closing';
            $avatarClass = 'bg-red-500';
            $iconName = 'heroicon-o-exclamation-triangle';
            $borderClass = 'border-b border-red-500 border-dashed';
        } elseif (str_contains($message, 'reopened')) {
            $messageType = 'reopen';
            $avatarClass = 'bg-sky-500';
            $iconName = 'heroicon-o-arrow-path';
            $borderClass = 'border-b border-sky-500 border-dashed';
        } elseif (($item->type ?? '') === 'note') {
            $messageType = 'note';
            $avatarClass = 'bg-yellow-500';
            $iconName = 'heroicon-o-document-text';
            $borderClass = 'border-b border-yellow-500 border-dashed';
        }
    }
@endphp

<div class="flex gap-3 {{ $isSystemMessage ? 'py-2' : 'py-4' }}">
    {{-- Avatar --}}
    <div class="flex-shrink-0">
        @if($isSystemMessage)
            <div class="h-8 w-8 rounded-full {{ $avatarClass }} flex items-center justify-center">
                <x-dynamic-component :component="$iconName" class="h-4 w-4 text-white" />
            </div>
        @else
            <div class="h-8 w-8 rounded-full bg-gradient-to-r from-sky-400 to-blue-500 flex items-center justify-center">
                <span class="text-xs font-medium text-white">
                    {{ substr($item->sender?->name ?? 'U', 0, 1) }}
                </span>
            </div>
        @endif
    </div>
    
    {{-- Message Content --}}
    <div class="flex-1 min-w-0">
        {{-- Header --}}
        <div class="flex items-center gap-2 mb-1">
            <span class="text-sm font-medium text-neutral-800 dark:text-neutral-200">
                {{ $item->sender?->name ?? 'System' }}
            </span>
            <span class="text-xs text-neutral-500 dark:text-neutral-400">
                {{ $item->created_at->format('M d, Y \a\t H:i') }}
            </span>
            @if($messageType !== 'default')
                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200">
                    {{ ucfirst($messageType) }}
                </span>
            @endif
        </div>
        
        {{-- Message Body --}}
        <div class="text-sm text-neutral-600 dark:text-neutral-300 {{ $borderClass }}">
            <div class="{{ $isSystemMessage ? 'italic pb-2' : 'pb-3' }}">
                {!! nl2br(e($item->message)) !!}
            </div>
            
            {{-- Attachments --}}
            @if(!$isSystemMessage && isset($item->attachments) && $item->attachments->count() > 0)
                <div class="mt-3 space-y-2">
                    @foreach($item->attachments as $attachment)
                        <div class="flex items-center gap-2 p-2 bg-neutral-50 dark:bg-neutral-700 rounded-md">
                            @if($attachment->is_image)
                                <x-heroicon-o-photo class="h-4 w-4 text-neutral-500" />
                            @else
                                <x-heroicon-o-document class="h-4 w-4 text-neutral-500" />
                            @endif
                            <span class="text-xs text-neutral-600 dark:text-neutral-400">
                                {{ $attachment->original_name }}
                                <span class="text-neutral-400 dark:text-neutral-500">
                                    ({{ number_format($attachment->size / 1024, 1) }} KB)
                                </span>
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>