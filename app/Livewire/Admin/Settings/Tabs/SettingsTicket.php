<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Contracts\SettingsRepositoryInterface;
use App\Services\TicketColorService;
use App\Enums\TicketPriority;
use App\Models\TicketStatus as TicketStatusModel;
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

    // Priority Color Management (inline editing)
    public array $priorityColors = [];
    public string $editingPriorityKey = '';
    public array $editPriorityForm = [
        'color' => '#3b82f6',
    ];

    // Status Management (inline editing)
    public array $ticketStatusesArray = [];
    public bool $showAddStatusForm = false;
    public string $editingStatusKey = '';
    public array $newStatusForm = [
        'name' => '',
        'key' => '',
        'description' => '',
        'color' => '#3b82f6',
        'department_groups' => [],
    ];
    public array $editStatusForm = [
        'name' => '',
        'key' => '',
        'description' => '',
        'color' => '#3b82f6',
        'department_groups' => [],
    ];
    
    // Department Group Access Management
    public array $departmentGroups = [];
    public bool $showDepartmentGroupAccess = false;
    public string $selectedStatusKey = '';
    public array $statusDepartmentGroups = [];

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
            $this->priorityColors = $colorService->getPriorityColors();
            
            // Load ticket statuses as array for easier management
            $this->ticketStatusesArray = $this->ticketStatuses->keyBy('key')->map(function($status) {
                return [
                    'id' => $status->id,
                    'name' => $status->name,
                    'key' => $status->key,
                    'description' => $status->description,
                    'color' => $status->color,
                    'sort_order' => $status->sort_order,
                    'is_active' => $status->is_active,
                    'is_protected' => $status->is_protected,
                    'department_groups' => $status->departmentGroups->pluck('id')->toArray(),
                ];
            })->toArray();
            
            // Load department groups for access management
            $this->departmentGroups = \App\Models\DepartmentGroup::active()->ordered()->get()->map(function($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'color' => $group->color,
                ];
            })->toArray();

            
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
        return \App\Models\DepartmentGroup::orderBy('name')->get();
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

    // Priority Color Management
    public function editPriorityColor($key)
    {
        $this->checkPermission('settings.update');
        $this->editingPriorityKey = $key;
        $this->editPriorityForm = [
            'color' => $this->priorityColors[$key] ?? '#3b82f6',
        ];
    }

    public function savePriorityColor()
    {
        $this->checkPermission('settings.update');

        // Validate color format manually to avoid regex issues
        if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $this->editPriorityForm['color'])) {
            $this->addError('editPriorityForm.color', 'The color must be a valid hex color code (e.g., #3b82f6).');
            return;
        }

        $this->validate([
            'editPriorityForm.color' => 'required|string',
        ]);

        try {
            $colorService = app(TicketColorService::class);
            $this->priorityColors[$this->editingPriorityKey] = $this->editPriorityForm['color'];
            $colorService->updatePriorityColors($this->priorityColors);

            $this->editingPriorityKey = '';
            $this->editPriorityForm = ['color' => '#3b82f6'];
            $this->dispatch('saved', 'Priority color updated successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save priority color: ' . $e->getMessage());
        }
    }

    public function cancelEditPriorityColor()
    {
        $this->editingPriorityKey = '';
        $this->editPriorityForm = ['color' => '#3b82f6'];
    }

    public function resetPriorityColors()
    {
        $this->checkPermission('settings.update');
        
        try {
            $colorService = app(TicketColorService::class);
            $colorService->resetPriorityColorsToDefaults();
            
            $this->loadData();
            $this->dispatch('reset', 'Priority colors reset to defaults successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to reset priority colors: ' . $e->getMessage());
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
    public function addNewStatus()
    {
        $this->checkPermission('settings.update');
        $this->showAddStatusForm = true;
        $this->newStatusForm = [
            'name' => '',
            'key' => '',
            'description' => '',
            'color' => '#3b82f6',
        ];
    }

    public function cancelAddStatus()
    {
        $this->showAddStatusForm = false;
        $this->newStatusForm = [
            'name' => '',
            'key' => '',
            'description' => '',
            'color' => '#3b82f6',
        ];
    }

    public function saveNewStatus()
    {
        $this->checkPermission('settings.update');

        // Ensure key is generated if empty
        if (empty($this->newStatusForm['key'])) {
            $this->newStatusForm['key'] = $this->generateKeyFromName($this->newStatusForm['name']);
        }

        // Clean the key to ensure it only contains valid characters
        $this->newStatusForm['key'] = $this->generateKeyFromName($this->newStatusForm['key']);

        // Validate key format manually to avoid regex issues
        if (!preg_match('/^[a-z0-9_]+$/', $this->newStatusForm['key'])) {
            $this->addError('newStatusForm.key', 'The key may only contain lowercase letters, numbers, and underscores.');
            return;
        }

        // Validate color format manually to avoid regex issues
        if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $this->newStatusForm['color'])) {
            $this->addError('newStatusForm.color', 'The color must be a valid hex color code (e.g., #3b82f6).');
            return;
        }

        $this->validate([
            'newStatusForm.name' => 'required|string|max:255',
            'newStatusForm.key' => [
                'required',
                'string',
                'max:50',
                'unique:ticket_statuses,key'
            ],
            'newStatusForm.description' => 'nullable|string',
            'newStatusForm.color' => 'required|string',
        ]);

        try {
            $statusData = array_merge($this->newStatusForm, [
                'sort_order' => count($this->ticketStatusesArray) + 1,
                'is_active' => true,
                'is_protected' => false,
            ]);
            
            $status = TicketStatusModel::create($statusData);

            // Sync department groups if any are selected
            if (!empty($this->newStatusForm['department_groups'])) {
                $status->departmentGroups()->sync($this->newStatusForm['department_groups']);
            }

            // Update the color service with the new color
            $colorService = app(TicketColorService::class);
            $colorService->setStatusColor($this->newStatusForm['key'], $this->newStatusForm['color']);

            $this->showAddStatusForm = false;
            $this->newStatusForm = ['name' => '', 'key' => '', 'description' => '', 'color' => '#3b82f6'];
            $this->dispatch('saved', 'Ticket status created successfully.');
            $this->refreshData();
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to create ticket status: ' . $e->getMessage());
        }
    }

    public function startEditStatus($key)
    {
        $this->checkPermission('settings.update');
        $status = $this->ticketStatusesArray[$key] ?? null;
        
        if (!$status) {
            $this->dispatch('error', 'Status not found.');
            return;
        }

        if ($status['is_protected']) {
            $this->dispatch('error', 'Cannot edit protected ticket status.');
            return;
        }

        $this->editingStatusKey = $key;
        $this->editStatusForm = [
            'name' => $status['name'],
            'key' => $status['key'],
            'description' => $status['description'],
            'color' => $status['color'],
            'department_groups' => $status['department_groups'],
        ];
    }

    public function cancelEditStatus()
    {
        $this->editingStatusKey = '';
        $this->editStatusForm = [
            'name' => '',
            'key' => '',
            'description' => '',
            'color' => '#3b82f6',
        ];
    }

    public function saveEditStatus()
    {
        $this->checkPermission('settings.update');

        // Clean the key to ensure it only contains valid characters
        $this->editStatusForm['key'] = $this->generateKeyFromName($this->editStatusForm['key']);

        // Validate key format manually to avoid regex issues
        if (!preg_match('/^[a-z0-9_]+$/', $this->editStatusForm['key'])) {
            $this->addError('editStatusForm.key', 'The key may only contain lowercase letters, numbers, and underscores.');
            return;
        }

        // Validate color format manually to avoid regex issues
        if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $this->editStatusForm['color'])) {
            $this->addError('editStatusForm.color', 'The color must be a valid hex color code (e.g., #3b82f6).');
            return;
        }

        $this->validate([
            'editStatusForm.name' => 'required|string|max:255',
            'editStatusForm.key' => [
                'required',
                'string',
                'max:50',
                'unique:ticket_statuses,key,' . $this->ticketStatusesArray[$this->editingStatusKey]['id']
            ],
            'editStatusForm.description' => 'nullable|string',
            'editStatusForm.color' => 'required|string',
        ]);

        try {
            $status = TicketStatusModel::findOrFail($this->ticketStatusesArray[$this->editingStatusKey]['id']);
            $status->update($this->editStatusForm);

            // Sync department groups if any are selected
            if (!empty($this->editStatusForm['department_groups'])) {
                $status->departmentGroups()->sync($this->editStatusForm['department_groups']);
            }

            // Update the color service with the new color
            $colorService = app(TicketColorService::class);
            $colorService->setStatusColor($this->editStatusForm['key'], $this->editStatusForm['color']);

            $this->editingStatusKey = '';
            $this->editStatusForm = ['name' => '', 'key' => '', 'description' => '', 'color' => '#3b82f6'];
            $this->dispatch('saved', 'Ticket status updated successfully.');
            $this->refreshData();
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to update ticket status: ' . $e->getMessage());
        }
    }


    public function deleteStatus($key)
    {
        $this->checkPermission('settings.update');
        
        $status = $this->ticketStatusesArray[$key] ?? null;
        if (!$status) {
            $this->dispatch('error', 'Status not found.');
            return;
        }
        
        if ($status['is_protected']) {
            $this->dispatch('error', 'Cannot delete protected ticket status.');
            return;
        }
        
        try {
            $statusModel = TicketStatusModel::findOrFail($status['id']);
            
            // Check if status is in use
            if ($statusModel->tickets()->count() > 0) {
                $this->dispatch('error', 'Cannot delete ticket status that is in use by tickets.');
                return;
            }
            
            $statusModel->delete();
            $this->dispatch('saved', 'Ticket status deleted successfully.');
            $this->refreshData();
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete ticket status: ' . $e->getMessage());
        }
    }

    public function toggleStatusActive($key)
    {
        $this->checkPermission('settings.update');
        
        $status = $this->ticketStatusesArray[$key] ?? null;
        if (!$status) {
            $this->dispatch('error', 'Status not found.');
            return;
        }
        
        try {
            $statusModel = TicketStatusModel::findOrFail($status['id']);
            $statusModel->update(['is_active' => !$statusModel->is_active]);
            
            $statusText = $statusModel->is_active ? 'enabled' : 'disabled';
            $this->dispatch('saved', "Ticket status {$statusText} successfully.");
            $this->refreshData();
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to update ticket status: ' . $e->getMessage());
        }
    }

    // Department Group Access Management Methods
    public function showDepartmentGroupAccess($statusKey)
    {
        $this->checkPermission('settings.update');
        
        $status = $this->ticketStatusesArray[$statusKey] ?? null;
        if (!$status) {
            $this->dispatch('error', 'Status not found.');
            return;
        }

        $this->selectedStatusKey = $statusKey;
        $this->statusDepartmentGroups = $status['department_groups'];
        $this->showDepartmentGroupAccess = true;
    }

    public function closeDepartmentGroupAccess()
    {
        $this->showDepartmentGroupAccess = false;
        $this->selectedStatusKey = '';
        $this->statusDepartmentGroups = [];
    }

    public function toggleDepartmentGroupAccess($groupId)
    {
        if (in_array($groupId, $this->statusDepartmentGroups)) {
            $this->statusDepartmentGroups = array_diff($this->statusDepartmentGroups, [$groupId]);
        } else {
            $this->statusDepartmentGroups[] = $groupId;
        }
    }

    public function saveDepartmentGroupAccess()
    {
        $this->checkPermission('settings.update');
        
        try {
            $status = $this->ticketStatusesArray[$this->selectedStatusKey] ?? null;
            if (!$status) {
                $this->dispatch('error', 'Status not found.');
                return;
            }

            $statusModel = TicketStatusModel::findOrFail($status['id']);
            $statusModel->departmentGroups()->sync($this->statusDepartmentGroups);
            
            $this->dispatch('saved', 'Department group access updated successfully.');
            $this->closeDepartmentGroupAccess();
            $this->refreshData();
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to update department group access: ' . $e->getMessage());
        }
    }

    public function updatedNewStatusFormName()
    {
        // Only auto-generate key if user hasn't manually modified it
        if (empty($this->newStatusForm['key']) || $this->shouldAutoGenerateKey()) {
            $this->newStatusForm['key'] = $this->generateKeyFromName($this->newStatusForm['name']);
        }
    }
    
    private function shouldAutoGenerateKey(): bool
    {
        // Check if the current key looks like it was auto-generated from the name
        $expectedKey = $this->generateKeyFromName($this->newStatusForm['name']);
        return $this->newStatusForm['key'] === $expectedKey || 
               $this->newStatusForm['key'] === $this->generateKeyFromName(substr($this->newStatusForm['name'], 0, -1));
    }
    
    private function generateKeyFromName(string $name): string
    {
        $key = strtolower(str_replace([' ', '-'], '_', $name));
        return preg_replace('/[^a-z0-9_]/', '', $key);
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