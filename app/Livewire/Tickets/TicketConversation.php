<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketNote;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class TicketConversation extends Component
{
    protected $listeners = ['edit-note' => 'handleEditNote', 'open-reply-modal' => 'openReplyModal', 'open-note-modal' => 'openNoteModal'];
    
    public Ticket $ticket;
    public string $activeInput = ''; // values: 'reply', 'note'
    public string $replyMessage = '';
    public string $replyStatus = '';
    public string $note = '';
    public string $noteColor = 'sky';
    public bool $noteInternal = true;
    public ?int $confirmingNoteId = null;
    public ?int $editingNoteId = null;
    public $noteInputKey = null;
    public $attachments = [];
    
    // Modal states
    public bool $showReplyModal = false;
    public bool $showNoteModal = false;

    public function mount(Ticket $ticket)
    {
        $this->ticket = $ticket;
        $this->replyStatus = 'in_progress'; // Default status for replies
        $this->noteInputKey = uniqid();
        $this->refreshConversation();
    }

    public function openReplyModal()
    {
        $this->replyMessage = '';
        $this->replyStatus = 'in_progress';
        $this->attachments = [];
        $this->showReplyModal = true;
    }

    public function closeReplyModal()
    {
        $this->replyMessage = '';
        $this->attachments = [];
        $this->showReplyModal = false;
        // Ensure conversation is preserved when modal is closed
        $this->refreshConversation();
    }

    public function openNoteModal()
    {
        $this->note = '';
        $this->noteColor = 'sky';
        $this->noteInternal = true;
        $this->editingNoteId = null;
        $this->showNoteModal = true;
    }

    public function closeNoteModal()
    {
        $this->note = '';
        $this->noteColor = 'sky';
        $this->noteInternal = true;
        $this->editingNoteId = null;
        $this->showNoteModal = false;
        // Ensure conversation is preserved when modal is closed
        $this->refreshConversation();
    }

    public function refreshConversation(): void
    {
        // Ensure ticket relationships are fresh
        $this->ticket->unsetRelation('messages');
        $this->ticket->unsetRelation('notes');
        
        // Create unified conversation combining messages and public notes
        $messages = $this->ticket->messages()
            ->select(['id', 'ticket_id', 'sender_id', 'message', 'is_system_message', 'created_at'])
            ->with([
                'sender:id,name',
                'attachments:id,uuid,attachable_id,attachable_type,original_name,stored_name,mime_type,size,is_image'
            ])
            ->selectRaw("'message' as type")
            ->get();
            
        $publicNotes = $this->ticket->notes()
            ->where('is_internal', false)
            ->select(['id', 'user_id as sender_id', 'note as message', 'created_at'])
            ->with('user:id,name')
            ->selectRaw("'note' as type")
            ->selectRaw("null as ticket_id")
            ->selectRaw("false as is_system_message")
            ->get()
            ->map(function ($note) {
                $note->attachments = collect(); // Add empty attachments collection
                return $note;
            });
            
        // Combine and sort by created_at descending (newest top, oldest bottom)
        $conversation = $messages->concat($publicNotes)->sortByDesc('created_at')->values();
        
        $this->ticket->setRelation('conversation', $conversation);
    }

    #[Computed]
    public function canReply()
    {
        $user = auth()->user();

        // Clients can only reply to their own organization's tickets
        if ($user->hasRole('client')) {
            return $this->ticket->organization_id === $user->organization_id;
        }

        // Support can reply to tickets in their department or department group
        if ($user->hasRole('support')) {
            // Check same department first
            if ($this->ticket->department_id === $user->department_id) {
                return true;
            }
            // Check same department group
            if ($user->department?->department_group_id && 
                $user->department->department_group_id === $this->ticket->department?->department_group_id) {
                return true;
            }
            return false;
        }

        // Admins can reply to any ticket
        return $user->hasRole('admin');
    }

    #[Computed]
    public function canAddNotes()
    {
        return auth()->user()->hasRole('admin') || auth()->user()->hasRole('support');
    }

    public function sendMessage()
    {
        try {
            if (! $this->canReply) {
                session()->flash('error', 'You do not have permission to reply to this ticket.');
                return;
            }

            $this->validate([
                'replyMessage' => 'required|string|max:2000',
                'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt,zip,rar',
                'replyStatus' => 'required|in:open,in_progress,solution_provided',
            ]);

            $message = TicketMessage::create([
                'ticket_id' => $this->ticket->id,
                'sender_id' => Auth::id(),
                'message' => $this->replyMessage,
            ]);

            // Handle file attachments
            if (!empty($this->attachments)) {
                foreach ($this->attachments as $file) {
                    $this->storeAttachment($file, $message);
                }
            }

            $this->reset('replyMessage');
            $this->attachments = [];
            $this->activeInput = ''; // hide input after submit

            // Update ticket status - default to in_progress for replies
            $newStatus = $this->replyStatus ?: 'in_progress';
            if ($newStatus !== $this->ticket->status) {
                $this->ticket->update(['status' => $newStatus]);
            }

            // Update first response time if this is the first response
            if (! $this->ticket->first_response_at && auth()->user()->hasRole(['support', 'admin'])) {
                $this->ticket->update([
                    'first_response_at' => now(),
                    'response_time_minutes' => $this->ticket->created_at->diffInMinutes(now()),
                ]);
            }

            // Refresh ticket attributes but preserve message relationship control
            $this->ticket->refresh(['status', 'updated_at']);
            $this->replyStatus = $this->ticket->status;
            
            // Clear and reload conversation
            $this->refreshConversation();

            $this->closeReplyModal();
            session()->flash('message', 'Message sent successfully.');
            
            // Dispatch event for auto-scroll to latest message
            $this->dispatch('message-sent');
            
        } catch (\Exception $e) {
            logger()->error('Failed to send message', [
                'user_id' => auth()->id(),
                'ticket_id' => $this->ticket->id,
                'message' => $this->replyMessage,
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Failed to send message. Please try again or contact support if the problem persists.');
        }
    }

    public function addNote()
    {
        if (! $this->canAddNotes) {
            session()->flash('error', 'You do not have permission to add notes.');
            return;
        }

        $this->validate([
            'note' => 'required|string|max:2000',
            'noteColor' => 'required',
        ]);

        // If editingNoteId is set, this is an update, not a new note
        if ($this->editingNoteId) {
            return $this->updateNote();
        }

        TicketNote::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'is_internal' => $this->noteInternal,
            'color' => $this->noteColor,
            'note' => $this->note,
        ]);

        $this->note = '';
        $this->noteColor = 'sky';
        $this->noteInternal = true;
        $this->noteInputKey = uniqid();
        $this->activeInput = ''; // hide input after submit

        $this->ticket->refresh()->load(['notes' => function($query) {
            $query->select(['id', 'ticket_id', 'user_id', 'note', 'color', 'is_internal', 'created_at'])
                  ->with('user:id,name')
                  ->latest();
        }]);
        
        // Refresh conversation to show updated public notes
        $this->refreshConversation();
        
        $this->closeNoteModal();
        session()->flash('message', 'Note added successfully.');
        $this->dispatch('noteAdded', ['ticket' => $this->ticket]);
    }

    public function editNote($noteId)
    {
        $note = TicketNote::where('ticket_id', $this->ticket->id)
            ->where('id', $noteId)
            ->first();

        if (!$note || !auth()->user()->can('update', $note)) {
            session()->flash('error', 'Unauthorized to edit this note.');
            return;
        }

        $this->editingNoteId = $noteId;
        $this->note = $note->note;
        $this->noteColor = $note->color;
        $this->noteInternal = $note->is_internal;
        $this->activeInput = 'note';
    }

    public function updateNote()
    {
        if (!$this->editingNoteId) {
            return $this->addNote();
        }

        $note = TicketNote::where('ticket_id', $this->ticket->id)
            ->where('id', $this->editingNoteId)
            ->first();

        if (!$note || !auth()->user()->can('update', $note)) {
            session()->flash('error', 'Unauthorized to edit this note.');
            return;
        }

        $this->validate([
            'note' => 'required|string|max:2000',
            'noteColor' => 'required',
        ]);

        $note->update([
            'note' => $this->note,
            'color' => $this->noteColor,
            'is_internal' => $this->noteInternal,
        ]);

        $this->note = '';
        $this->noteColor = 'sky';
        $this->noteInternal = true;
        $this->editingNoteId = null;
        $this->noteInputKey = uniqid();
        $this->activeInput = '';

        $this->ticket->refresh()->load(['notes' => function($query) {
            $query->select(['id', 'ticket_id', 'user_id', 'note', 'color', 'is_internal', 'created_at'])
                  ->with('user:id,name')
                  ->latest();
        }]);
        
        // Refresh conversation to show updated public notes
        $this->refreshConversation();
        
        $this->closeNoteModal();
        session()->flash('message', 'Note updated successfully.');
        $this->dispatch('noteUpdated', ['ticket' => $this->ticket]);
        
        // Refresh the parent ViewTicket component's notes
        $this->dispatch('refresh-notes');
    }

    public function cancelEditNote()
    {
        $this->editingNoteId = null;
        $this->note = '';
        $this->noteColor = 'sky';
        $this->noteInternal = true;
        $this->activeInput = '';
    }

    public function confirmDelete($noteId)
    {
        $this->confirmingNoteId = $noteId;
    }

    public function cancelDelete()
    {
        $this->confirmingNoteId = null;
    }

    public function deleteNote($noteId)
    {
        $note = TicketNote::where('ticket_id', $this->ticket->id)
            ->where('id', $noteId)
            ->first();

        if (! $note || ($note->user_id !== Auth::id() && ! auth()->user()->hasRole('admin'))) {
            session()->flash('error', 'Unauthorized to delete this note.');
            return;
        }

        $note->delete();
        $this->confirmingNoteId = null;

        $this->ticket->refresh()->load(['notes' => function($query) {
            $query->select(['id', 'ticket_id', 'user_id', 'note', 'color', 'is_internal', 'created_at'])
                  ->with('user:id,name')
                  ->latest();
        }]);
        
        session()->flash('message', 'Note deleted successfully.');
        $this->dispatch('noteDeleted', ['ticket' => $this->ticket]);
    }

    public function removeAttachment($index)
    {
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    private function storeAttachment($file, $attachable)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        
        // Generate a unique stored name for security
        $storedName = \Illuminate\Support\Str::uuid() . '.' . $extension;
        
        // Store the file
        $path = $file->storeAs('attachments', $storedName, 'local');
        
        // Determine if it's an image
        $isImage = in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
        
        // Create attachment record
        $attachment = \App\Models\Attachment::create([
            'attachable_type' => get_class($attachable),
            'attachable_id' => $attachable->id,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'path' => $path,
            'disk' => 'local',
            'mime_type' => $mimeType,
            'size' => $size,
            'extension' => $extension,
            'is_public' => false,
            'is_image' => $isImage,
            'uploaded_by' => Auth::id(),
        ]);

        // Ensure the UUID was set (it should be auto-generated in the model's boot method)
        if (empty($attachment->uuid)) {
            $attachment->uuid = \Illuminate\Support\Str::uuid();
            $attachment->save();
        }
    }

    public function handleEditNote($data)
    {
        $this->editingNoteId = $data['noteId'];
        $this->note = $data['note'];
        $this->noteColor = $data['color'];
        $this->noteInternal = $data['isInternal'];
        $this->showNoteModal = true;
    }

    public function render()
    {
        return view('tickets.conversation');
    }
}