<?php

namespace App\Livewire;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\TicketType;
use App\Models\Attachment;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketNote;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class ViewTicket extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public string $activeInput = ''; // values: 'reply', 'note'

    public bool $editMode = false;

    public array $form = [
        'subject' => '',
        'type' => '',
        'status' => '',
        'priority' => '',
        'assigned_to' => '',
        'department_id' => '',
        'description' => '',
    ];

    public string $replyMessage = '';

    public $attachments = [];

    public ?int $confirmingNoteId = null;

    public $noteInputKey = null;

    public string $note = '';

    public string $noteColor = 'sky';

    public bool $noteInternal = false;

    public function mount(Ticket $ticket)
    {
        // Check permissions
        $user = auth()->user();
        if (! $user->can('tickets.view')) {
            abort(403, 'You do not have permission to view tickets.');
        }

        // Role-based access control
        if ($user->hasRole('client') && $ticket->organization_id !== $user->organization_id) {
            abort(403, 'You can only view tickets from your organization.');
        }

        if ($user->hasRole('support')) {
            $hasAccess = $ticket->department_id === $user->department_id;
            
            // Also check department group access
            if (!$hasAccess && $user->department?->department_group_id) {
                $hasAccess = $user->department->department_group_id === $ticket->department?->department_group_id;
            }
            
            if (!$hasAccess) {
                abort(403, 'You can only view tickets from your department or department group.');
            }
        }

        $this->ticket = $ticket->load([
            'organization:id,name',
            'organization.contracts' => function($query) use ($ticket) {
                $query->select(['id', 'organization_id', 'department_id', 'contract_number', 'type', 'status', 'start_date', 'end_date', 'contract_value', 'currency'])
                      ->where('department_id', $ticket->department_id)
                      ->where('status', 'active')
                      ->orderBy('start_date', 'desc')
                      ->limit(1);
            },
            'department:id,name,department_group_id',
            'department.departmentGroup:id,name',
            'assigned:id,name',
            'client:id,name,email,organization_id',
            'notes' => function($query) {
                $query->select(['id', 'ticket_id', 'user_id', 'note', 'color', 'is_internal', 'created_at'])
                      ->with('user:id,name')
                      ->latest();
            },
            'attachments:id,attachable_id,attachable_type,original_name,stored_name,mime_type,size,is_image',
        ]);

        $this->ticket->setRelation(
            'messages',
            $ticket->messages()
                   ->select(['id', 'ticket_id', 'sender_id', 'message', 'created_at'])
                   ->with([
                       'sender:id,name',
                       'attachments:id,attachable_id,attachable_type,original_name,stored_name,mime_type,size,is_image'
                   ])
                   ->latest('created_at')
                   ->get()
        );

        $this->form = [
            'subject' => $ticket->subject,
            'type' => $ticket->type,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'assigned_to' => $ticket->assigned_to,
            'department_id' => $ticket->department_id,
            'description' => $ticket->description,
        ];

        $this->noteInputKey = uniqid();
    }

    #[Computed]
    public function canEdit()
    {
        $user = auth()->user();

        return $user->hasRole('admin') || $user->can('tickets.edit');
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

    #[Computed]
    public function activeContract()
    {
        return $this->ticket->organization->contracts->first();
    }

    public function enableEdit()
    {
        if (! $this->canEdit) {
            session()->flash('error', 'You do not have permission to edit this ticket.');

            return;
        }

        $this->editMode = true;
    }

    public function cancelEdit()
    {
        $this->editMode = false;
        $this->form = [
            'subject' => $this->ticket->subject,
            'type' => $this->ticket->type,
            'status' => $this->ticket->status,
            'priority' => $this->ticket->priority,
            'assigned_to' => $this->ticket->assigned_to,
            'department_id' => $this->ticket->department_id,
            'description' => $this->ticket->description,
        ];
    }

    public function updateTicket()
    {
        try {
            if (! $this->canEdit) {
                session()->flash('error', 'You do not have permission to edit this ticket.');
                return;
            }

            $this->validate([
                'form.type' => TicketType::validationRule(),
                'form.status' => TicketStatus::validationRule(),
                'form.priority' => TicketPriority::validationRule(),
                'form.assigned_to' => 'nullable|exists:users,id',
                'form.department_id' => 'required|exists:departments,id',
            ]);

            // Remove subject and description from update to prevent modification
            $updateData = $this->form;
            unset($updateData['subject'], $updateData['description']);
            
            $this->ticket->update($updateData);
            $this->ticket->refresh();
            $this->editMode = false;

            session()->flash('message', 'Ticket updated successfully.');

            // Refresh messages with optimized eager loading
            $this->ticket->setRelation(
                'messages',
                $this->ticket->messages()
                             ->select(['id', 'ticket_id', 'sender_id', 'message', 'created_at'])
                             ->with([
                                 'sender:id,name',
                                 'attachments:id,attachable_id,attachable_type,original_name,stored_name,mime_type,size,is_image'
                             ])
                             ->latest('created_at')
                             ->get()
            );
            
        } catch (\Exception $e) {
            logger()->error('Failed to update ticket', [
                'user_id' => auth()->id(),
                'ticket_id' => $this->ticket->id,
                'form_data' => $this->form,
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Failed to update ticket. Please try again or contact support if the problem persists.');
        }
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

            $this->replyMessage = '';
            $this->attachments = [];
            $this->activeInput = ''; // hide input after submit

            // Update first response time if this is the first response
            if (! $this->ticket->first_response_at && auth()->user()->hasRole(['support', 'admin'])) {
                $this->ticket->update([
                    'first_response_at' => now(),
                    'response_time_minutes' => $this->ticket->created_at->diffInMinutes(now()),
                ]);
            }

            $this->ticket = $this->ticket->fresh();
            $this->ticket->setRelation(
                'messages',
                $this->ticket->messages()
                             ->select(['id', 'ticket_id', 'sender_id', 'message', 'created_at'])
                             ->with([
                                 'sender:id,name',
                                 'attachments:id,attachable_id,attachable_type,original_name,stored_name,mime_type,size,is_image'
                             ])
                             ->latest('created_at')
                             ->get()
            );

            session()->flash('message', 'Message sent successfully.');
            
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

        TicketNote::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => Auth::id(),
            'is_internal' => $this->noteInternal,
            'color' => $this->noteColor,
            'note' => $this->note,
        ]);

        $this->note = '';
        $this->noteColor = 'sky';
        $this->noteInternal = false;
        $this->noteInputKey = uniqid();
        $this->activeInput = ''; // hide input after submit

        $this->ticket->refresh()->load(['notes' => function($query) {
            $query->select(['id', 'ticket_id', 'user_id', 'note', 'color', 'is_internal', 'created_at'])
                  ->with('user:id,name')
                  ->latest();
        }]);
        session()->flash('message', 'Note added successfully.');
        $this->dispatch('noteAdded', ['ticket' => $this->ticket]);
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
        $storedName = Str::uuid() . '.' . $extension;
        
        // Store the file
        $path = $file->storeAs('attachments', $storedName, 'local');
        
        // Determine if it's an image
        $isImage = in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
        
        // Create attachment record
        Attachment::create([
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
    }

    public function render()
    {
        $user = auth()->user();

        // Filter departments and users based on role
        $departments = collect();
        $users = collect();

        if ($user->hasRole('admin')) {
            $departments = Department::orderBy('name')->get();
            $users = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['support', 'admin']);
            })->orderBy('name')->get();
        } elseif ($user->hasRole('support')) {
            if ($user->department?->department_group_id) {
                // Show all departments in the same group
                $departments = Department::where('department_group_id', $user->department->department_group_id)
                    ->orderBy('name')->get();
                $users = User::whereHas('department', function ($q) use ($user) {
                    $q->where('department_group_id', $user->department->department_group_id);
                })->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['support', 'admin']);
                })->orderBy('name')->get();
            } else {
                // Fallback to just their department
                $departments = Department::where('id', $user->department_id)->get();
                $users = User::where('department_id', $user->department_id)
                    ->whereHas('roles', function ($q) {
                        $q->whereIn('name', ['support', 'admin']);
                    })->orderBy('name')->get();
            }
        }

        return view('livewire.view-ticket', [
            'departments' => $departments,
            'users' => $users,
            'statusOptions' => TicketStatus::options(),
            'priorityOptions' => TicketPriority::options(),
            'typeOptions' => TicketType::options(),
        ]);
    }

    // TODO: Add any additional methods needed for ticket management, such as filtering messages or notes, or handling file uploads.
    /*🧩 UI/UX Enhancements
    Animate reply/note form toggle (with Alpine or transition utilities)

    Scroll to bottom on new reply/message

    Highlight new messages or notes temporarily (e.g., yellow flash)

    Add icons to messages (based on user role/type)

    Show relative timestamps (e.g., "5 minutes ago", via Carbon::diffForHumans())

    🗂️ Ticket Content Management
    Add file attachments to replies or notes

    Support Markdown or rich text formatting in replies

    Allow editing of notes/messages (limited to author)

    Add reply tagging (e.g. @owner) or quick mentions

    🔐 Authorization & Roles
    Restrict note visibility if internal (hide from unauthorized users)

    Show owner’s avatar or badge next to replies

    Allow admin-only visibility for certain notes or replies

    📡 Livewire & Real-time
    Convert messages/notes to Livewire polling or Pusher for real-time updates

    Auto-refresh when ticket is updated by another user

    📊 Data & Insights
    Show message count / note count badges

    Add activity log (who updated status, department, etc.)

    Visualize note color usage or ticket priority trend

    🧪 QA & Stability
    Add debounce to form inputs to prevent spammy writes

    Persist open tab/form state on refresh (optional: Alpine + localStorage)

    Add validation feedback indicators near form fields
    */
}
