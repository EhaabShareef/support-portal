<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketMessageAttachment;
use App\Models\TicketCcRecipient;
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
    public string $replyStatus;
    public array $attachments = [];
    
    // Reference to attachment upload component
    public $attachmentUploadComponent;
    public string $cc = '';
    public bool $show = false;

    protected function rules()
    {
        $rules = [
            'replyMessage' => 'required|string|max:2000',
            'replyStatus' => 'required|string',
            'cc' => 'nullable|string'
        ];
        
        // Only add attachment validation if there are attachments
        if (!empty($this->attachments)) {
            $maxSizeKB = config('app.max_file_size', 10240); // 10MB in KB
            $rules['attachments.*'] = 'file|max:' . $maxSizeKB;
        }
        
        return $rules;
    }

    protected $listeners = ['reply:toggle' => 'toggle'];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->replyStatus = config('tickets.default_reply_status', 'in_progress');
    }

    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    public function sendMessage(): void
    {
        // Debug logging
        logger()->info('ReplyForm sendMessage called', [
            'ticket_id' => $this->ticket->id,
            'user_id' => auth()->id(),
            'attachments_count' => count($this->attachments),
            'reply_message_length' => strlen($this->replyMessage),
            'reply_status' => $this->replyStatus,
            'max_file_size_kb' => config('app.max_file_size', 10240),
            'attachment_sizes' => collect($this->attachments)->map(function($att) {
                return [
                    'name' => $att->getClientOriginalName(),
                    'size_kb' => round($att->getSize() / 1024, 2)
                ];
            })->toArray()
        ]);

        try {
            $this->validate($this->rules());
            logger()->info('Validation passed');
        } catch (\Exception $e) {
            logger()->error('Validation failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        try {
            $this->authorize('reply', $this->ticket);
            $this->authorize('setStatus', [$this->ticket, $this->replyStatus]);
            logger()->info('Authorization passed');
        } catch (\Exception $e) {
            logger()->error('Authorization failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        $storedFiles = [];
        
        logger()->info('Starting database transaction');
        
        try {
            DB::transaction(function () use (&$storedFiles) {
                logger()->info('Inside transaction - creating ticket message');
                // Create the ticket message
                $ticketMessage = TicketMessage::create([
                    'ticket_id' => $this->ticket->id,
                    'sender_id' => auth()->id(),
                    'message' => $this->replyMessage,
                    'is_system_message' => false,
                    'is_internal' => false,
                ]);
                
                logger()->info('Ticket message created', ['message_id' => $ticketMessage->id]);

                // Process attachments if any were uploaded
                if (!empty($this->attachments)) {
                    logger()->info('Processing attachments', [
                        'attachment_count' => count($this->attachments),
                        'attachments' => collect($this->attachments)->map(function($att) {
                            return [
                                'name' => $att->getClientOriginalName(),
                                'size' => $att->getSize(),
                                'mime' => $att->getMimeType()
                            ];
                        })->toArray()
                    ]);
                    
                    foreach ($this->attachments as $attachment) {
                        // Create organized storage path
                        $year = date('Y');
                        $month = date('m');
                        $storedPath = $attachment->store("tickets/{$this->ticket->id}/attachments/{$year}/{$month}", 'local');
                        
                        if (!$storedPath) {
                            throw new \Exception('Failed to store attachment file: ' . $attachment->getClientOriginalName());
                        }
                        
                        // Track stored files for cleanup on failure
                        $storedFiles[] = $storedPath;
                        
                        // Create attachment record
                        TicketMessageAttachment::create([
                            'ticket_message_id' => $ticketMessage->id,
                            'original_name' => $attachment->getClientOriginalName(),
                            'path' => $storedPath,
                            'disk' => 'local',
                            'mime_type' => $attachment->getMimeType(),
                            'size' => $attachment->getSize(),
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
                
                logger()->info('Transaction completed successfully');
            });
            
            logger()->info('Outside transaction - resetting form');
            
            // Success - reset form and refresh
            $this->reset(['replyMessage', 'attachments', 'cc']);
            $this->show = false;
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

    public function removeAttachment($index): void
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments); // Re-index array
        }
    }



    public function render()
    {
        return view('livewire.tickets.reply-form');
    }
}
