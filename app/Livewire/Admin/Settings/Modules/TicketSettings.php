<?php

namespace App\Livewire\Admin\Settings\Modules;

use App\Livewire\Admin\Settings\BaseSettingsComponent;
use App\Services\SettingsProgressTracker;
use App\Models\TicketStatus;
use App\Models\DepartmentGroup;

class TicketSettings extends BaseSettingsComponent
{
    public string $activeSection = 'workflow';

    // Status Management Properties
    public array $ticketStatuses = [];
    public array $departmentGroups = [];
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
    public bool $showDepartmentGroupAccess = false;
    public string $selectedStatusKey = '';
    public array $statusDepartmentGroups = [];

    // Workflow Properties
    public string $defaultReplyStatus = 'in_progress';
    public int $reopenWindowDays = 3;
    public bool $requireEscalationConfirmation = true;
    public string $messageOrder = 'newest_first';

    // Attachment Properties
    public int $attachmentMaxSizeMb = 10;
    public int $attachmentMaxCount = 5;
    public array $allowedFileTypes = [];
    public bool $enableImageCompression = true;
    public int $imageCompressionQuality = 80;
    public bool $scanForViruses = false;

    // Priority Properties
    public array $priorityColors = [];
    public bool $enableCustomColors = true;
    public bool $showPriorityIcons = true;
    public bool $enablePriorityEscalation = true;
    public int $escalationDelayHours = 24;

    protected function getSettingsGroup(): string
    {
        return 'tickets';
    }

    protected function getTitle(): string
    {
        return 'Ticket Settings';
    }

    protected function getDescription(): string
    {
        return 'Configure ticket workflow, statuses, colors, and limits';
    }

    protected function getIcon(): string
    {
        return 'heroicon-o-ticket';
    }

    protected function loadData(): void
    {
        \Log::info('TicketSettings: loadData called');
        
        // Load workflow settings
        $this->defaultReplyStatus = $this->getSetting('tickets.workflow.default_reply_status', 'in_progress');
        $this->reopenWindowDays = (int) $this->getSetting('tickets.workflow.reopen_window_days', 3);
        $this->requireEscalationConfirmation = (bool) $this->getSetting('tickets.workflow.require_escalation_confirmation', true);
        $this->messageOrder = $this->getSetting('tickets.workflow.message_order', 'newest_first');

        // Load attachment settings
        $this->attachmentMaxSizeMb = (int) $this->getSetting('tickets.attachment.max_size_mb', 10);
        $this->attachmentMaxCount = (int) $this->getSetting('tickets.attachment.max_count', 5);
        $this->allowedFileTypes = $this->getSetting('tickets.attachment.allowed_types', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt']);
        $this->enableImageCompression = (bool) $this->getSetting('tickets.attachment.enable_image_compression', true);
        $this->imageCompressionQuality = (int) $this->getSetting('tickets.attachment.image_compression_quality', 80);
        $this->scanForViruses = (bool) $this->getSetting('tickets.attachment.scan_for_viruses', false);

        // Load priority settings
        $this->priorityColors = $this->getSetting('tickets.priority.colors', [
            'low' => '#10b981',
            'medium' => '#f59e0b',
            'high' => '#ef4444',
            'urgent' => '#dc2626',
            'critical' => '#7c2d12',
        ]);
        $this->enableCustomColors = (bool) $this->getSetting('tickets.priority.enable_custom_colors', true);
        $this->showPriorityIcons = (bool) $this->getSetting('tickets.priority.show_icons', true);
        $this->enablePriorityEscalation = (bool) $this->getSetting('tickets.priority.enable_escalation', true);
        $this->escalationDelayHours = (int) $this->getSetting('tickets.priority.escalation_delay_hours', 24);

        // Load status data
        $this->loadStatusData();

        // Update progress tracker
        $this->updateProgress();
        
        \Log::info('TicketSettings: loadData completed');
    }

    protected function saveData(): void
    {
        // Save workflow settings
        $this->setSetting('tickets.workflow.default_reply_status', $this->defaultReplyStatus);
        $this->setSetting('tickets.workflow.reopen_window_days', $this->reopenWindowDays, 'integer');
        $this->setSetting('tickets.workflow.require_escalation_confirmation', $this->requireEscalationConfirmation, 'boolean');
        $this->setSetting('tickets.workflow.message_order', $this->messageOrder);

        // Save attachment settings
        $this->setSetting('tickets.attachment.max_size_mb', $this->attachmentMaxSizeMb, 'integer');
        $this->setSetting('tickets.attachment.max_count', $this->attachmentMaxCount, 'integer');
        $this->setSetting('tickets.attachment.allowed_types', $this->allowedFileTypes, 'json');
        $this->setSetting('tickets.attachment.enable_image_compression', $this->enableImageCompression, 'boolean');
        $this->setSetting('tickets.attachment.image_compression_quality', $this->imageCompressionQuality, 'integer');
        $this->setSetting('tickets.attachment.scan_for_viruses', $this->scanForViruses, 'boolean');

        // Save priority settings
        $this->setSetting('tickets.priority.colors', $this->priorityColors, 'json');
        $this->setSetting('tickets.priority.enable_custom_colors', $this->enableCustomColors, 'boolean');
        $this->setSetting('tickets.priority.show_icons', $this->showPriorityIcons, 'boolean');
        $this->setSetting('tickets.priority.enable_escalation', $this->enablePriorityEscalation, 'boolean');
        $this->setSetting('tickets.priority.escalation_delay_hours', $this->escalationDelayHours, 'integer');
    }

    private function loadStatusData(): void
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

    public function setActiveSection(string $section): void
    {
        $this->activeSection = $section;
    }

    public function getSections(): array
    {
        return [
            'workflow' => [
                'title' => 'Workflow',
                'description' => 'Ticket workflow and behavior settings',
                'icon' => 'heroicon-o-cog-6-tooth',
            ],
            'attachment' => [
                'title' => 'Attachments',
                'description' => 'File upload limits and settings',
                'icon' => 'heroicon-o-paper-clip',
            ],
            'priority' => [
                'title' => 'Priority Colors',
                'description' => 'Customize priority color schemes',
                'icon' => 'heroicon-o-swatch',
            ],
            'status' => [
                'title' => 'Status Management',
                'description' => 'Manage ticket statuses and access',
                'icon' => 'heroicon-o-list-bullet',
            ],
        ];
    }

    // Status Management Methods
    public function addNewStatus()
    {
        \Log::info('TicketSettings: addNewStatus called');
        $this->checkPermission('settings.update');
        $this->showAddStatusForm = true;
        $this->newStatusForm = [
            'name' => '',
            'key' => '',
            'description' => '',
            'color' => '#3b82f6',
            'department_groups' => [],
        ];
        \Log::info('TicketSettings: addNewStatus completed, showAddStatusForm = ' . ($this->showAddStatusForm ? 'true' : 'false'));
    }

    public function saveNewStatus()
    {
        \Log::info('TicketSettings: saveNewStatus called', ['form' => $this->newStatusForm]);
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
            $this->loadStatusData();
            \Log::info('TicketSettings: saveNewStatus completed successfully', ['status_id' => $status->id]);
        } catch (\Exception $e) {
            \Log::error('TicketSettings: saveNewStatus failed', ['error' => $e->getMessage()]);
            $this->showError('Failed to create ticket status: ' . $e->getMessage());
        }
    }

    public function showDepartmentGroupAccess($statusKey)
    {
        \Log::info('TicketSettings: showDepartmentGroupAccess called', ['statusKey' => $statusKey]);
        $this->checkPermission('settings.update');
        
        $status = $this->ticketStatuses[$statusKey] ?? null;
        if (!$status) {
            \Log::warning('TicketSettings: Status not found', ['statusKey' => $statusKey]);
            $this->showError('Status not found.');
            return;
        }

        $this->selectedStatusKey = $statusKey;
        $this->statusDepartmentGroups = $status['department_groups'];
        $this->showDepartmentGroupAccess = true;
        \Log::info('TicketSettings: showDepartmentGroupAccess completed', [
            'selectedStatusKey' => $this->selectedStatusKey,
            'statusDepartmentGroups' => $this->statusDepartmentGroups,
            'showDepartmentGroupAccess' => $this->showDepartmentGroupAccess
        ]);
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
            $this->loadStatusData();
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
            $this->loadStatusData();
        } catch (\Exception $e) {
            $this->showError('Failed to delete ticket status: ' . $e->getMessage());
        }
    }

    private function generateKeyFromName(string $name): string
    {
        $key = strtolower(str_replace([' ', '-'], '_', $name));
        return preg_replace('/[^a-z0-9_]/', '', $key);
    }

    // Priority Methods
    public function updatePriorityColor(string $priority, string $color): void
    {
        $this->priorityColors[$priority] = $color;
        $this->markAsChanged();
    }

    public function resetPriorityToDefaults(): void
    {
        $this->priorityColors = [
            'low' => '#10b981',
            'medium' => '#f59e0b',
            'high' => '#ef4444',
            'urgent' => '#dc2626',
            'critical' => '#7c2d12',
        ];
        $this->markAsChanged();
    }

    public function getPriorities(): array
    {
        return [
            'low' => [
                'name' => 'Low',
                'description' => 'Low priority tickets',
                'icon' => 'heroicon-o-arrow-down',
            ],
            'medium' => [
                'name' => 'Medium',
                'description' => 'Medium priority tickets',
                'icon' => 'heroicon-o-minus',
            ],
            'high' => [
                'name' => 'High',
                'description' => 'High priority tickets',
                'icon' => 'heroicon-o-arrow-up',
            ],
            'urgent' => [
                'name' => 'Urgent',
                'description' => 'Urgent priority tickets',
                'icon' => 'heroicon-o-exclamation-triangle',
            ],
            'critical' => [
                'name' => 'Critical',
                'description' => 'Critical priority tickets',
                'icon' => 'heroicon-o-exclamation-circle',
            ],
        ];
    }

    // Attachment Methods
    public function addFileType(string $fileType): void
    {
        $fileType = strtolower(trim($fileType));
        if (!empty($fileType) && !in_array($fileType, $this->allowedFileTypes)) {
            $this->allowedFileTypes[] = $fileType;
            $this->markAsChanged();
        }
    }

    public function removeFileType(int $index): void
    {
        if (isset($this->allowedFileTypes[$index])) {
            unset($this->allowedFileTypes[$index]);
            $this->allowedFileTypes = array_values($this->allowedFileTypes);
            $this->markAsChanged();
        }
    }

    public function testMethod()
    {
        \Log::info('TicketSettings: testMethod called successfully');
        $this->showSuccess('Test method called successfully!');
    }

    public function debugComponent()
    {
        \Log::info('TicketSettings: debugComponent called', [
            'activeSection' => $this->activeSection,
            'showAddStatusForm' => $this->showAddStatusForm,
            'showDepartmentGroupAccess' => $this->showDepartmentGroupAccess,
            'ticketStatuses' => count($this->ticketStatuses),
            'departmentGroups' => count($this->departmentGroups)
        ]);
        
        return [
            'activeSection' => $this->activeSection,
            'showAddStatusForm' => $this->showAddStatusForm,
            'showDepartmentGroupAccess' => $this->showDepartmentGroupAccess,
            'ticketStatusesCount' => count($this->ticketStatuses),
            'departmentGroupsCount' => count($this->departmentGroups)
        ];
    }

    public function render()
    {
        return view('livewire.admin.settings.modules.ticket-settings.index', [
            'sections' => $this->getSections(),
        ]);
    }

    private function updateProgress(): void
    {
        $progressTracker = app(SettingsProgressTracker::class);
        
        // Mark sections as completed based on data presence
        $progressTracker->markSectionCompleted('ticket', 'workflow');
        $progressTracker->markSectionCompleted('ticket', 'attachment');
        $progressTracker->markSectionCompleted('ticket', 'priority');
        $progressTracker->markSectionCompleted('ticket', 'status');
    }
}
