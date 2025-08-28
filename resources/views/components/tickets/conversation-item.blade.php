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
            $avatarClass = 'bg-neutral-500'; // Changed from red to gray
            $iconName = 'heroicon-o-exclamation-triangle';
            // Removed dashed border
        } elseif (str_contains($message, 'reopened')) {
            $messageType = 'reopen';
            $avatarClass = 'bg-sky-500';
            $iconName = 'heroicon-o-arrow-path';
            // Removed dashed border
        } elseif (($item->type ?? '') === 'note') {
            $messageType = 'note';
            $avatarClass = 'bg-yellow-500';
            $iconName = 'heroicon-o-document-text';
            // Removed dashed border
        }
    }
    
    // Get the correct sender name
    $senderName = 'System';
    if (!$isSystemMessage) {
        $senderName = $item->sender?->name ?? 'Unknown';
    } elseif (($item->type ?? '') === 'note') {
        // For public notes, show the actual user who created the note
        $senderName = $item->sender?->name ?? 'Unknown';
    }
    
    // Determine if this is a public note (non-internal)
    $isPublicNote = ($item->type ?? '') === 'note';
@endphp

<div class="flex gap-3 {{ $isSystemMessage ? 'py-2' : 'py-4' }} hover:bg-neutral-50 dark:hover:bg-neutral-800/50 rounded-lg transition-colors duration-200 p-2 -m-2">
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
        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-1">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-neutral-800 dark:text-neutral-200">
                    {{ $senderName }}
                </span>
                <span class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ $item->created_at->format('M d, Y \a\t H:i') }}
                </span>
            </div>
            <div class="flex items-center gap-1">
                @if($messageType !== 'default')
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200">
                        {{ ucfirst($messageType) }}
                    </span>
                @elseif($isPublicNote)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200">
                        Note
                    </span>
                @endif
            </div>
        </div>
        
        {{-- Message Body --}}
        <div class="text-sm text-neutral-600 dark:text-neutral-300">
            <div class="{{ $isSystemMessage ? 'italic pb-2' : 'pb-3' }}">
                {!! nl2br(e($item->message)) !!}
            </div>
            
            {{-- Attachments --}}
            @if(!$isSystemMessage && isset($item->attachments) && $item->attachments->count() > 0)
                <div class="mt-3 space-y-2">
                    @foreach($item->attachments as $attachment)
                        @php
                            $extension = strtolower(pathinfo($attachment->original_name, PATHINFO_EXTENSION));
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                            $isPdf = $extension === 'pdf';
                            $canPreview = $isImage || $isPdf;
                        @endphp
                        
                        <div class="flex items-center gap-2 p-2 bg-neutral-50 dark:bg-neutral-700 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-600 transition-colors">
                            @if($isImage)
                                <x-heroicon-o-photo class="h-4 w-4 text-neutral-500" aria-hidden="true" />
                            @elseif($isPdf)
                                <x-heroicon-o-document-text class="h-4 w-4 text-neutral-500" aria-hidden="true" />
                            @else
                                <x-heroicon-o-document class="h-4 w-4 text-neutral-500" aria-hidden="true" />
                            @endif
                            
                            <span class="text-xs text-neutral-600 dark:text-neutral-400 flex-1">
                                {{ $attachment->original_name }}
                                <span class="text-neutral-400 dark:text-neutral-500">
                                    ({{ number_format($attachment->size / 1024, 1) }} KB)
                                </span>
                            </span>
                            
                            <div class="flex items-center gap-1">
                                @if($canPreview)
                                    <button type="button" 
                                            wire:click="$parent.openAttachmentPreview({{ $attachment->id }})"
                                            class="text-sky-500 hover:text-sky-700 dark:hover:text-sky-400"
                                            title="Preview {{ $attachment->original_name }}">
                                        <x-heroicon-o-eye class="h-3 w-3" />
                                    </button>
                                @endif
                                
                                <a href="{{ route('attachments.download', $attachment->uuid) }}" 
                                   class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300"
                                   title="Download {{ $attachment->original_name }}">
                                    <x-heroicon-o-arrow-down-tray class="h-3 w-3" />
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>