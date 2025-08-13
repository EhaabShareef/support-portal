@props(['ticket', 'editMode', 'canEdit'])

<div class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50 mb-6">
    <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-800 dark:text-neutral-200">
                    {{ $ticket->subject }}
                </h1>
                <div class="flex items-center gap-4 mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    <span>Ticket #{{ $ticket->ticket_number }}</span>
                    <span>•</span>
                    <span>Created {{ $ticket->created_at->format('M d, Y \a\t H:i') }}</span>
                    @if($ticket->updated_at->ne($ticket->created_at))
                        <span>•</span>
                        <span>Updated {{ $ticket->updated_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-3">
                @if($ticket->status !== 'closed')
                    @if(!$editMode && $canEdit)
                        <button wire:click="enableEdit" 
                                class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <x-heroicon-o-pencil class="h-4 w-4 mr-2" />
                            Edit
                        </button>
                    @endif

                    @if(!$ticket->owner_id && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('support')))
                        <button wire:click="assignToMe" 
                                class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            <x-heroicon-o-user-plus class="h-4 w-4 mr-2" />
                            Assign to Me
                        </button>
                    @endif

                    @if(auth()->user()->can('tickets.update'))
                        <button wire:click="openCloseModal" 
                                class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            <x-heroicon-o-x-circle class="h-4 w-4 mr-2" />
                            Close Ticket
                        </button>
                    @endif
                @else
                    {{-- Reopen button for closed tickets --}}
                    @php
                        $canReopen = auth()->user()->hasRole(['admin', 'support']) || 
                                   (auth()->user()->hasRole('client') && 
                                    auth()->user()->organization_id === $ticket->organization_id &&
                                    $ticket->closed_at && 
                                    floor(now()->diffInDays($ticket->closed_at)) <= (app(\App\Contracts\SettingsRepositoryInterface::class)->get('tickets.reopen_window_days', 3)));
                    @endphp
                    @if($canReopen)
                        <button wire:click="changeStatus('open')" 
                                class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                            Reopen Ticket
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>