<div>
    {{-- Hardware Progress (only for Hardware department tickets) --}}
    <x-tickets.hardware-progress :ticket="$ticket" />
    
    <div id="messages-start" class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50">
        <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Conversation</h3>
        </div>
        <div class="px-6 py-4 space-y-1 max-h-[500px] overflow-y-auto custom-scrollbar scrollbar-on-hover">
            @forelse($ticket->conversation ?? [] as $item)
                <div wire:key="thread-{{ $item->type }}-{{ $item->id }}">
                    <x-tickets.conversation-item :item="$item" :ticket="$ticket" />
                </div>
            @empty
                <div class="text-center py-12">
                    <x-heroicon-o-chat-bubble-left-right class="mx-auto h-12 w-12 text-neutral-400" />
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-300">No messages yet</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Start the conversation by sending a reply.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
