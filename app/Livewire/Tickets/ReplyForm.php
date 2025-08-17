<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketCcRecipient;
use App\Models\Attachment;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReplyForm extends Component
{
    use WithFileUploads, AuthorizesRequests;

    public Ticket $ticket;
    public string $replyMessage = '';
    public string $replyStatus = 'in_progress';
    public array $attachments = [];
    public string $cc = '';
    public bool $show = false;

    protected $rules = [
        'replyMessage' => 'required|string|max:2000',
        'replyStatus' => 'required|string',
        'attachments.*' => 'file|max:10240',
        'cc' => 'nullable|string'
    ];

    protected $listeners = ['reply:toggle' => 'toggle'];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    public function sendMessage(): void
    {
        $this->validate();

        $this->authorize('reply', $this->ticket);
        $this->authorize('setStatus', [$this->ticket, $this->replyStatus]);

        $storedFiles = [];
        
        try {
            DB::transaction(function () use (&$storedFiles) {
                // Create the ticket message
                $ticketMessage = TicketMessage::create([
                    'ticket_id' => $this->ticket->id,
                    'sender_id' => auth()->id(),
                    'message' => $this->replyMessage,
                    'is_system_message' => false,
                    'is_internal' => false,
                ]);

                // Process attachments if any were uploaded
                if (!empty($this->attachments)) {
                    foreach ($this->attachments as $attachment) {
                        // Store the file
                        $storedPath = $attachment->store('ticket-attachments', 'local');
                        
                        if (!$storedPath) {
                            throw new \Exception('Failed to store attachment file: ' . $attachment->getClientOriginalName());
                        }
                        
                        // Track stored files for cleanup on failure
                        $storedFiles[] = $storedPath;
                        
                        // Create attachment record
                        Attachment::create([
                            'attachable_type' => TicketMessage::class,
                            'attachable_id' => $ticketMessage->id,
                            'original_name' => $attachment->getClientOriginalName(),
                            'stored_name' => basename($storedPath),
                            'path' => $storedPath,
                            'disk' => 'local',
                            'mime_type' => $attachment->getMimeType(),
                            'size' => $attachment->getSize(),
                            'extension' => $attachment->getClientOriginalExtension(),
                            'is_public' => false,
                            'is_image' => in_array(strtolower($attachment->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'], true),
                            'uploaded_by' => auth()->id(),
                        ]);
                    }
                }

                // Update ticket status
                $this->ticket->update(['status' => $this->replyStatus]);
                
                // Create system message for status change if status changed
                if ($this->ticket->wasChanged('status')) {
                    TicketMessage::create([
                        'ticket_id' => $this->ticket->id,
                        'sender_id' => auth()->id(),
                        'message' => 'Status changed to ' . $this->replyStatus . ' by ' . auth()->user()->name,
                        'is_system_message' => true,
                        'is_internal' => false,
                    ]);
                }
                
                // Process CC recipients if provided
                if (!empty($this->cc)) {
                    $ccEmails = array_map('trim', explode(',', $this->cc));
                    $validCcEmails = [];
                    
                    foreach ($ccEmails as $email) {
                        $email = strtolower(trim($email));
                        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $validCcEmails[] = $email;
                        }
                    }
                    
                    // Remove duplicates and process each valid email
                    $uniqueCcEmails = array_unique($validCcEmails);
                    foreach ($uniqueCcEmails as $email) {
                        TicketCcRecipient::updateOrCreate(
                            [
                                'ticket_id' => $this->ticket->id,
                                'email' => $email,
                            ],
                            [
                                'active' => true,
                            ]
                        );
                    }
                }
            });
            
            // Success - reset form and refresh
            $this->reset(['replyMessage', 'attachments', 'cc']);
            session()->flash('message', 'Reply sent successfully.');
            $this->dispatch('thread:refresh')->to(ConversationThread::class);
            
        } catch (\Exception $e) {
            // Clean up any stored files if transaction failed
            foreach ($storedFiles as $filePath) {
                if (Storage::disk('local')->exists($filePath)) {
                    Storage::disk('local')->delete($filePath);
                }
            }
            
            // Log the error
            logger()->error('Failed to send reply', [
                'ticket_id' => $this->ticket->id,
                'user_id' => auth()->id(),
                'stored_files' => $storedFiles,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Show user-friendly error message
            session()->flash('error', 'Failed to send reply. Please try again.');
            return;
        }
    }

    public function render()
    {
        return view('livewire.tickets.reply-form');
    }
}
