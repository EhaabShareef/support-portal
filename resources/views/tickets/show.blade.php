<div class="space-y-6">
    {{-- Header Section --}}
    <div
        class="rounded-xl border border-white/20 dark:border-white/10 bg-white/50 dark:bg-neutral-900/40 backdrop-blur-md shadow-sm">
        <div class="p-4 md:p-6">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('tickets.index') }}"
                            class="inline-flex items-center gap-1.5 text-xs sm:text-sm text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-neutral-100 hover:underline transition-colors duration-200 mb-3">
                            <x-heroicon-o-arrow-left class="h-4 w-4" />
                            Back to Tickets
                        </a>

                        <h1
                            class="text-lg sm:text-xl md:text-2xl lg:text-3xl font-semibold tracking-tight text-neutral-900 dark:text-neutral-100 mb-2 break-words">
                            {{ $ticket->subject }}
                        </h1>

                        <div
                            class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-xs sm:text-sm text-neutral-600 dark:text-neutral-400">
                            <span>Ticket #{{ $ticket->ticket_number }}</span>
                            <span class="hidden sm:block">•</span>
                            <span class="break-words">{{ $ticket->client->name }}
                                ({{ $ticket->organization->name }})</span>
                            <span class="hidden sm:block">•</span>
                            <span>Created {{ $ticket->created_at->format('M d, Y \a\t H:i') }}</span>
                        </div>
                    </div>

                    {{-- Status & Priority Badges --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-white/60 dark:bg-white/10 border border-white/30 dark:border-white/10 text-neutral-700 dark:text-neutral-200">
                            <x-heroicon-o-tag class="h-3.5 w-3.5" />
                            {{ \App\Enums\TicketStatus::from($ticket->status)->label() }}
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-white/60 dark:bg-white/10 border border-white/30 dark:border-white/10 text-neutral-700 dark:text-neutral-200">
                            <x-dynamic-component :component="\App\Enums\TicketPriority::from($ticket->priority)->icon()" class="h-3.5 w-3.5" />
                            {{ \App\Enums\TicketPriority::from($ticket->priority)->label() }}
                        </span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-2">
                    @if ($ticket->status !== 'closed')
                        @if (!$editMode && $this->canEdit)
                            <button wire:click="enableEdit"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-xs sm:text-sm font-medium
                                       bg-white/60 dark:bg-white/10 border border-neutral-500/30 dark:border-white/10
                                       text-neutral-800 dark:text-neutral-100 hover:bg-sky-200/80 dark:hover:bg-sky-500/15
                                       focus:outline-none focus:ring-2 focus:ring-neutral-200/40 transition">
                                <x-heroicon-o-pencil class="h-4 w-4" />
                                Edit
                            </button>
                        @endif

                        @if (!$ticket->owner_id && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('support')))
                            <button wire:click="assignToMe"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-xs sm:text-sm font-medium
                                       bg-white/60 dark:bg-white/10 border border-white/30 dark:border-white/10
                                       text-neutral-800 dark:text-neutral-100 hover:bg-white/80 dark:hover:bg-white/15
                                       focus:outline-none focus:ring-2 focus:ring-neutral-400/40 transition">
                                <x-heroicon-o-user-plus class="h-4 w-4" />
                                Assign to Me
                            </button>
                        @endif

                        @if (auth()->user()->can('tickets.update'))
                            <button wire:click="openCloseModal"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-xs sm:text-sm font-medium
                                       bg-white/60 dark:bg-white/10 border border-neutral-500/30 dark:border-white/10
                                       text-neutral-800 dark:text-neutral-100 hover:bg-sky-200/80 dark:hover:bg-sky-500/15
                                       focus:outline-none focus:ring-2 focus:ring-red-300/50 transition">
                                <x-heroicon-o-x-circle class="h-4 w-4" />
                                Close
                            </button>
                        @endif

                        @if ($this->canReply)
                            <button wire:click="openReplyModal"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-xs sm:text-sm font-medium
                                        bg-white/60 dark:bg-white/10 border border-neutral-500/30 dark:border-white/10
                                       text-neutral-800 dark:text-neutral-100 hover:bg-sky-200/80 dark:hover:bg-sky-500/15
                                       focus:outline-none focus:ring-2 focus:ring-neutral-400/40 transition">
                                <x-heroicon-o-paper-airplane class="h-4 w-4" />
                                Reply
                            </button>
                        @endif

                        @if ($this->canAddNotes)
                            <button wire:click="openNoteModal"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-xs sm:text-sm font-medium
                                        bg-white/60 dark:bg-white/10 border border-neutral-500/30 dark:border-white/10
                                       text-neutral-800 dark:text-neutral-100 hover:bg-sky-200/80 dark:hover:bg-sky-500/15
                                       focus:outline-none focus:ring-2 focus:ring-neutral-400/40 transition">
                                <x-heroicon-o-plus class="h-4 w-4" />
                                Note
                            </button>
                        @endif
                    @else
                        @php
                            $canReopen =
                                auth()
                                    ->user()
                                    ->hasRole(['admin', 'support']) ||
                                (auth()->user()->hasRole('client') &&
                                    auth()->user()->organization_id === $ticket->organization_id &&
                                    $ticket->closed_at &&
                                    floor(now()->diffInDays($ticket->closed_at)) <=
                                        app(\App\Contracts\SettingsRepositoryInterface::class)->get(
                                            'tickets.reopen_window_days',
                                            3,
                                        ));
                        @endphp
                        @if ($canReopen)
                            <button wire:click="changeStatus('open')"
                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-xs sm:text-sm font-medium
                                       bg-white/60 dark:bg-white/10 border border-white/30 dark:border-white/10
                                       text-neutral-800 dark:text-neutral-100 hover:bg-white/80 dark:hover:bg-white/15
                                       focus:outline-none focus:ring-2 focus:ring-neutral-400/40 transition">
                                <x-heroicon-o-arrow-path class="h-4 w-4" />
                                Reopen
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Left Column: Details, Contract, Organization Notes, Internal Notes --}}
        <div class="xl:col-span-1 space-y-6">
            {{-- Ticket Details --}}
            <x-tickets.details :ticket="$ticket" :editMode="$editMode" :form="$form" :departments="$departments" :users="$users"
                :statusOptions="$statusOptions" :priorityOptions="$priorityOptions" />

            {{-- Active Contract (if available) --}}
            @if ($this->activeContract)
                <div
                    class="rounded-xl border border-white/20 dark:border-white/10 bg-white/50 dark:bg-neutral-900/40 backdrop-blur-md shadow-sm">
                    <div class="px-6 py-4 border-b border-white/20 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-document-text class="h-5 w-5 text-neutral-700 dark:text-neutral-300" />
                            <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Active Contract
                            </h3>
                        </div>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between gap-3">
                                <dt class="font-medium text-neutral-700 dark:text-neutral-300">Contract #</dt>
                                <dd class="text-neutral-600 dark:text-neutral-400">
                                    {{ $this->activeContract->contract_number }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="font-medium text-neutral-700 dark:text-neutral-300">Type</dt>
                                <dd class="text-neutral-600 dark:text-neutral-400">
                                    {{ ucfirst($this->activeContract->type) }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="font-medium text-neutral-700 dark:text-neutral-300">Period</dt>
                                <dd class="text-neutral-600 dark:text-neutral-400">
                                    {{ $this->activeContract->start_date->format('M d, Y') }} –
                                    {{ $this->activeContract->end_date->format('M d, Y') }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="font-medium text-neutral-700 dark:text-neutral-300">Status</dt>
                                <dd>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/60 dark:bg-white/10 border border-white/30 dark:border-white/10 text-neutral-700 dark:text-neutral-200">
                                        {{ ucfirst($this->activeContract->status) }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            @endif

            {{-- Organization Notes (if available) --}}
            @if ($ticket->organization->notes)
                <div
                    class="rounded-xl border border-white/20 dark:border-white/10 bg-white/50 dark:bg-neutral-900/40 backdrop-blur-md shadow-sm">
                    <div class="px-6 py-4 border-b border-white/20 dark:border-white/10">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-building-office class="h-5 w-5 text-neutral-700 dark:text-neutral-300" />
                            <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Organization
                                Notes</h3>
                        </div>
                    </div>
                    <div class="px-6 py-4">
                        <div class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 leading-relaxed">
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
            @if ($ticket->status !== 'closed')
                <livewire:tickets.ticket-conversation :ticket="$ticket" :key="$ticket->id" />
            @else
                {{-- Read-only conversation for closed tickets --}}
                <div
                    class="rounded-xl border border-white/20 dark:border-white/10 bg-white/50 dark:bg-neutral-900/40 backdrop-blur-md shadow-sm">
                    <div class="px-6 py-4 border-b border-white/20 dark:border-white/10">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Conversation</h3>
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 border border-red-500/20 text-red-700 dark:text-red-300">
                                <x-heroicon-o-lock-closed class="h-4 w-4" />
                                Ticket Closed
                            </span>
                        </div>
                    </div>

                    <div class="px-6 py-4 space-y-4">
                        @forelse($ticket->conversation ?? [] as $item)
                            <div class="border-b border-white/20 dark:border-white/10 pb-4 last:border-b-0 last:pb-0">
                                <x-tickets.conversation-item :item="$item" :ticket="$ticket" />
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <x-heroicon-o-chat-bubble-left-right class="mx-auto h-12 w-12 text-neutral-400" />
                                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-200">No messages
                                </h3>
                                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">This ticket has no
                                    conversation history.</p>
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

            const priorityLevels = {
                'low': 1,
                'normal': 2,
                'high': 3,
                'urgent': 4,
                'critical': 5
            };

            const isEscalation = priorityLevels[newPriority] > priorityLevels[currentPriority];

            if (isEscalation) {
                const priorityLabels = {
                    'low': 'Low',
                    'normal': 'Normal',
                    'high': 'High',
                    'urgent': 'Urgent',
                    'critical': 'Critical'
                };

                if (!confirm(
                        `Are you sure you want to escalate this ticket's priority to ${priorityLabels[newPriority]}?\n\nThis action will be logged for audit purposes.`
                        )) {
                    event.preventDefault();
                    return false;
                }
            }

            return true;
        };
    </script>
</div>
