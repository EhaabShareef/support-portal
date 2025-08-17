<div id="messages-start" class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50">
    <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Conversation</h3>
    </div>
    <div class="px-6 py-4 space-y-4 max-h-[500px] overflow-y-auto">
        @forelse($ticket->conversation ?? [] as $item)
            <div wire:key="thread-{{ $item->type }}-{{ $item->id }}" class="@if(!($item->is_system_message ?? false)) border-b border-neutral-200 dark:border-neutral-700 pb-4 last:border-b-0 last:pb-0 @endif">
                <x-ticket.reply-bubble :item="$item" />
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
