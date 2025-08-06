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
        'organization_id' => '',
        'client_id' => '',
        'department_id' => '',
        'status'    => 'in progress',
        'priority'  => 'normal',
        'assigned_to' => '',
    ];

    public function rules(): array
    {
        return [
            'form.subject'   => 'required|string|max:255',
            'form.type'      => 'required|in:issue,feedback,bug,lead,task,incident,request',
            'form.organization_id' => 'required|exists:organizations,id',
            'form.client_id' => 'nullable', // will be set to authenticated user
            'form.department_id' => 'required|exists:departments,id',
            'form.priority'  => 'required|in:low,normal,high,urgent,critical',
            'form.assigned_to' => 'nullable|exists:users,id',
        ];
    }

    public function submit()
    {
        $user = auth()->user();
        $validated = $this->validate()['form'];
        
        // Override security-sensitive fields
        $validated['client_id'] = $user->id;
        $validated['status'] = 'open';
        
        // For clients, force their organization
        if ($user->hasRole('Client')) {
            $validated['organization_id'] = $user->organization_id;
        }

        if (empty($validated['assigned_to'])) {
            $validated['assigned_to'] = null;
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
