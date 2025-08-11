<?php

namespace App\Livewire;

use App\Enums\TicketPriority;
use App\Models\Department;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketNote;
use App\Models\User;
use Livewire\Component;

class CreateTicket extends Component
{
    public bool $showCriticalConfirmation = false;
    public bool $criticalConfirmed = false;

    public function mount(): void
    {
        // Check permissions before allowing access to ticket creation
        $user = auth()->user();
        if (!$user || !$user->can('tickets.create')) {
            abort(403, 'Insufficient permissions to create tickets.');
        }
    }

    public array $form = [
        'subject'   => '',
        'organization_id' => '',
        'client_id' => '',
        'department_id' => '',
        'status'    => 'in progress',
        'priority'  => 'normal',
        'assigned_to' => '',
    ];

    public function rules(): array
    {
        $user = auth()->user();
        
        return [
            'form.subject'   => 'required|string|max:255',
            'form.organization_id' => 'required|exists:organizations,id',
            // Clients cannot select client, Admins/Agents can select on behalf of clients
            'form.client_id' => $user->hasRole('client') ? 'nullable' : 'required|exists:users,id',
            'form.department_id' => 'required|exists:departments,id',
            'form.priority'  => TicketPriority::validationRule(),
            'form.assigned_to' => 'nullable|exists:users,id',
        ];
    }

    protected $messages = [
        'form.subject.required' => 'Please enter a subject for your ticket.',
        'form.subject.max' => 'Subject must not exceed 255 characters.',
        'form.organization_id.required' => 'Please select an organization.',
        'form.organization_id.exists' => 'The selected organization is invalid.',
        'form.client_id.required' => 'Please select a client for this ticket.',
        'form.client_id.exists' => 'The selected client is invalid.',
        'form.department_id.required' => 'Please select a department.',
        'form.department_id.exists' => 'The selected department is invalid.',
        'form.priority.required' => 'Please select a priority level.',
        'form.assigned_to.exists' => 'The selected assignee is invalid.',
    ];

    public function submit()
    {
        try {
            $user = auth()->user();
            $validated = $this->validate()['form'];
            
            // Check for critical priority confirmation
            if ($validated['priority'] === 'critical' && !$this->criticalConfirmed) {
                $this->showCriticalConfirmation = true;
                return;
            }
            
            // Override security-sensitive fields
            $validated['status'] = 'open';
            $validated['critical_confirmed'] = $this->criticalConfirmed;
            
            // For clients, force their organization and set them as the client
            if ($user->hasRole('client')) {
                $validated['organization_id'] = $user->organization_id;
                $validated['client_id'] = $user->id;
            }
            // For Admins/Agents, validate client selection
            elseif (!$user->hasRole('client') && empty($validated['client_id'])) {
                $this->addError('form.client_id', 'Please select a client for this ticket.');
                return;
            }

            if (empty($validated['assigned_to'])) {
                $validated['assigned_to'] = null;
            }

            $ticket = Ticket::create($validated);

            // Add hotline note only for critical and urgent tickets
            if (in_array($validated['priority'], ['critical', 'urgent'])) {
                TicketNote::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => auth()->id(),
                    'is_internal' => false,
                    'color' => 'red',
                    'note' => 'PRIORITY ALERT: This ticket has been marked as ' . strtoupper($validated['priority']) . '. For immediate assistance, please contact our technical hotline at [HOTLINE_NUMBER]. Our support team is available to provide additional guidance and ensure timely resolution of your request.',
                ]);
            }

            session()->flash('message', 'Ticket created successfully.');

            return redirect()->route('tickets.show', $ticket);
            
        } catch (\Exception $e) {
            logger()->error('Failed to create ticket', [
                'user_id' => auth()->id(),
                'form_data' => $this->form,
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Failed to create ticket. Please try again or contact support if the problem persists.');
            return;
        }
    }

    public function confirmCriticalPriority()
    {
        $this->criticalConfirmed = true;
        $this->showCriticalConfirmation = false;
        $this->submit(); // Retry submission
    }

    public function cancelCriticalConfirmation()
    {
        $this->showCriticalConfirmation = false;
        $this->criticalConfirmed = false;
        // Reset priority to high
        $this->form['priority'] = 'high';
    }

    public function render()
    {
        $user = auth()->user();
        
        // Get available clients for Admin/Agent ticket creation
        $clients = collect();
        if ($user->hasRole('admin') || $user->hasRole('support')) {
            $clients = User::whereHas('roles', function ($q) {
                $q->where('name', 'client');
            })->orderBy('name')->get();
        }
        
        return view('livewire.create-ticket', [
            'organizations' => Organization::all(),
            'departments' => Department::all(),
            'users' => User::all(),
            'clients' => $clients,
            'priorityOptions' => TicketPriority::options(),
        ]);
    }
}
