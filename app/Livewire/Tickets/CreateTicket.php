<?php

namespace App\Livewire\Tickets;

use App\Enums\TicketPriority;
use App\Models\Department;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateTicket extends Component
{
    use WithFileUploads;
    
    public int $currentStep = 1;
    public bool $showCriticalConfirmation = false;
    public bool $criticalConfirmed = false;
    public $attachments = [];
    
    public array $form = [
        'subject' => '',
        'description' => '',
        'organization_id' => '',
        'client_id' => '',
        'department_id' => '',
        'priority' => 'normal',
        'selected_hardware' => [],
        'hardware_serials' => [], // Store selected serials for each hardware
    ];

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->can('tickets.create')) {
            abort(403, 'Insufficient permissions to create tickets.');
        }
        
        // Auto-set organization for clients
        if ($user->hasRole('client')) {
            $this->form['organization_id'] = $user->organization_id;
            $this->form['client_id'] = $user->id;
        }
    }

    public function nextStep()
    {
        $this->validate([
            'form.subject' => 'required|string|max:255',
            'form.organization_id' => 'required|exists:organizations,id',
            'form.department_id' => 'required|exists:departments,id',
            'form.priority' => 'required|in:low,normal,high,urgent,critical',
        ]);

        if (!$this->isClientUser()) {
            $this->validate([
                'form.client_id' => 'required|exists:users,id',
            ]);
        }

        // Check if priority is critical or urgent
        if (in_array($this->form['priority'], ['critical', 'urgent'])) {
            $this->showCriticalConfirmation = true;
            return;
        }

        // Check if this is a hardware department
        $isHardware = $this->isHardwareDepartment();
        \Log::info('Is hardware department: ' . ($isHardware ? 'true' : 'false') . ', Setting step to: ' . ($isHardware ? '2' : '3'));
        
        if ($isHardware) {
            $this->currentStep = 2;
        } else {
            $this->currentStep = 3; // Skip hardware step for non-hardware departments
        }
    }

    public function confirmCriticalAndContinue()
    {
        \Log::info('confirmCriticalAndContinue called, criticalConfirmed: ' . ($this->criticalConfirmed ? 'true' : 'false'));
        
        if ($this->criticalConfirmed) {
            $this->showCriticalConfirmation = false;
            $this->criticalConfirmed = false;
            
            // Check if this is a hardware department
            if ($this->isHardwareDepartment()) {
                $this->currentStep = 2;
            } else {
                $this->currentStep = 3; // Skip hardware step for non-hardware departments
            }
        }
    }

    public function cancelCriticalConfirmation()
    {
        $this->showCriticalConfirmation = false;
        $this->criticalConfirmed = false;
    }

    public function updatedCriticalConfirmed()
    {
        \Log::info('Checkbox changed to: ' . ($this->criticalConfirmed ? 'true' : 'false'));
    }

    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments); // Re-index array
        }
    }

    public function previousStep()
    {
        if ($this->currentStep === 3) {
            // If we're on step 3 (description), go back to step 2 (hardware) if it's a hardware department
            if ($this->isHardwareDepartment()) {
                $this->currentStep = 2;
            } else {
                $this->currentStep = 1; // Skip hardware step for non-hardware departments
            }
        } elseif ($this->currentStep === 2) {
            $this->currentStep = 1;
        }
    }

    public function nextStepFromHardware()
    {
        $this->currentStep = 3;
    }

    public function isHardwareDepartment()
    {
        if (empty($this->form['department_id'])) {
            return false;
        }

        $department = \App\Models\Department::with('departmentGroup')->find($this->form['department_id']);
        if (!$department) {
            return false;
        }

        \Log::info('Department: ' . $department->name . ', Group: ' . ($department->departmentGroup ? $department->departmentGroup->name : 'null'));
        
        // Check if department belongs to Hardware group
        return $department->departmentGroup && $department->departmentGroup->name === 'Hardware';
    }

    public function submit()
    {
        $this->validate([
            'form.description' => 'required|string|max:2000',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
        ]);

        try {
            $user = auth()->user();
            
            \Log::info('Creating ticket with data: ' . json_encode($this->form));
            
            // Create ticket
            $ticket = Ticket::create([
                'subject' => $this->form['subject'],
                'organization_id' => $this->form['organization_id'],
                'client_id' => $this->form['client_id'],
                'department_id' => $this->form['department_id'],
                'priority' => $this->form['priority'],
                'status' => 'open',
                'owner_id' => null,
                // uuid and ticket_number will be auto-generated by the model
            ]);

            // Handle hardware selection and linking
            if (!empty($this->form['selected_hardware'])) {
                \Log::info('Selected hardware: ' . json_encode($this->form['selected_hardware']));
                
                // Get hardware details
                $hardwareItems = \App\Models\OrganizationHardware::with(['type', 'contract', 'serials'])
                    ->whereIn('id', $this->form['selected_hardware'])
                    ->get();
                
                // Link hardware to ticket
                $hardwareData = [];
                foreach ($this->form['selected_hardware'] as $hardwareId) {
                    $hardwareData[$hardwareId] = [
                        'maintenance_note' => null // Can be updated later
                    ];
                }
                
                $ticket->hardware()->sync($hardwareData);
                
                \Log::info('Hardware linked for ticket: ' . $ticket->id);
            }
            
            \Log::info('Ticket created successfully with ID: ' . $ticket->id);
            
            // Create first message
            $message = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $ticket->client_id,
                'message' => $this->form['description'],
            ]);
            
            \Log::info('Message created successfully with ID: ' . $message->id);

            // Handle file attachments
            if (!empty($this->attachments)) {
                \Log::info('Processing ' . count($this->attachments) . ' attachments');
                foreach ($this->attachments as $index => $attachment) {
                    if ($attachment) {
                        \Log::info('Processing attachment ' . ($index + 1) . ': ' . $attachment->getClientOriginalName());
                        
                        // Use the same path structure as reply attachments
                        $year = now()->format('Y');
                        $month = now()->format('m');
                        $path = $attachment->store("tickets/{$ticket->id}/attachments/{$year}/{$month}", 'local');
                        
                        // Create attachment record
                        $attachmentRecord = \App\Models\TicketMessageAttachment::create([
                            'ticket_message_id' => $message->id,
                            'disk' => 'local',
                            'path' => $path,
                            'original_name' => $attachment->getClientOriginalName(),
                            'mime_type' => $attachment->getMimeType(),
                            'size' => $attachment->getSize(),
                        ]);
                        
                        \Log::info('Attachment record created with ID: ' . $attachmentRecord->id);
                    }
                }
            }

            \Log::info('Ticket creation completed successfully, redirecting to ticket view');
            session()->flash('message', 'Ticket created successfully.');
            return redirect()->route('tickets.show', $ticket);
            
        } catch (\Exception $e) {
            \Log::error('Failed to create ticket: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            session()->flash('error', 'Failed to create ticket. Please try again.');
        }
    }

    public function updatedFormOrganizationId()
    {
        $this->form['client_id'] = '';
        
        // Debug: Log when organization changes
        \Log::info('Organization changed to: ' . $this->form['organization_id']);
        
        // Check if there are any users for this organization
        $clientCount = User::where('organization_id', $this->form['organization_id'])
            ->where('is_active', true)
            ->count();
        
        \Log::info('User count for org ' . $this->form['organization_id'] . ': ' . $clientCount);
    }

    public function updatedFormDepartmentId()
    {
        // Reset hardware selection when department changes
        $this->form['selected_hardware'] = [];
        $this->form['hardware_serials'] = [];
        
        // Ensure current step is valid for the new department
        $this->validateCurrentStep();
    }

    /**
     * Ensure the current step is valid for the current department
     */
    private function validateCurrentStep(): void
    {
        $isHardware = $this->isHardwareDepartment();
        $maxSteps = $isHardware ? 3 : 2;
        
        // If we're on step 2 and it's not a hardware department, skip to step 3
        if ($this->currentStep === 2 && !$isHardware) {
            $this->currentStep = 3;
        }
        
        // If we're on step 3 and it's a hardware department but we haven't completed step 2, go back to step 2
        if ($this->currentStep === 3 && $isHardware && empty($this->form['selected_hardware'])) {
            $this->currentStep = 2;
        }
        
        // Ensure step is within valid range
        if ($this->currentStep > $maxSteps) {
            $this->currentStep = $maxSteps;
        }
        
        \Log::info("Step validated: Current step {$this->currentStep}, Max steps {$maxSteps}, Is hardware: " . ($isHardware ? 'true' : 'false'));
    }

    public function isClientUser(): bool
    {
        return auth()->user()->hasRole('client');
    }

    public function getAvailableClientsProperty()
    {
        if (empty($this->form['organization_id'])) {
            return collect();
        }

        return User::where('organization_id', $this->form['organization_id'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getAvailableHardwareProperty()
    {
        if (empty($this->form['organization_id'])) {
            return collect();
        }

        return \App\Models\OrganizationHardware::where('organization_id', $this->form['organization_id'])
            ->with(['type', 'contract', 'serials'])
            ->orderBy('brand')
            ->orderBy('model')
            ->get();
    }

        public function render()
    {
        return view('livewire.tickets.create-ticket', [
            'organizations' => Organization::where('is_active', true)->get(),
            'departments' => Department::where('is_active', true)->get(),
            'users' => User::where('is_active', true)->get(),
            'clients' => $this->availableClients,
            'hardware' => $this->availableHardware,
            'priorityOptions' => [
                'low' => 'Low',
                'normal' => 'Normal',
                'high' => 'High',
                'urgent' => 'Urgent',
                'critical' => 'Critical',
            ],
        ]);
    }
}
