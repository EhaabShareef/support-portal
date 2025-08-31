<?php

namespace App\Livewire\Settings\Tickets;

use Livewire\Component;
use App\Services\TicketColorService;
use App\Models\TicketStatus;
use App\Models\DepartmentGroup;
use Illuminate\Support\Str;

class Status extends Component
{
    public $statuses = [];
    public $departmentGroups = [];
    
    // Form properties for creating/editing status
    public $editingStatus = null;
    public $statusName = '';
    public $statusKey = '';
    public $statusDescription = '';
    public $statusColor = ['bg' => '#f3f4f6', 'text' => '#374151'];
    public $sortOrder = 0;
    public $isProtected = false;
    public $isActive = true;
    
    // Department group assignments
    public $assignedDepartmentGroups = [];
    public $availableDepartmentGroups = [];
    
    // UI state
    public $showFlash = false;
    public $flashMessage = '';
    public $flashType = 'success';
    public $showCreateForm = false;
    public $showEditForm = false;
    public $showAssignForm = false;
    public $selectedStatusForAssignment = null;

    protected $ticketColorService;

    public function boot(TicketColorService $ticketColorService)
    {
        $this->ticketColorService = $ticketColorService;
    }

    public function mount()
    {
        $this->loadStatuses();
        $this->loadDepartmentGroups();
    }

    public function loadStatuses()
    {
        $this->statuses = TicketStatus::with('departmentGroups')->orderBy('sort_order')->orderBy('name')->get();
    }

    public function getStatusColors()
    {
        $colors = [];
        foreach ($this->statuses as $status) {
            $colors[$status->id] = [
                'bg' => $status->color,
                'text' => $this->ticketColorService->getContrastColor($status->color)
            ];
        }
        return $colors;
    }

    public function loadDepartmentGroups()
    {
        $this->departmentGroups = DepartmentGroup::orderBy('name')->get();
        $this->availableDepartmentGroups = $this->departmentGroups->pluck('name', 'id')->toArray();
    }

    public function createStatus()
    {
        $this->resetForm();
        $this->showCreateForm = true;
        $this->showEditForm = false;
        $this->showAssignForm = false;
    }

    public function editStatus($statusId)
    {
        $status = TicketStatus::findOrFail($statusId);
        $this->editingStatus = $status;
        $this->statusName = $status->name;
        $this->statusKey = $status->key;
        $this->statusDescription = $status->description ?? '';
        
        // Store background color from database, calculate text color
        $this->statusColor = [
            'bg' => $status->color ?? '#6b7280',
            'text' => $this->ticketColorService->getContrastColor($status->color ?? '#6b7280')
        ];
        
        $this->sortOrder = $status->sort_order;
        $this->isProtected = $status->is_protected;
        $this->isActive = $status->is_active;
        
        $this->showEditForm = true;
        $this->showCreateForm = false;
        $this->showAssignForm = false;
    }

    public function assignStatusToGroups($statusId)
    {
        $status = TicketStatus::findOrFail($statusId);
        $this->selectedStatusForAssignment = $status;
        $this->assignedDepartmentGroups = $status->departmentGroups->pluck('id')->toArray();
        
        $this->showAssignForm = true;
        $this->showCreateForm = false;
        $this->showEditForm = false;
    }

    public function saveStatus()
    {
        $this->validate([
            'statusName' => 'required|string|max:255',
            'statusKey' => 'required|string|max:50|unique:ticket_statuses,key,' . ($this->editingStatus?->id ?? ''),
            'statusDescription' => 'nullable|string|max:500',
            'statusColor.bg' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'sortOrder' => 'required|integer|min:0',
            'isProtected' => 'boolean',
            'isActive' => 'boolean',
        ]);

        try {
            $data = [
                'name' => $this->statusName,
                'key' => $this->statusKey,
                'description' => $this->statusDescription,
                'color' => $this->statusColor['bg'], // Store only background color
                'sort_order' => $this->sortOrder,
                'is_protected' => $this->isProtected,
                'is_active' => $this->isActive,
            ];

            if ($this->editingStatus) {
                // For protected statuses, only allow color and department group changes
                if ($this->editingStatus->is_protected) {
                    $data = [
                        'color' => $this->statusColor['bg'], // Store only background color
                    ];
                }
                $this->editingStatus->update($data);
                $this->showFlashMessage("Status '{$this->statusName}' updated successfully!", 'success');
            } else {
                TicketStatus::create($data);
                $this->showFlashMessage("Status '{$this->statusName}' created successfully!", 'success');
            }

            $this->loadStatuses();
            $this->resetForm();
            $this->showCreateForm = false;
            $this->showEditForm = false;
        } catch (\Exception $e) {
            $this->showFlashMessage('Error saving status: ' . $e->getMessage(), 'error');
        }
    }

    public function saveAssignments()
    {
        if (!$this->selectedStatusForAssignment) {
            return;
        }

        try {
            $this->selectedStatusForAssignment->departmentGroups()->sync($this->assignedDepartmentGroups);
            $this->showFlashMessage("Department group assignments updated for '{$this->selectedStatusForAssignment->name}'!", 'success');
            $this->loadStatuses();
            $this->showAssignForm = false;
            $this->selectedStatusForAssignment = null;
        } catch (\Exception $e) {
            $this->showFlashMessage('Error updating assignments: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteStatus($statusId)
    {
        try {
            $status = TicketStatus::findOrFail($statusId);
            
            if ($status->is_protected) {
                $this->showFlashMessage("Cannot delete protected status '{$status->name}'!", 'error');
                return;
            }
            
            $statusName = $status->name;
            $status->delete();
            
            $this->showFlashMessage("Status '{$statusName}' deleted successfully!", 'success');
            $this->loadStatuses();
        } catch (\Exception $e) {
            $this->showFlashMessage('Error deleting status: ' . $e->getMessage(), 'error');
        }
    }

    public function toggleStatusActive($statusId)
    {
        try {
            $status = TicketStatus::findOrFail($statusId);
            $status->update(['is_active' => !$status->is_active]);
            
            $action = $status->is_active ? 'activated' : 'deactivated';
            $this->showFlashMessage("Status '{$status->name}' {$action}!", 'success');
            $this->loadStatuses();
        } catch (\Exception $e) {
            $this->showFlashMessage('Error updating status: ' . $e->getMessage(), 'error');
        }
    }

    public function generateStatusKey()
    {
        if ($this->statusName) {
            $this->statusKey = Str::snake(Str::lower($this->statusName));
        }
    }

    public function setCustomBgColor($color)
    {
        $this->statusColor['bg'] = $color;
        $this->statusColor['text'] = $this->ticketColorService->getContrastColor($color);
    }

    public function getColorPalette()
    {
        return $this->ticketColorService->getColorPalette();
    }

    public function getColorDetails($colors)
    {
        return $this->ticketColorService->getColorDetails($colors);
    }

    public function getPreviewClasses($colors)
    {
        return $this->ticketColorService->getPreviewClasses($colors);
    }

    private function resetForm()
    {
        $this->editingStatus = null;
        $this->statusName = '';
        $this->statusKey = '';
        $this->statusDescription = '';
        $this->statusColor = ['bg' => '#f3f4f6', 'text' => '#374151'];
        $this->sortOrder = 0;
        $this->isProtected = false;
        $this->isActive = true;
    }

    private function showFlashMessage($message, $type = 'success')
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
        $this->showFlash = true;

        $this->dispatch('flash-message', [
            'message' => $message,
            'type' => $type
        ]);
    }

    public function render()
    {
        return view('livewire.settings.tickets.status');
    }
}
