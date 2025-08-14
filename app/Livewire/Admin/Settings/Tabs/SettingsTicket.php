<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Contracts\SettingsRepositoryInterface;
use App\Services\TicketColorService;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Models\TicketStatus as TicketStatusModel;
use App\Models\DepartmentGroup;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SettingsTicket extends Component
{
    // Ticket Workflow Settings
    public string $defaultReplyStatus = 'in_progress';
    public int $reopenWindowDays = 3;
    public bool $requireEscalationConfirmation = true;
    public string $messageOrder = 'newest_first';
    public int $attachmentMaxSizeMb = 10;
    public int $attachmentMaxCount = 5;

    // Color Management
    public array $statusColors = [];
    public array $priorityColors = [];
    public bool $showColorModal = false;
    public string $editingColorType = ''; // 'status' or 'priority'
    public string $editingColorKey = '';
    public string $editingColorValue = '#3b82f6';

    // Ticket Status Management
    public bool $showStatusModal = false;
    public bool $statusEditMode = false;
    public ?int $selectedStatusId = null;
    public array $statusForm = [
        'name' => '',
        'key' => '',
        'description' => '',
        'color' => '#3b82f6',
        'sort_order' => 0,
        'is_active' => true,
    ];
    public array $statusDepartmentGroups = [];
    public ?int $confirmingStatusDelete = null;

    public bool $hasUnsavedChanges = false;

    protected $listeners = ['tabChanged' => 'refreshData'];

    public function mount()
    {
        $this->checkPermission('settings.read');
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $repository = app(SettingsRepositoryInterface::class);
            
            // Load ticket workflow settings
            $this->defaultReplyStatus = $repository->get('tickets.default_reply_status', 'in_progress');
            $this->reopenWindowDays = (int) $repository->get('tickets.reopen_window_days', 3);
            $this->requireEscalationConfirmation = (bool) $repository->get('tickets.require_escalation_confirmation', true);
            $this->messageOrder = $repository->get('tickets.message_order', 'newest_first');
            $this->attachmentMaxSizeMb = (int) $repository->get('tickets.attachment_max_size_mb', 10);
            $this->attachmentMaxCount = (int) $repository->get('tickets.attachment_max_count', 5);

            // Load color settings
            $colorService = app(TicketColorService::class);
            $this->statusColors = $colorService->getStatusColors();
            $this->priorityColors = $colorService->getPriorityColors();

            // Load department group assignments for all statuses
            foreach ($this->ticketStatuses as $status) {
                $this->statusDepartmentGroups[$status->id] = $status->departmentGroups->pluck('id')->toArray();
            }
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to load ticket settings: ' . $e->getMessage());
        }
    }

    public function refreshData()
    {
        $this->loadData();
    }

    #[Computed]
    public function ticketStatuses()
    {
        return TicketStatusModel::with('departmentGroups')->ordered()->get();
    }

    #[Computed]
    public function departmentGroups()
    {
        return DepartmentGroup::active()->ordered()->get();
    }

    public function updatedDefaultReplyStatus()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedReopenWindowDays()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedRequireEscalationConfirmation()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedMessageOrder()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedAttachmentMaxSizeMb()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedAttachmentMaxCount()
    {
        $this->hasUnsavedChanges = true;
    }

    public function saveSettings()
    {
        $this->checkPermission('settings.update');

        $this->validate([
            'defaultReplyStatus' => 'required|in:open,in_progress,solution_provided',
            'reopenWindowDays' => 'required|integer|min:1|max:365',
            'requireEscalationConfirmation' => 'boolean',
            'messageOrder' => 'required|in:newest_first,oldest_first',
            'attachmentMaxSizeMb' => 'required|integer|min:1|max:100',
            'attachmentMaxCount' => 'required|integer|min:1|max:20',
        ]);

        try {
            $repository = app(SettingsRepositoryInterface::class);
            
            $repository->set('tickets.default_reply_status', $this->defaultReplyStatus, 'string');
            $repository->set('tickets.reopen_window_days', $this->reopenWindowDays, 'integer');
            $repository->set('tickets.require_escalation_confirmation', $this->requireEscalationConfirmation, 'boolean');
            $repository->set('tickets.message_order', $this->messageOrder, 'string');
            $repository->set('tickets.attachment_max_size_mb', $this->attachmentMaxSizeMb, 'integer');
            $repository->set('tickets.attachment_max_count', $this->attachmentMaxCount, 'integer');

            $this->hasUnsavedChanges = false;
            $this->dispatch('saved', 'Ticket settings saved successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save ticket settings: ' . $e->getMessage());
        }
    }

    public function editColor($type, $key, $currentColor)
    {
        $this->checkPermission('settings.update');
        $this->editingColorType = $type;
        $this->editingColorKey = $key;
        $this->editingColorValue = $currentColor;
        $this->showColorModal = true;
    }

    public function saveColor()
    {
        $this->checkPermission('settings.update');

        $this->validate([
            'editingColorValue' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
        ]);

        try {
            $colorService = app(TicketColorService::class);
            
            if ($this->editingColorType === 'status') {
                $this->statusColors[$this->editingColorKey] = $this->editingColorValue;
                $colorService->updateStatusColors($this->statusColors);
            } else {
                $this->priorityColors[$this->editingColorKey] = $this->editingColorValue;
                $colorService->updatePriorityColors($this->priorityColors);
            }

            $this->showColorModal = false;
            $this->dispatch('saved', 'Color updated successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save color: ' . $e->getMessage());
        }
    }

    public function closeColorModal()
    {
        $this->showColorModal = false;
        $this->editingColorType = '';
        $this->editingColorKey = '';
        $this->editingColorValue = '#3b82f6';
    }

    public function resetColors()
    {
        $this->checkPermission('settings.update');
        
        try {
            $colorService = app(TicketColorService::class);
            $colorService->resetStatusColorsToDefaults();
            $colorService->resetPriorityColorsToDefaults();
            
            $this->loadData();
            $this->dispatch('reset', 'Colors reset to defaults successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to reset colors: ' . $e->getMessage());
        }
    }

    public function resetToDefaults()
    {
        $this->checkPermission('settings.update');
        
        try {
            $repository = app(SettingsRepositoryInterface::class);
            
            // Reset all ticket settings
            $repository->reset('tickets.default_reply_status');
            $repository->reset('tickets.reopen_window_days');
            $repository->reset('tickets.require_escalation_confirmation');
            $repository->reset('tickets.message_order');
            $repository->reset('tickets.attachment_max_size_mb');
            $repository->reset('tickets.attachment_max_count');

            // Reset colors
            $colorService = app(TicketColorService::class);
            $colorService->resetStatusColorsToDefaults();
            $colorService->resetPriorityColorsToDefaults();
            
            // Reload data
            $this->loadData();
            $this->hasUnsavedChanges = false;
            
            $this->dispatch('reset', 'All ticket settings reset to defaults successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }

    public function getStatusOptionsProperty()
    {
        return [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'solution_provided' => 'Solution Provided',
        ];
    }

    public function getMessageOrderOptionsProperty()
    {
        return [
            'newest_first' => 'Newest First (Recommended)',
            'oldest_first' => 'Oldest First',
        ];
    }

    // Status Management Methods
    public function createStatus()
    {
        $this->checkPermission('settings.update');
        $this->resetStatusForm();
        $this->statusEditMode = false;
        $this->showStatusModal = true;
    }

    public function editStatus($id)
    {
        $this->checkPermission('settings.update');
        $status = TicketStatusModel::findOrFail($id);
        
        if ($status->is_protected) {
            $this->dispatch('error', 'Cannot edit protected ticket status.');
            return;
        }

        $this->selectedStatusId = $id;
        $this->statusForm = [
            'name' => $status->name,
            'key' => $status->key,
            'description' => $status->description,
            'color' => $status->color,
            'sort_order' => $status->sort_order,
            'is_active' => $status->is_active,
        ];
        $this->statusEditMode = true;
        $this->showStatusModal = true;
    }

    public function saveStatus()
    {
        $this->checkPermission('settings.update');

        $this->validate([
            'statusForm.name' => 'required|string|max:255',
            'statusForm.key' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z_]+$/',
                $this->statusEditMode 
                    ? 'unique:ticket_statuses,key,' . $this->selectedStatusId
                    : 'unique:ticket_statuses,key',
            ],
            'statusForm.description' => 'nullable|string',
            'statusForm.color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'statusForm.sort_order' => 'integer|min:0',
            'statusForm.is_active' => 'boolean',
        ]);

        try {
            if ($this->statusEditMode) {
                $status = TicketStatusModel::findOrFail($this->selectedStatusId);
                $status->update($this->statusForm);
                $message = 'Ticket status updated successfully.';
            } else {
                $status = TicketStatusModel::create($this->statusForm);
                $message = 'Ticket status created successfully.';
            }

            // Update the color service with the new color
            $colorService = app(TicketColorService::class);
            $colorService->setStatusColor($this->statusForm['key'], $this->statusForm['color']);

            $this->closeStatusModal();
            $this->dispatch('saved', $message);
            $this->refreshData(); // Reload data to show changes
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save ticket status: ' . $e->getMessage());
        }
    }

    public function closeStatusModal()
    {
        $this->showStatusModal = false;
        $this->resetStatusForm();
    }

    public function updateDepartmentGroupAssignment($statusId, $departmentGroupId, $assigned)
    {
        $this->checkPermission('settings.update');
        
        try {
            $status = TicketStatusModel::findOrFail($statusId);
            
            if ($assigned) {
                $status->departmentGroups()->syncWithoutDetaching([$departmentGroupId]);
            } else {
                $status->departmentGroups()->detach($departmentGroupId);
            }
            
            // Update local state
            if ($assigned) {
                $this->statusDepartmentGroups[$statusId][] = $departmentGroupId;
            } else {
                $this->statusDepartmentGroups[$statusId] = array_diff(
                    $this->statusDepartmentGroups[$statusId] ?? [], 
                    [$departmentGroupId]
                );
            }
            
            $this->dispatch('saved', 'Department group assignment updated.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to update assignment: ' . $e->getMessage());
        }
    }

    public function confirmDeleteStatus($id)
    {
        $this->checkPermission('settings.update');
        
        $status = TicketStatusModel::findOrFail($id);
        if ($status->is_protected) {
            $this->dispatch('error', 'Cannot delete protected ticket status.');
            return;
        }
        
        // Check if status is in use
        if ($status->tickets()->count() > 0) {
            $this->dispatch('error', 'Cannot delete ticket status that is in use by tickets.');
            return;
        }
        
        $this->confirmingStatusDelete = $id;
    }

    public function deleteStatus()
    {
        $this->checkPermission('settings.update');
        
        try {
            TicketStatusModel::findOrFail($this->confirmingStatusDelete)->delete();
            $this->confirmingStatusDelete = null;
            $this->dispatch('saved', 'Ticket status deleted successfully.');
            $this->refreshData();
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete ticket status: ' . $e->getMessage());
        }
    }

    public function cancelStatusDelete()
    {
        $this->confirmingStatusDelete = null;
    }

    public function toggleStatusActive($id)
    {
        $this->checkPermission('settings.update');
        
        try {
            $status = TicketStatusModel::findOrFail($id);
            $status->update(['is_active' => !$status->is_active]);
            
            $statusText = $status->is_active ? 'enabled' : 'disabled';
            $this->dispatch('saved', "Ticket status {$statusText} successfully.");
            $this->refreshData();
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to update ticket status: ' . $e->getMessage());
        }
    }

    private function resetStatusForm()
    {
        $this->statusForm = [
            'name' => '',
            'key' => '',
            'description' => '',
            'color' => '#3b82f6',
            'sort_order' => $this->ticketStatuses->count() + 1,
            'is_active' => true,
        ];
        $this->selectedStatusId = null;
        $this->resetErrorBag('statusForm');
    }

    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to {$permission}.");
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.tabs.ticket');
    }
}