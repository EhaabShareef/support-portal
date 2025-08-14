<?php

namespace App\Livewire\Admin\Settings\Tabs;

use Livewire\Component;
use App\Models\HardwareType;
use App\Models\HardwareStatus;

class SettingsHardware extends Component
{
    // Hardware Types Management
    public array $hardwareTypes = [];

    // Hardware Statuses Management  
    public array $hardwareStatuses = [];

    public bool $showTypeModal = false;
    public bool $showStatusModal = false;
    public bool $typeEditMode = false;
    public bool $statusEditMode = false;
    
    public array $typeForm = [
        'name' => '',
        'slug' => '',
        'sort_order' => 0,
        'is_protected' => false,
    ];

    public array $statusForm = [
        'name' => '',
        'slug' => '',
        'sort_order' => 0,
        'is_protected' => false,
    ];

    public ?int $selectedTypeId = null;
    public ?int $selectedStatusId = null;
    public ?int $confirmingTypeDelete = null;
    public ?int $confirmingStatusDelete = null;

    protected $listeners = ['tabChanged' => 'refreshData'];

    public function mount()
    {
        $this->checkPermission('settings.read');
        $this->loadData();
    }

    public function loadData()
    {
        // Load from database models
        $this->hardwareTypes = HardwareType::ordered()->get()->toArray();
        
        // Load from database models
        try {
            $this->hardwareStatuses = HardwareStatus::ordered()->get()->toArray();
        } catch (\Exception $e) {
            // Fallback to hardcoded data if table doesn't exist yet
            $this->hardwareStatuses = [
                ['id' => 1, 'name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_protected' => true],
                ['id' => 2, 'name' => 'Inactive', 'slug' => 'inactive', 'sort_order' => 2, 'is_protected' => true],
                ['id' => 3, 'name' => 'Maintenance', 'slug' => 'maintenance', 'sort_order' => 3, 'is_protected' => false],
                ['id' => 4, 'name' => 'Retired', 'slug' => 'retired', 'sort_order' => 4, 'is_protected' => false],
                ['id' => 5, 'name' => 'Under Repair', 'slug' => 'under_repair', 'sort_order' => 5, 'is_protected' => false],
            ];
        }
    }

    public function refreshData()
    {
        $this->loadData();
    }

    // Hardware Type Methods
    public function createType()
    {
        $this->checkPermission('settings.update');
        $this->resetTypeForm();
        $this->typeEditMode = false;
        $this->showTypeModal = true;
    }

    public function editType($id)
    {
        $this->checkPermission('settings.update');
        $type = collect($this->hardwareTypes)->firstWhere('id', $id);
        
        if (!$type) {
            $this->dispatch('error', 'Hardware type not found.');
            return;
        }

        $this->selectedTypeId = $id;
        $this->typeForm = [
            'name' => $type['name'],
            'slug' => $type['slug'],
            'sort_order' => $type['sort_order'],
            'is_protected' => $type['is_protected'],
        ];
        $this->typeEditMode = true;
        $this->showTypeModal = true;
    }

    public function saveType()
    {
        $this->checkPermission('settings.update');

        $this->validate([
            'typeForm.name' => 'required|string|max:255',
            'typeForm.slug' => 'required|string|max:255|regex:/^[a-z0-9_]+$/',
            'typeForm.sort_order' => 'integer|min:0',
            'typeForm.is_protected' => 'boolean',
        ]);

        try {
            if ($this->typeEditMode) {
                // Update existing type
                $type = HardwareType::find($this->selectedTypeId);
                if ($type) {
                    $type->update($this->typeForm);
                    $message = 'Hardware type updated successfully.';
                } else {
                    $this->dispatch('error', 'Hardware type not found.');
                    return;
                }
            } else {
                // Add new type
                HardwareType::create($this->typeForm);
                $message = 'Hardware type created successfully.';
            }

            $this->loadData();
            $this->closeTypeModal();
            $this->dispatch('saved', $message);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save hardware type: ' . $e->getMessage());
        }
    }

    public function confirmDeleteType($id)
    {
        $this->checkPermission('settings.update');
        $type = collect($this->hardwareTypes)->firstWhere('id', $id);
        
        if ($type && $type['is_protected']) {
            $this->dispatch('error', 'Cannot delete protected hardware type.');
            return;
        }
        
        $this->confirmingTypeDelete = $id;
    }

    public function deleteType()
    {
        $this->checkPermission('settings.update');
        
        try {
            $type = HardwareType::find($this->confirmingTypeDelete);
            if ($type) {
                $type->delete();
                $this->loadData();
                $this->confirmingTypeDelete = null;
                $this->dispatch('saved', 'Hardware type deleted successfully.');
            } else {
                $this->dispatch('error', 'Hardware type not found.');
            }
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete hardware type: ' . $e->getMessage());
        }
    }

    public function closeTypeModal()
    {
        $this->showTypeModal = false;
        $this->resetTypeForm();
    }

    public function cancelTypeDelete()
    {
        $this->confirmingTypeDelete = null;
    }

    // Hardware Status Methods
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
        $status = collect($this->hardwareStatuses)->firstWhere('id', $id);
        
        if (!$status) {
            $this->dispatch('error', 'Hardware status not found.');
            return;
        }

        $this->selectedStatusId = $id;
        $this->statusForm = [
            'name' => $status['name'],
            'slug' => $status['slug'],
            'sort_order' => $status['sort_order'],
            'is_protected' => $status['is_protected'],
        ];
        $this->statusEditMode = true;
        $this->showStatusModal = true;
    }

    public function saveStatus()
    {
        $this->checkPermission('settings.update');

        $this->validate([
            'statusForm.name' => 'required|string|max:255',
            'statusForm.slug' => 'required|string|max:255|regex:/^[a-z0-9_]+$/',
            'statusForm.sort_order' => 'integer|min:0',
            'statusForm.is_protected' => 'boolean',
        ]);

        try {
            if ($this->statusEditMode) {
                // Update existing status
                $index = collect($this->hardwareStatuses)->search(fn($item) => $item['id'] === $this->selectedStatusId);
                if ($index !== false) {
                    $this->hardwareStatuses[$index] = array_merge($this->hardwareStatuses[$index], $this->statusForm);
                }
                $message = 'Hardware status updated successfully.';
            } else {
                // Add new status
                $newId = collect($this->hardwareStatuses)->max('id') + 1;
                $this->hardwareStatuses[] = array_merge($this->statusForm, ['id' => $newId]);
                $message = 'Hardware status created successfully.';
            }

            // TODO: Save to database when lookup tables are implemented
            
            $this->closeStatusModal();
            $this->dispatch('saved', $message);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save hardware status: ' . $e->getMessage());
        }
    }

    public function confirmDeleteStatus($id)
    {
        $this->checkPermission('settings.update');
        $status = collect($this->hardwareStatuses)->firstWhere('id', $id);
        
        if ($status && $status['is_protected']) {
            $this->dispatch('error', 'Cannot delete protected hardware status.');
            return;
        }
        
        $this->confirmingStatusDelete = $id;
    }

    public function deleteStatus()
    {
        $this->checkPermission('settings.update');
        
        try {
            $this->hardwareStatuses = collect($this->hardwareStatuses)
                ->reject(fn($item) => $item['id'] === $this->confirmingStatusDelete)
                ->values()
                ->toArray();
                
            $this->confirmingStatusDelete = null;
            $this->dispatch('saved', 'Hardware status deleted successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete hardware status: ' . $e->getMessage());
        }
    }

    public function closeStatusModal()
    {
        $this->showStatusModal = false;
        $this->resetStatusForm();
    }

    public function cancelStatusDelete()
    {
        $this->confirmingStatusDelete = null;
    }

    // Helper Methods
    private function resetTypeForm()
    {
        $this->typeForm = [
            'name' => '',
            'slug' => '',
            'sort_order' => count($this->hardwareTypes) + 1,
            'is_protected' => false,
        ];
        $this->selectedTypeId = null;
        $this->resetErrorBag('typeForm');
    }

    private function resetStatusForm()
    {
        $this->statusForm = [
            'name' => '',
            'slug' => '',
            'sort_order' => count($this->hardwareStatuses) + 1,
            'is_protected' => false,
        ];
        $this->selectedStatusId = null;
        $this->resetErrorBag('statusForm');
    }

    public function updatedTypeFormName()
    {
        if (!$this->typeEditMode) {
            $this->typeForm['slug'] = strtolower(str_replace([' ', '-'], '_', $this->typeForm['name']));
            $this->typeForm['slug'] = preg_replace('/[^a-z0-9_]/', '', $this->typeForm['slug']);
        }
    }

    public function updatedStatusFormName()
    {
        if (!$this->statusEditMode) {
            $this->statusForm['slug'] = strtolower(str_replace([' ', '-'], '_', $this->statusForm['name']));
            $this->statusForm['slug'] = preg_replace('/[^a-z0-9_]/', '', $this->statusForm['slug']);
        }
    }

    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to {$permission}.");
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.tabs.hardware');
    }
}