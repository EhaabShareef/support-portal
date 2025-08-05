<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketNote;
use App\Models\User;
use Livewire\Component;

class CreateTicket extends Component
{
    public array $form = [
        'subject'   => '',
        'type'      => 'issue',
        'org_id'    => '',
        'client_id' => '',
        'dept_id'   => '',
        'status'    => 'in progress',
        'priority'  => 'Normal',
        'owner_id'  => '',
    ];

    public function rules(): array
    {
        return [
            'form.subject'   => 'required|string|max:50',
            'form.type'      => 'required|in:issue,feedback,bug,lead,task',
            'form.org_id'    => 'required|exists:organizations,id',
            'form.client_id' => 'nullable', // removed validation — will set it manually
            'form.dept_id'   => 'required|exists:departments,id',
            'form.priority'  => 'required|in:Low,Normal,High,Serious Business Impact',
            'form.owner_id' => 'nullable|exists:users,id',

        ];
    }

    public function submit()
    {
        $validated = $this->validate()['form'];
        $validated['client_id'] = auth()->id();
        $validated['status'] = 'open';

        if (empty($validated['owner_id'])) {
            $validated['owner_id'] = null;
        }

        $ticket = Ticket::create($validated);

        // Add dependent note about calling hotline
        TicketNote::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'is_internal' => false,
            'color' => 'blue',
            'note' => 'Please note: For urgent assistance or immediate support regarding this ticket, you may contact our technical hotline at [HOTLINE_NUMBER]. Our support team is available to provide additional guidance and ensure timely resolution of your request.',
        ]);

        session()->flash('message', 'Ticket created successfully.');

        return redirect()->route('tickets.show', $ticket);
    }

    public function render()
    {
        return view('livewire.create-ticket', [
            'organizations' => Organization::all(),
            'departments' => Department::all(),
            'users' => User::all(),
        ]);
    }
}
