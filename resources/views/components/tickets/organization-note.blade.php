@props(['ticket'])

@if($ticket->organization && $ticket->organization->notes)
    <div class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50 mb-6">
        <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Organization Notes</h3>
        </div>
        
        <div class="px-6 py-4">
            <div class="text-sm text-neutral-600 dark:text-neutral-300">
                {!! nl2br(e($ticket->organization->notes)) !!}
            </div>
        </div>
    </div>
@endif