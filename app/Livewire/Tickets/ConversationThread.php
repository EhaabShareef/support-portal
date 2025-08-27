<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Livewire\Component;

class ConversationThread extends Component
{
    protected $listeners = ['thread:refresh' => 'refreshConversation'];

    public Ticket $ticket;

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->refreshConversation();
    }

    public function refreshConversation(): void
    {
        $messages = $this->ticket->messages()
            ->where('is_log', false)
            ->select(['id','ticket_id','sender_id','message','is_system_message','created_at'])
            ->with(['sender:id,name', 'attachments:id,uuid,ticket_message_id,original_name,path,mime_type,size'])
            ->selectRaw("'message' as type")
            ->get();

        $publicNotes = $this->ticket->notes()
            ->where('is_internal', false)
            ->select(['id','user_id as sender_id','note as message','created_at'])
            ->with('user:id,name')
            ->selectRaw("'note' as type")
            ->selectRaw("null as ticket_id")
            ->selectRaw("false as is_system_message")
            ->get()
            ->map(function ($note) {
                $note->sender = $note->user;
                $note->attachments = collect();
                return $note;
            });

        $conversation = $messages->concat($publicNotes)->sortByDesc('created_at')->values();
        
        // Filter out system messages if configured to hide them (except close/reopen)
        if (config('app.hide_system_messages', false)) {
            $conversation = $conversation->filter(function ($item) {
                if (!$item->is_system_message) {
                    return true; // Keep non-system messages
                }
                
                // Keep close and reopen messages
                $message = strtolower($item->message);
                if (str_contains($message, 'closed') || str_contains($message, 'reopened')) {
                    return true;
                }
                
                // Hide other system messages
                return false;
            })->values();
        }
        
        $this->ticket->setRelation('conversation', $conversation);
    }

    public function openAttachmentPreview($attachmentId): void
    {
        logger()->info('=== ConversationThread::openAttachmentPreview called ===', [
            'attachmentId' => $attachmentId,
            'attachmentId_type' => gettype($attachmentId)
        ]);
        $this->dispatch('open-attachment-preview', ['id' => $attachmentId]);
    }

    public function openPreviewById($attachmentId): void
    {
        logger()->info('=== ConversationThread::openPreviewById called ===', [
            'attachmentId' => $attachmentId,
            'attachmentId_type' => gettype($attachmentId)
        ]);
        $this->dispatch('open-attachment-preview', ['id' => $attachmentId]);
    }

    public function openAttachmentPreviewById($attachmentId): void
    {
        logger()->info('=== ConversationThread::openAttachmentPreviewById called ===', [
            'attachmentId' => $attachmentId,
            'attachmentId_type' => gettype($attachmentId)
        ]);
        $this->dispatch('open-attachment-preview', ['id' => $attachmentId]);
    }

    public function render()
    {
        return view('livewire.tickets.conversation-thread');
    }
}
