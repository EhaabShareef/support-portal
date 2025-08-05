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
                        #{{ $ticket->ticket_number }}
                    </h1>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">{{ $ticket->subject }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                @php
                    $priorityColors = [
                        'low' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300',
                        'normal' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                        'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
                        'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                        'critical' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'
                    ];
                    $statusColors = [
                        'open' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                        'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                        'awaiting_customer_response' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                        'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300',
                        'on_hold' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'
                    ];
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$ticket->priority] ?? $priorityColors['normal'] }}">
                    {{ $ticket->priority_label }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$ticket->status] ?? $statusColors['open'] }}">
                    {{ $ticket->status_label }}
                </span>
            </div>
        </div>
    </div>

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

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Ticket Details --}}
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
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Subject</label>
                            <input type="text" wire:model="form.subject" 
                                   class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                            @error('form.subject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Type</label>
                                <select wire:model="form.type" 
                                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                                    @foreach($typeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('form.type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
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
                        </div>

                        <div class="grid grid-cols-2 gap-3">
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

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                            <textarea wire:model="form.description" rows="3"
                                      class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                            @error('form.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                            <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Type</dt>
                            <dd class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">{{ ucfirst($ticket->type) }}</dd>
                        </div>
                        
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
                        
                        @if($ticket->description)
                        <div>
                            <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Description</dt>
                            <dd class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">{{ $ticket->description }}</dd>
                        </div>
                        @endif
                    </dl>
                @endif
            </div>
        </div>

        {{-- Right Column - Messages and Notes --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Messages --}}
            <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Conversation</h3>
                        @if($this->canReply)
                        <button wire:click="$set('activeInput', 'reply')" 
                                class="inline-flex items-center px-3 py-1.5 text-xs text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200">
                            <x-heroicon-o-chat-bubble-left class="h-3 w-3 mr-1" />
                            Reply
                        </button>
                        @endif
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    {{-- Initial Description --}}
                    @if($ticket->description)
                    <div class="bg-neutral-50 dark:bg-neutral-800/50 rounded-lg p-4 border-l-4 border-sky-500">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-xs font-medium">{{ substr($ticket->client->name, 0, 1) }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $ticket->client->name }}</span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">opened this ticket</span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $ticket->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-sm text-neutral-700 dark:text-neutral-300">
                                    {!! nl2br(e($ticket->description)) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Messages --}}
                    @foreach($ticket->messages as $message)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center">
                            <span class="text-white text-xs font-medium">{{ substr($message->sender->name, 0, 1) }}</span>
                        </div>
                        <div class="flex-1 bg-white/20 dark:bg-neutral-800/20 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $message->sender->name }}</span>
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $message->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-sm text-neutral-700 dark:text-neutral-300">
                                {!! nl2br(e($message->message)) !!}
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- Reply Form --}}
                    @if($activeInput === 'reply')
                    <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4">
                        <form wire:submit="sendMessage">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Your Reply</label>
                                <textarea wire:model="replyMessage" rows="4"
                                          class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500"
                                          placeholder="Type your reply..."></textarea>
                                @error('replyMessage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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

                    @if($ticket->messages->isEmpty() && !$ticket->description)
                    <div class="text-center py-8">
                        <x-heroicon-o-chat-bubble-left class="mx-auto h-12 w-12 text-neutral-400" />
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No messages yet</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Start the conversation by sending a reply.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Notes Section --}}
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
                    <div class="relative bg-{{ $note->color }}-50 dark:bg-{{ $note->color }}-900/20 border border-{{ $note->color }}-200 dark:border-{{ $note->color }}-800 rounded-lg p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $note->user->name }}</span>
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $note->created_at->diffForHumans() }}</span>
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
                            @if($note->user_id === auth()->id() || auth()->user()->hasRole(['Super Admin', 'Admin']))
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
                        <x-heroicon-o-sticky-note class="mx-auto h-12 w-12 text-neutral-400" />
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No notes yet</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Add internal notes to keep track of progress.</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>