<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketMessageAttachment;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateTicketWizard extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public array $form = [
        'subject' => '',
        'organization_id' => '',
        'department_id' => '',
        'owner_id' => null,
        'description' => '',
    ];
    public array $attachments = [];

    protected $rules = [
        'form.subject' => 'required|string|max:255',
        'form.organization_id' => 'required|exists:organizations,id',
        'form.department_id' => 'required|exists:departments,id',
        'form.owner_id' => 'nullable|exists:users,id',
        'form.description' => 'required|string',
        'attachments.*' => 'file|max:10240',
    ];

    public function next(): void
    {
        $this->validateOnlyStep();
        $this->step = 2;
    }

    public function back(): void
    {
        $this->step = 1;
    }

    protected function validateOnlyStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'form.subject' => $this->rules['form.subject'],
                'form.organization_id' => $this->rules['form.organization_id'],
                'form.department_id' => $this->rules['form.department_id'],
                'form.owner_id' => $this->rules['form.owner_id'],
            ]);
        }
    }

    public function save()
    {
        $data = $this->validate();
        $ticket = Ticket::create([
            'subject' => $data['form']['subject'],
            'status' => 'open',
            'priority' => 'normal',
            'organization_id' => $data['form']['organization_id'],
            'client_id' => auth()->id(),
            'department_id' => $data['form']['department_id'],
            'owner_id' => empty($data['form']['owner_id']) ? null : $data['form']['owner_id'],
        ]);

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => auth()->id(),
            'message' => $data['form']['description'],
            'is_system_message' => false,
        ]);

        foreach ($this->attachments as $file) {
            $path = $file->store('ticket-attachments');
            TicketMessageAttachment::create([
                'ticket_message_id' => $message->id,
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return redirect()->route('tickets.show', $ticket);
    }

    public function render()
    {
        return view('livewire.tickets.create-ticket-wizard', [
            // TODO: provide options for select fields
        ]);
    }
}
