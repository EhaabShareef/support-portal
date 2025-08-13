<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('tickets.index') }}" 
                   class="inline-flex items-center text-sm text-neutral-600 dark:text-neutral-300 hover:text-neutral-800 dark:hover:text-neutral-100 hover:underline transition-colors duration-200">
                    <x-heroicon-o-arrow-left class="h-4 w-4 mr-1" />
                    Back to Tickets
                </a>
                <div class="hidden sm:block w-px h-6 bg-neutral-300 dark:bg-neutral-600"></div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100">{{ $ticket->subject }}</h1>
                    <div class="flex items-center gap-4 mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                        <span>Ticket #{{ $ticket->ticket_number }}</span>
                        <span>•</span>
                        <span>{{ $ticket->client->name }} ({{ $ticket->organization->name }})</span>
                        <span>•</span>
                        <span>Created {{ $ticket->created_at->format('M d, Y \a\t H:i') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                {{-- Status Badge --}}
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \App\Enums\TicketStatus::from($ticket->status)->cssClass() }}">
                    {{ \App\Enums\TicketStatus::from($ticket->status)->label() }}
                </span>
                
                {{-- Priority Badge --}}
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \App\Enums\TicketPriority::from($ticket->priority)->cssClass() }}">
                    <x-dynamic-component :component="\App\Enums\TicketPriority::from($ticket->priority)->icon()" class="h-3 w-3 mr-1" />
                    {{ \App\Enums\TicketPriority::from($ticket->priority)->label() }}
                </span>

                {{-- Action Buttons --}}
                @if($ticket->status !== 'closed')
                    @if(!$editMode && $this->canEdit)
                        <button wire:click="enableEdit" 
                                class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition-all duration-200">
                            <x-heroicon-o-pencil class="h-3 w-3 mr-1" />
                            Edit
                        </button>
                    @endif

                    @if(!$ticket->owner_id && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('support')))
                        <button wire:click="assignToMe" 
                                class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition-all duration-200">
                            <x-heroicon-o-user-plus class="h-3 w-3 mr-1" />
                            Assign to Me
                        </button>
                    @endif

                    @if(auth()->user()->can('tickets.update'))
                        <button wire:click="openCloseModal" 
                                class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition-all duration-200">
                            <x-heroicon-o-x-circle class="h-3 w-3 mr-1" />
                            Close
                        </button>
                    @endif
                @else
                    {{-- Reopen button for closed tickets --}}
                    @php
                        $canReopen = auth()->user()->hasRole(['admin', 'support']) || 
                                   (auth()->user()->hasRole('client') && 
                                    auth()->user()->organization_id === $ticket->organization_id &&
                                    $ticket->closed_at && 
                                    floor(now()->diffInDays($ticket->closed_at)) <= (\App\Models\Setting::get('tickets.reopen_window_days', 3)));
                    @endphp
                    @if($canReopen)
                        <button wire:click="changeStatus('open')" 
                                class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition-all duration-200">
                            <x-heroicon-o-arrow-path class="h-3 w-3 mr-1" />
                            Reopen
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Left Column: Details, Contract, Organization Notes, Internal Notes --}}
        <div class="xl:col-span-1 space-y-6">
            {{-- Ticket Details --}}
            <x-tickets.details 
                :ticket="$ticket" 
                :editMode="$editMode" 
                :form="$form"
                :departments="$departments"
                :users="$users" 
                :statusOptions="$statusOptions"
                :priorityOptions="$priorityOptions" />

            {{-- Active Contract (if available) --}}
            @if($this->activeContract)
                <div class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-document-text class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Active Contract</h3>
                        </div>
                    </div>
                    <div class="px-6 py-4">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Contract #:</span>
                                <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ $this->activeContract->contract_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Type:</span>
                                <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ ucfirst($this->activeContract->type) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Value:</span>
                                <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ $this->activeContract->currency }} {{ number_format($this->activeContract->contract_value, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Period:</span>
                                <span class="text-sm text-neutral-600 dark:text-neutral-400">{{ $this->activeContract->start_date->format('M d, Y') }} - {{ $this->activeContract->end_date->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Status:</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                    {{ ucfirst($this->activeContract->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Organization Notes (if available) --}}
            @if($ticket->organization->notes)
                <div class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-building-office class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Organization Notes</h3>
                        </div>
                    </div>
                    <div class="px-6 py-4">
                        <div class="prose prose-sm dark:prose-invert max-w-none">
                            {!! nl2br(e($ticket->organization->notes)) !!}
                        </div>
                    </div>
                </div>
            @endif

            {{-- Internal Notes --}}
            <x-tickets.notes :ticket="$ticket" :canAddNotes="$this->canAddNotes" />
        </div>

        {{-- Right Column: Conversation --}}
        <div class="xl:col-span-2">
            @if($ticket->status !== 'closed')
                <livewire:tickets.ticket-conversation :ticket="$ticket" :key="$ticket->id" />
            @else
                {{-- Read-only conversation for closed tickets --}}
                <div class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50">
                    <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Conversation</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                Ticket Closed
                            </span>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 space-y-4">
                        @forelse($ticket->conversation ?? [] as $item)
                            <div class="border-b border-neutral-200 dark:border-neutral-700 pb-4 last:border-b-0 last:pb-0">
                                <x-tickets.conversation-item :item="$item" :ticket="$ticket" />
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <x-heroicon-o-chat-bubble-left-right class="mx-auto h-12 w-12 text-neutral-400" />
                                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-300">No messages</h3>
                                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">This ticket has no conversation history.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Close Modal --}}
    <x-tickets.close-modal :showCloseModal="$showCloseModal" :closeForm="$closeForm" />

    {{-- Priority escalation confirmation script --}}
    <script>
        window.confirmTicketUpdate = function(event, currentPriority) {
            const prioritySelect = document.getElementById('prioritySelect');
            if (!prioritySelect) return true;
            
            const newPriority = prioritySelect.value;
            
            // Priority hierarchy for comparison
            const priorityLevels = {
                'low': 1,
                'normal': 2,
                'high': 3,
                'urgent': 4,
                'critical': 5
            };
            
            const isEscalation = priorityLevels[newPriority] > priorityLevels[currentPriority];
            
            if (isEscalation) {
                // Show confirmation for escalation
                const priorityLabels = {
                    'low': 'Low',
                    'normal': 'Normal',
                    'high': 'High',
                    'urgent': 'Urgent',
                    'critical': 'Critical'
                };
                
                if (!confirm(`Are you sure you want to escalate this ticket's priority to ${priorityLabels[newPriority]}?\n\nThis action will be logged for audit purposes.`)) {
                    event.preventDefault();
                    return false;
                }
            }
            
            return true;
        };
    </script>
</div>