@props(['ticket'])

@if($ticket->notes && $ticket->notes->where('is_internal', true)->count() > 0)
    <div class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50 mb-6">
        <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Internal Notes</h3>
        </div>
        
        <div class="px-6 py-4 space-y-4">
            @foreach($ticket->notes->where('is_internal', true) as $note)
                <div class="flex gap-3 py-3 border-b border-neutral-200 dark:border-neutral-700 last:border-0 last:pb-0">
                    {{-- Avatar --}}
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 rounded-full bg-orange-500 flex items-center justify-center">
                            <span class="text-xs font-medium text-white">
                                {{ substr($note->user->name, 0, 1) }}
                            </span>
                        </div>
                    </div>
                    
                    {{-- Note Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-neutral-800 dark:text-neutral-200">
                                {{ $note->user->name }}
                            </span>
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                {{ $note->created_at->format('M d, Y \a\t H:i') }}
                            </span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200">
                                Internal
                            </span>
                        </div>
                        
                        <div class="text-sm text-neutral-600 dark:text-neutral-300">
                            {!! nl2br(e($note->note)) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif