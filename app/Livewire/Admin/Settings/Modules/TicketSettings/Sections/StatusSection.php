<?php

namespace App\Livewire\Admin\Settings\Modules\TicketSettings\Sections;

use App\Livewire\Admin\Settings\BaseSettingsComponent;
use App\Models\TicketStatus;
use App\Models\DepartmentGroup;

class StatusSection extends BaseSettingsComponent
{
    // Status Management
    public array $ticketStatuses = [];
    public array $departmentGroups = [];
    
    // Status CRUD
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

    // Department Group Access
    public bool $showDepartmentGroupAccess = false;
    public string $selectedStatusKey = '';
    public array $statusDepartmentGroups = [];

    protected function getSettingsGroup(): string
    {
        return 'tickets.status';
    }

    protected function getTitle(): string
    {
        return 'Status Management';
    }

    protected function getDescription(): string
    {
        return 'Manage ticket statuses and access';
    }

    protected function getIcon(): string
    {
        return 'heroicon-o-list-bullet';
    }

    protected function loadData(): void
    {
        // Load ticket statuses
        $this->ticketStatuses = TicketStatus::with('departmentGroups')
            ->ordered()
            ->get()
            ->keyBy('key')
            ->map(function($status) {
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
            })
            ->toArray();

        // Load department groups
        $this->departmentGroups = DepartmentGroup::active()
            ->ordered()
            ->get()
            ->map(function($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'color' => $group->color,
                ];
            })
            ->toArray();
    }

    protected function saveData(): void
    {
        // Status data is saved through individual CRUD operations
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
            'department_groups' => [],
        ];
    }

    public function saveNewStatus()
    {
        $this->checkPermission('settings.update');

        if (empty($this->newStatusForm['key'])) {
            $this->newStatusForm['key'] = $this->generateKeyFromName($this->newStatusForm['name']);
        }

        $this->validate([
            'newStatusForm.name' => 'required|string|max:255',
            'newStatusForm.key' => 'required|string|max:50|unique:ticket_statuses,key',
            'newStatusForm.description' => 'nullable|string',
            'newStatusForm.color' => 'required|string',
        ]);

        try {
            $status = TicketStatus::create([
                'name' => $this->newStatusForm['name'],
                'key' => $this->newStatusForm['key'],
                'description' => $this->newStatusForm['description'],
                'color' => $this->newStatusForm['color'],
                'sort_order' => count($this->ticketStatuses) + 1,
                'is_active' => true,
                'is_protected' => false,
            ]);

            if (!empty($this->newStatusForm['department_groups'])) {
                $status->departmentGroups()->sync($this->newStatusForm['department_groups']);
            }

            $this->showAddStatusForm = false;
            $this->newStatusForm = ['name' => '', 'key' => '', 'description' => '', 'color' => '#3b82f6', 'department_groups' => []];
            $this->showSuccess('Ticket status created successfully.');
            $this->loadData();
        } catch (\Exception $e) {
            $this->showError('Failed to create ticket status: ' . $e->getMessage());
        }
    }

    public function showDepartmentGroupAccess($statusKey)
    {
        $this->checkPermission('settings.update');
        
        $status = $this->ticketStatuses[$statusKey] ?? null;
        if (!$status) {
            $this->showError('Status not found.');
            return;
        }

        $this->selectedStatusKey = $statusKey;
        $this->statusDepartmentGroups = $status['department_groups'];
        $this->showDepartmentGroupAccess = true;
    }

    public function saveDepartmentGroupAccess()
    {
        $this->checkPermission('settings.update');
        
        try {
            $status = $this->ticketStatuses[$this->selectedStatusKey] ?? null;
            if (!$status) {
                $this->showError('Status not found.');
                return;
            }

            $statusModel = TicketStatus::findOrFail($status['id']);
            $statusModel->departmentGroups()->sync($this->statusDepartmentGroups);
            
            $this->showSuccess('Department group access updated successfully.');
            $this->closeDepartmentGroupAccess();
            $this->loadData();
        } catch (\Exception $e) {
            $this->showError('Failed to update department group access: ' . $e->getMessage());
        }
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

    public function deleteStatus($statusKey)
    {
        $this->checkPermission('settings.update');
        
        $status = $this->ticketStatuses[$statusKey] ?? null;
        if (!$status) {
            $this->showError('Status not found.');
            return;
        }

        if ($status['is_protected']) {
            $this->showError('Cannot delete protected status.');
            return;
        }

        try {
            $statusModel = TicketStatus::findOrFail($status['id']);
            $statusModel->delete();
            
            $this->showSuccess('Ticket status deleted successfully.');
            $this->loadData();
        } catch (\Exception $e) {
            $this->showError('Failed to delete ticket status: ' . $e->getMessage());
        }
    }

    private function generateKeyFromName(string $name): string
    {
        $key = strtolower(str_replace([' ', '-'], '_', $name));
        return preg_replace('/[^a-z0-9_]/', '', $key);
    }

    public function render()
    {
        return view('livewire.admin.settings.modules.ticket-settings.sections.status');
    }
}
