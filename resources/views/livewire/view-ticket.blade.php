<div>
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('tickets.index') }}"
                    class="inline-flex items-center text-sm text-neutral-600 dark:text-neutral-300 hover:text-neutral-800 dark:hover:text-neutral-100 hover:underline transition-colors duration-200">
                    <x-heroicon-o-arrow-left class="h-4 w-4 mr-1" /> Back to Tickets
                </a>
                <div class="hidden sm:block w-px h-6 bg-neutral-300 dark:bg-neutral-600"></div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                        <x-heroicon-o-ticket class="h-8 w-8" />
                        {{ $ticket->subject }}
                    </h1>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">#{{ $ticket->ticket_number }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2 relative z-50">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \App\Enums\TicketPriority::from($ticket->priority)->cssClass() }}">
                    <x-dynamic-component :component="\App\Enums\TicketPriority::from($ticket->priority)->icon()" class="h-3 w-3 mr-1" />
                    {{ \App\Enums\TicketPriority::from($ticket->priority)->label() }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \App\Enums\TicketStatus::from($ticket->status)->cssClass() }}">
                    {{ \App\Enums\TicketStatus::from($ticket->status)->label() }}
                </span>

                @if(auth()->user()->can('tickets.update'))
                    @if(!$ticket->assigned_to && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('support')))
                        <button wire:click="assignToMe"
                                class="inline-flex items-center px-2 py-1 text-xs text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/30 rounded transition-all duration-200"
                                title="Assign to Me">
                            <x-heroicon-o-user-plus class="h-4 w-4" />
                        </button>
                    @endif

                    <div class="relative">
                        <button @click="$dispatch('toggle-status-dropdown')"
                                x-ref="statusButton"
                                class="inline-flex items-center px-2 py-1 text-xs text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/30 rounded transition-all duration-200"
                                title="Change Status">
                            <x-heroicon-o-arrow-path class="h-4 w-4" />
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div>
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
            class="bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 p-4 rounded-lg shadow">
            <div class="flex items-center">
                <x-heroicon-o-check-circle class="h-5 w-5 mr-2" />
                {{ session('message') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
            class="bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 p-4 rounded-lg shadow">
            <div class="flex items-center">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                {{ session('error') }}
            </div>
        </div>
    @endif
    </div>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Ticket Details & Notes --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Ticket Information --}}
            <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Ticket Details</h3>
                    @if($this->canEdit)
                    <button wire:click="enableEdit" 
                        class="inline-flex items-center px-3 py-1.5 text-xs text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200">
                        <x-heroicon-o-pencil class="h-3 w-3 mr-1" />
                        Edit
                    </button>
                    @endif
                </div>

                @if($editMode)
                    {{-- Edit Form --}}
                    <form wire:submit="updateTicket" class="space-y-4">

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Priority</label>
                                <select wire:model="form.priority"
                                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                    @foreach($priorityOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('form.priority') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Status</label>
                                <select wire:model="form.status"
                                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('form.status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

        <div class="grid grid-cols-2 gap-3 mt-4">
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Department</label>
                <select wire:model="form.department_id"
                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
                @error('form.department_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Assigned To</label>
                <select wire:model="form.assigned_to"
                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Unassigned</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                @error('form.assigned_to') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                            <textarea readonly rows="3"
                                      class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-neutral-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 cursor-not-allowed">{{ $form['description'] }}</textarea>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Description cannot be modified after ticket creation</p>
                        </div>

                        <div class="flex items-center gap-2 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                                <x-heroicon-o-check class="h-4 w-4 mr-1" />
                                Save Changes
                            </button>
                            <button type="button" wire:click="cancelEdit" 
                                    class="inline-flex items-center px-4 py-2 bg-neutral-500 hover:bg-neutral-600 text-white text-sm font-medium rounded-md transition-all duration-200">
                                <x-heroicon-o-x-mark class="h-4 w-4 mr-1" />
                                Cancel
                            </button>
                        </div>
                    </form>
                @else
                    {{-- Display Mode --}}
                    <dl class="space-y-3">
                        <div>
                            <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Client</dt>
                            <dd class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">{{ $ticket->client->name }}</dd>
                        </div>
                        
                        <div>
                            <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Organization</dt>
                            <dd class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">{{ $ticket->organization->name }}</dd>
                        </div>
                        
                        <div>
                            <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Department</dt>
                            <dd class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">{{ $ticket->department->name }}</dd>
                        </div>
                        
                        <div>
                            <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Contract Status</dt>
                            <dd class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">
                                @if($this->activeContract)
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">{{ $this->activeContract->contract_number }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                                                @if($this->activeContract->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                                @elseif($this->activeContract->status === 'expired') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300 @endif">
                                                {{ ucfirst($this->activeContract->status) }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-neutral-600 dark:text-neutral-400">
                                            {{ ucfirst($this->activeContract->type) }} • 
                                            {{ $this->activeContract->start_date->format('M d, Y') }} - 
                                            {{ $this->activeContract->end_date ? $this->activeContract->end_date->format('M d, Y') : 'Ongoing' }}
                                        </div>
                                        @if($this->activeContract->contract_value)
                                        <div class="text-xs text-neutral-600 dark:text-neutral-400">
                                            Value: {{ $this->activeContract->currency }} {{ number_format($this->activeContract->contract_value, 2) }}
                                        </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300">
                                            <x-heroicon-o-exclamation-triangle class="h-3 w-3 mr-1" />
                                            No Active Contract
                                        </span>
                                    </div>
                                    <div class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
                                        No active contract found for {{ $ticket->organization->name }} in {{ $ticket->department->name }} department
                                    </div>
                                @endif
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Assigned To</dt>
                            <dd class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">
                                @if($ticket->assigned)
                                    {{ $ticket->assigned->name }}
                                @else
                                    <span class="text-neutral-500 dark:text-neutral-400">Unassigned</span>
                                @endif
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Created</dt>
                            <dd class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">{{ $ticket->created_at->format('M d, Y H:i') }}</dd>
                        </div>
                        
                    </dl>
                @endif
            </div>

            {{-- Organization Notes Section --}}
            <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
                <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 mb-4">Organization Notes</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-sky-600 dark:text-sky-400">{{ $ticket->organization->users()->count() }}</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Users</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $ticket->organization->contracts()->count() }}</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Contracts</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $ticket->organization->hardware()->count() }}</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Hardware</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $ticket->organization->tickets()->count() }}</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Tickets</div>
                    </div>
                </div>
            </div>

            {{-- Internal Notes Section --}}
            @if($this->canAddNotes)
            <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Internal Notes</h3>
                        <button wire:click="$set('activeInput', 'note')" 
                                class="inline-flex items-center px-3 py-1.5 text-xs text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 hover:bg-purple-50 dark:hover:bg-purple-900/30 rounded-md transition-all duration-200">
                            <x-heroicon-o-plus class="h-3 w-3 mr-1" />
                            Add Note
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Notes --}}
                    @foreach($ticket->notes as $note)
                    @php
                        $colorClasses = [
                            'sky' => 'bg-sky-50 dark:bg-sky-900/20 border-sky-200 dark:border-sky-800',
                            'green' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
                            'yellow' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
                            'red' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                            'purple' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800',
                            'blue' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
                        ];
                        $noteClasses = $colorClasses[$note->color] ?? $colorClasses['sky'];
                    @endphp
                    <div class="relative {{ $noteClasses }} border rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $note->user->name }}</span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $note->created_at?->diffForHumans() ?? 'Unknown time' }}</span>
                                    @if($note->is_internal)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                            Internal
                                        </span>
                                    @endif
                                </div>
                                <div class="text-sm text-neutral-700 dark:text-neutral-300">
                                    {!! nl2br(e($note->note)) !!}
                                </div>
                            </div>
                            @if($note->user_id === auth()->id() || auth()->user()->hasRole('admin'))
                            <button wire:click="confirmDelete({{ $note->id }})" 
                                    class="ml-2 text-red-500 hover:text-red-700 transition-colors duration-200">
                                <x-heroicon-o-trash class="h-4 w-4" />
                            </button>
                            @endif
                        </div>

                        {{-- Delete Confirmation --}}
                        @if($confirmingNoteId === $note->id)
                        <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded">
                            <p class="text-sm text-red-800 dark:text-red-200 mb-3">Are you sure you want to delete this note?</p>
                            <div class="flex gap-2">
                                <button wire:click="deleteNote({{ $note->id }})"
                                        class="inline-flex items-center px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-xs rounded transition-all duration-200">
                                    Delete
                                </button>
                                <button wire:click="cancelDelete"
                                        class="inline-flex items-center px-3 py-1 bg-neutral-500 hover:bg-neutral-600 text-white text-xs rounded transition-all duration-200">
                                    Cancel
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach

                    {{-- Add Note Form --}}
                    @if($activeInput === 'note')
                    <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4">
                        <form wire:submit="addNote">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Note</label>
                                    <textarea wire:model="note" rows="3"
                                              class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500"
                                              placeholder="Add your note..."></textarea>
                                    @error('note') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex items-center gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Color</label>
                                        <select wire:model="noteColor" 
                                                class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                            <option value="sky">Blue</option>
                                            <option value="green">Green</option>
                                            <option value="yellow">Yellow</option>
                                            <option value="red">Red</option>
                                            <option value="purple">Purple</option>
                                        </select>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" wire:model="noteInternal" id="noteInternal" 
                                               class="rounded border-neutral-300 text-sky-600 focus:border-sky-300 focus:ring focus:ring-sky-200 focus:ring-opacity-50">
                                        <label for="noteInternal" class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Internal only</label>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 mt-4">
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                                    <x-heroicon-o-plus class="h-4 w-4 mr-1" />
                                    Add Note
                                </button>
                                <button type="button" wire:click="$set('activeInput', '')"
                                        class="inline-flex items-center px-4 py-2 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 text-sm font-medium transition-all duration-200">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif

                    @if($ticket->notes->isEmpty())
                    <div class="text-center py-8">
                        <x-heroicon-o-paper-clip class="mx-auto h-12 w-12 text-neutral-400" />
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No notes yet</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Add internal notes to keep track of progress.</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column - Conversation Only --}}
        <div class="lg:col-span-2">
            <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Conversation</h3>
                        @if($this->canReply)
                        <div class="flex items-center gap-2">
                            <button wire:click="$set('activeInput', 'reply')"
                                    class="inline-flex items-center px-3 py-1.5 text-xs text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200">
                                <x-heroicon-o-chat-bubble-left class="h-3 w-3 mr-1" />
                                Reply
                            </button>
                            @if($ticket->status !== 'closed')
                            <button wire:click="openCloseModal"
                                    class="inline-flex items-center px-3 py-1.5 text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-all duration-200">
                                <x-heroicon-o-x-circle class="h-3 w-3 mr-1" />
                                Close
                            </button>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Reply Form --}}
                    @if($activeInput === 'reply')
                    <div class="border-b border-neutral-200 dark:border-neutral-700 pb-4 mb-4">
                        <form wire:submit="sendMessage">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Your Reply</label>
                                <textarea wire:model="replyMessage" rows="4"
                                          class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500"
                                          placeholder="Type your reply..."></textarea>
                                @error('replyMessage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Update Status</label>
                                <select wire:model="replyStatus"
                                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                    @foreach($statusOptions as $value => $label)
                                        @if($value !== 'closed')
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('replyStatus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            {{-- File Upload Section --}}
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Attachments</label>

                                {{-- Drag & Drop Zone --}}
                                <div x-data="{
                                    dragging: false,
                                    handleDrop($event) {
                                        this.dragging = false;
                                        const files = Array.from($event.dataTransfer.files);
                                        files.forEach(file => {
                                            if (file.size <= 10485760) { // 10MB limit
                                                @this.upload('attachments', file, () => {}, () => {}, () => {});
                                            } else {
                                                alert('File size must be less than 10MB');
                                            }
                                        });
                                    }
                                }"
                                @dragover.prevent="dragging = true"
                                @dragleave.prevent="dragging = false"
                                @drop.prevent="handleDrop($event)"
                                :class="{'border-sky-400 bg-sky-50 dark:bg-sky-900/20': dragging}"
                                class="border-2 border-dashed border-neutral-300 dark:border-neutral-600 rounded-lg p-6 text-center transition-colors duration-200">
                                    <input type="file" wire:model="attachments" multiple
                                           accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar"
                                           class="hidden" id="fileInput">

                                    <div class="space-y-2">
                                        <x-heroicon-o-cloud-arrow-up class="mx-auto h-12 w-12 text-neutral-400" />
                                        <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                            <label for="fileInput" class="cursor-pointer text-sky-600 hover:text-sky-500">
                                                Click to upload
                                            </label>
                                            or drag and drop files here
                                        </div>
                                        <p class="text-xs text-neutral-500">PDF, DOC, XLS, TXT, ZIP, Images (max 10MB each)</p>
                                    </div>
                                </div>

                                {{-- Upload Progress --}}
                                <div wire:loading wire:target="attachments" class="mt-2">
                                    <div class="flex items-center text-sm text-sky-600">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-sky-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Uploading files...
                                    </div>
                                </div>

                                {{-- File List --}}
                                @if(!empty($attachments))
                                    <div class="mt-3 space-y-2">
                                        @foreach($attachments as $index => $file)
                                            <div class="flex items-center justify-between p-2 bg-neutral-50 dark:bg-neutral-800 rounded border">
                                                <div class="flex items-center space-x-2">
                                                    <x-heroicon-o-document class="h-4 w-4 text-neutral-400" />
                                                    <span class="text-sm text-neutral-600 dark:text-neutral-300">{{ $file->getClientOriginalName() }}</span>
                                                    <span class="text-xs text-neutral-500">({{ number_format($file->getSize() / 1024, 1) }} KB)</span>
                                                </div>
                                                <button type="button" wire:click="removeAttachment({{ $index }})"
                                                        class="text-red-500 hover:text-red-700 transition-colors">
                                                    <x-heroicon-o-x-mark class="h-4 w-4" />
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @error('attachments.*') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex items-center gap-2 mt-3">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                                    <x-heroicon-o-paper-airplane class="h-4 w-4 mr-1" />
                                    Send Reply
                                </button>
                                <button type="button" wire:click="$set('activeInput', '')"
                                        class="inline-flex items-center px-4 py-2 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 text-sm font-medium transition-all duration-200">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif


                    {{-- Messages --}}
                    <div id="messages-start"></div>
                    @foreach($ticket->conversation as $item)
                        @if(!$loop->first)
                            <hr class="border-neutral-200 dark:border-neutral-700">
                        @endif
                        
                        @php
                            $isCurrentUser = $item->sender_id === auth()->id();
                            $isSystemMessage = $item->is_system_message ?? false;
                            $isNote = $item->type === 'note';
                            
                            if ($isSystemMessage) {
                                $avatarClass = 'bg-gradient-to-br from-blue-400 to-blue-600';
                                $messageBackgroundClass = 'bg-blue-50/50 dark:bg-blue-900/20 border border-blue-200/50 dark:border-blue-800/30';
                            } elseif ($isNote) {
                                $avatarClass = 'bg-gradient-to-br from-purple-400 to-purple-600';
                                $messageBackgroundClass = 'bg-purple-50/30 dark:bg-purple-900/20 border-l-4 border-l-purple-400 dark:border-l-purple-500 border border-purple-200/50 dark:border-purple-800/30';
                            } else {
                                $avatarClass = $isCurrentUser 
                                    ? 'bg-gradient-to-br bg-green-400/60' 
                                    : 'bg-gradient-to-br bg-orange-400/60';
                                
                                // Check if this is a closing message
                                $isClosingMessage = $ticket->status === 'closed' && 
                                                  $ticket->closed_at && 
                                                  $item->created_at->diffInMinutes($ticket->closed_at) <= 2;
                                
                                $messageBackgroundClass = $isClosingMessage 
                                    ? 'bg-red-100/30 dark:bg-red-900/20 border border-red-200/50 dark:border-red-800/30'
                                    : 'bg-white/20 dark:bg-neutral-800/20';
                            }
                        @endphp
                        
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 {{ $avatarClass }} rounded-full flex items-center justify-center">
                                @if($isSystemMessage)
                                    <x-heroicon-o-cog-6-tooth class="h-4 w-4 text-white" />
                                @elseif($isNote)
                                    <x-heroicon-o-chat-bubble-left-ellipsis class="h-4 w-4 text-white" />
                                @else
                                    <span class="text-white text-xs font-medium">{{ substr($item->sender->name ?? $item->user->name ?? 'Unknown', 0, 1) }}</span>
                                @endif
                            </div>
                        <div class="flex-1 {{ $messageBackgroundClass }} rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-medium text-neutral-800 dark:text-neutral-200">
                                    @if($isSystemMessage)
                                        System
                                    @elseif($isNote)
                                        {{ $item->user->name ?? 'Unknown' }}
                                    @else
                                        {{ $item->sender->name ?? 'Unknown' }}
                                    @endif
                                </span>
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $item->created_at?->diffForHumans() ?? 'Unknown time' }}</span>
                                @if($isSystemMessage)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        <x-heroicon-o-information-circle class="h-3 w-3 mr-1" />
                                        System Message
                                    </span>
                                @elseif($isNote)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                                        <x-heroicon-o-chat-bubble-left-ellipsis class="h-3 w-3 mr-1" />
                                        Note
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm text-neutral-700 dark:text-neutral-300">
                                {!! nl2br(e($item->message)) !!}
                            </div>
                            
                            {{-- Message Attachments (only for messages, not notes) --}}
                            @if(!$isNote && isset($item->attachments) && $item->attachments->isNotEmpty())
                                <div class="mt-3 space-y-2">
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400 font-medium">Attachments:</div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($item->attachments as $attachment)
                                            <div class="flex items-center p-2 bg-neutral-100 dark:bg-neutral-700 rounded border">
                                                <div class="flex items-center flex-1 min-w-0">
                                                    <x-dynamic-component :component="$attachment->icon" class="h-4 w-4 text-neutral-500 mr-2 flex-shrink-0" />
                                                    <div class="flex-1 min-w-0">
                                                        <div class="text-sm text-neutral-700 dark:text-neutral-200 truncate">{{ $attachment->original_name }}</div>
                                                        <div class="text-xs text-neutral-500">{{ $attachment->formatted_size }}</div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center space-x-1 ml-2">
                                                    @if($attachment->canBeViewedInBrowser())
                                                        <button @click="$dispatch('show-attachment', {{ $attachment->toJson() }})"
                                                                class="text-sky-600 hover:text-sky-800 transition-colors"
                                                                title="View in browser">
                                                            <x-heroicon-o-eye class="h-4 w-4" />
                                                        </button>
                                                    @endif
                                                    <a href="{{ route('attachments.download', ['uuid' => $attachment->uuid, 'download' => true]) }}"
                                                       class="text-neutral-600 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-200 transition-colors"
                                                       title="Download">
                                                        <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    @if($ticket->conversation->isEmpty())
                    <div class="text-center py-8">
                        <x-heroicon-o-chat-bubble-left class="mx-auto h-12 w-12 text-neutral-400" />
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No messages yet</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Start the conversation by sending a reply.</p>
                    </div>
                    @endif
                </div>

                </div>
            </div>

        </div>

        {{-- Auto-scroll to latest message after reply --}}
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('message-sent', () => {
                    setTimeout(() => {
                        const messagesStart = document.getElementById('messages-start');
                        if (messagesStart) {
                            messagesStart.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }, 100);
                });
            });
        </script>
    </div>
    @if($showCloseModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showCloseModal') }" x-show="show" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="fixed inset-0 bg-neutral-500 bg-opacity-75" @click="show = false"></div>
            <div class="inline-block bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full p-6 relative z-10">
                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-neutral-100 mb-4">Close Ticket</h3>
                <form wire:submit="submitClose" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Remarks</label>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">This will be visible to all users including the client.</p>
                        <textarea wire:model="closeForm.remarks" rows="3" class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                        @error('closeForm.remarks') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Solution Summary</label>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">Internal only - visible to admin and support users only.</p>
                        <textarea wire:model="closeForm.solution" rows="2" class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                        @error('closeForm.solution') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" @click="show = false" class="px-4 py-2 text-sm text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">Close Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Attachment Preview Modal (moved inside main wrapper) --}}
    <div x-data="{ 
        showModal: false, 
        currentAttachment: null,
        showAttachment(attachment) {
            this.currentAttachment = attachment;
            this.showModal = true;
        }
    }"
    x-on:show-attachment.window="showAttachment($event.detail)"
    x-show="showModal"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity"
                 @click="showModal = false"></div>

            <div x-show="showModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                
                <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100" x-text="currentAttachment?.original_name"></h3>
                        <button @click="showModal = false" class="text-neutral-400 hover:text-neutral-600 transition-colors">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>
                    
                    <div class="max-h-96 overflow-auto">
                        <template x-if="currentAttachment?.is_image">
                            <img :src="'/attachments/' + currentAttachment?.uuid + '/view'" 
                                 :alt="currentAttachment?.original_name"
                                 class="max-w-full h-auto rounded">
                        </template>
                        
                        <template x-if="!currentAttachment?.is_image && currentAttachment?.mime_type === 'application/pdf'">
                            <iframe :src="'/attachments/' + currentAttachment?.uuid + '/view'" 
                                    class="w-full h-96 border rounded"></iframe>
                        </template>
                        
                        <template x-if="!currentAttachment?.is_image && currentAttachment?.mime_type !== 'application/pdf'">
                            <div class="text-center py-8">
                                <x-heroicon-o-document class="mx-auto h-12 w-12 text-neutral-400" />
                                <p class="mt-2 text-sm text-neutral-500">Preview not available for this file type</p>
                                <a :href="'/attachments/' + currentAttachment?.uuid + '/download?download=true'"
                                   class="mt-2 inline-flex items-center px-3 py-1 bg-sky-600 text-white text-sm rounded hover:bg-sky-700 transition-colors">
                                    <x-heroicon-o-arrow-down-tray class="h-4 w-4 mr-1" />
                                    Download
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
                
                <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <a :href="'/attachments/' + currentAttachment?.uuid + '/download?download=true'"
                       class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        <x-heroicon-o-arrow-down-tray class="h-4 w-4 mr-1" />
                        Download
                    </a>
                    <button @click="showModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Dropdown (positioned outside stacking contexts) --}}
    @if(auth()->user()->can('tickets.update'))
    <div x-data="{ open: false }" 
         x-on:toggle-status-dropdown.window="
             open = !open; 
             if(open) { 
                 $nextTick(() => { 
                     const button = document.querySelector('[x-ref=statusButton]'); 
                     if(button) {
                         const rect = button.getBoundingClientRect(); 
                         $refs.dropdown.style.top = (rect.bottom + window.scrollY + 4) + 'px'; 
                         $refs.dropdown.style.left = (rect.right - 160) + 'px'; 
                     }
                 }); 
             }"
         @click.away="open = false"
         x-show="open"
         class="fixed inset-0 z-[9999] pointer-events-none">
        <div x-ref="dropdown"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="transform opacity-0 scale-95"
             x-transition:enter-end="transform opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="transform opacity-100 scale-100"
             x-transition:leave-end="transform opacity-0 scale-95"
             class="absolute w-40 bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-md shadow-xl pointer-events-auto">
            <div class="py-1">
                @foreach($statusOptions as $value => $label)
                    @if($value !== $ticket->status && $value !== 'closed')
                        <button wire:click="changeStatus('{{ $value }}')" @click="open = false"
                                class="w-full text-left px-2 py-1 text-xs text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-700">
                            {{ $label }}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
</div>