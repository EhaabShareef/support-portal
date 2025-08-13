@props(['ticket', 'canAddNotes'])

@if($canAddNotes && $ticket->notes && $ticket->notes->count() > 0)
    <div class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50 mb-6">
        <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Internal Notes</h3>
        </div>
        
        <div class="px-6 py-4 space-y-4">
            @foreach($ticket->notes->where('is_internal', true) as $note)
                <div class="flex gap-3 p-3 rounded-lg border border-{{ $note->color }}-200 dark:border-{{ $note->color }}-700 bg-{{ $note->color }}-50 dark:bg-{{ $note->color }}-900/20">
                    {{-- Avatar --}}
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 rounded-full bg-{{ $note->color }}-500 flex items-center justify-center">
                            <span class="text-xs font-medium text-white">
                                {{ substr($note->user->name, 0, 1) }}
                            </span>
                        </div>
                    </div>
                    
                    {{-- Note Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-{{ $note->color }}-800 dark:text-{{ $note->color }}-200">
                                    {{ $note->user->name }}
                                </span>
                                <span class="text-xs text-{{ $note->color }}-600 dark:text-{{ $note->color }}-400">
                                    {{ $note->created_at->format('M d, Y \a\t H:i') }}
                                </span>
                                @if(!$note->is_internal)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                        Public
                                    </span>
                                @endif
                            </div>
                            
                            {{-- Note Actions --}}
                            @if($note->user_id === auth()->id() || auth()->user()->hasRole('admin'))
                                <div class="flex items-center gap-2">
                                    @if($this->editingNoteId === $note->id)
                                        <button wire:click="cancelEditNote" 
                                                class="text-xs text-{{ $note->color }}-600 hover:text-{{ $note->color }}-800 dark:text-{{ $note->color }}-400 dark:hover:text-{{ $note->color }}-200">
                                            Cancel
                                        </button>
                                    @else
                                        <button wire:click="editNote({{ $note->id }})" 
                                                class="text-xs text-{{ $note->color }}-600 hover:text-{{ $note->color }}-800 dark:text-{{ $note->color }}-400 dark:hover:text-{{ $note->color }}-200">
                                            Edit
                                        </button>
                                    @endif
                                    
                                    @if($this->confirmingNoteId === $note->id)
                                        <button wire:click="deleteNote({{ $note->id }})" 
                                                class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                                            Confirm
                                        </button>
                                        <button wire:click="cancelDelete" 
                                                class="text-xs text-{{ $note->color }}-600 hover:text-{{ $note->color }}-800 dark:text-{{ $note->color }}-400 dark:hover:text-{{ $note->color }}-200">
                                            Cancel
                                        </button>
                                    @else
                                        <button wire:click="confirmDelete({{ $note->id }})" 
                                                class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200">
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="text-sm text-{{ $note->color }}-700 dark:text-{{ $note->color }}-300">
                            {!! nl2br(e($note->note)) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif