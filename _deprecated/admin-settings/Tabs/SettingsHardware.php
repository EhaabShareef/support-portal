<?php

namespace App\Livewire\Admin\Settings\Tabs;

use Livewire\Component;
use App\Models\HardwareType;
use App\Models\HardwareStatus;
use Livewire\Attributes\Computed;

class SettingsHardware extends Component
{

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
        // Data now loaded via computed properties
    }

    public function refreshData()
    {
        $this->loadData();
    }

    #[Computed]
    public function hardwareTypes()
    {
        return HardwareType::ordered()->get();
    }

    #[Computed]
    public function hardwareStatuses()
    {
        return HardwareStatus::ordered()->get();
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
        $type = HardwareType::find($id);
        
        if (!$type) {
            $this->dispatch('error', 'Hardware type not found.');
            return;
        }

        if ($type->is_protected) {
            $this->dispatch('error', 'Cannot edit protected hardware type.');
            return;
        }

        $this->selectedTypeId = $id;
        $this->typeForm = [
            'name' => $type->name,
            'slug' => $type->slug,
            'sort_order' => $type->sort_order,
            'is_protected' => $type->is_protected,
        ];
        $this->typeEditMode = true;
        $this->showTypeModal = true;
    }

    public function saveType()
    {
        $this->checkPermission('settings.update');

        $this->validate([
            'typeForm.name' => 'required|string|max:255',
            'typeForm.slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                $this->typeEditMode 
                    ? 'unique:hardware_types,slug,' . $this->selectedTypeId
                    : 'unique:hardware_types,slug',
            ],
            'typeForm.sort_order' => 'integer|min:0',
            'typeForm.is_protected' => 'boolean',
        ]);

        try {
            if ($this->typeEditMode) {
                $type = HardwareType::findOrFail($this->selectedTypeId);
                $type->update($this->typeForm);
                $message = 'Hardware type updated successfully.';
            } else {
                HardwareType::create($this->typeForm);
                $message = 'Hardware type created successfully.';
            }

            $this->closeTypeModal();
            $this->dispatch('saved', $message);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save hardware type: ' . $e->getMessage());
        }
    }

    public function confirmDeleteType($id)
    {
        $this->checkPermission('settings.update');
        $type = HardwareType::find($id);
        
        if (!$type) {
            $this->dispatch('error', 'Hardware type not found.');
            return;
        }
        
        if ($type->is_protected) {
            $this->dispatch('error', 'Cannot delete protected hardware type.');
            return;
        }
        
        // Check if type is in use
        if ($type->organizationHardware()->count() > 0) {
            $this->dispatch('error', 'Cannot delete hardware type that is in use.');
            return;
        }
        
        $this->confirmingTypeDelete = $id;
    }

    public function deleteType()
    {
        $this->checkPermission('settings.update');
        
        try {
            HardwareType::findOrFail($this->confirmingTypeDelete)->delete();
            $this->confirmingTypeDelete = null;
            $this->dispatch('saved', 'Hardware type deleted successfully.');
            
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
        $status = HardwareStatus::find($id);
        
        if (!$status) {
            $this->dispatch('error', 'Hardware status not found.');
            return;
        }

        if ($status->is_protected) {
            $this->dispatch('error', 'Cannot edit protected hardware status.');
            return;
        }

        $this->selectedStatusId = $id;
        $this->statusForm = [
            'name' => $status->name,
            'slug' => $status->slug,
            'sort_order' => $status->sort_order,
            'is_protected' => $status->is_protected,
        ];
        $this->statusEditMode = true;
        $this->showStatusModal = true;
    }

    public function saveStatus()
    {
        $this->checkPermission('settings.update');

        $this->validate([
            'statusForm.name' => 'required|string|max:255',
            'statusForm.slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                $this->statusEditMode 
                    ? 'unique:hardware_statuses,slug,' . $this->selectedStatusId
                    : 'unique:hardware_statuses,slug',
            ],
            'statusForm.sort_order' => 'integer|min:0',
            'statusForm.is_protected' => 'boolean',
        ]);

        try {
            if ($this->statusEditMode) {
                $status = HardwareStatus::findOrFail($this->selectedStatusId);
                $status->update($this->statusForm);
                $message = 'Hardware status updated successfully.';
            } else {
                HardwareStatus::create($this->statusForm);
                $message = 'Hardware status created successfully.';
            }
            
            $this->closeStatusModal();
            $this->dispatch('saved', $message);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save hardware status: ' . $e->getMessage());
        }
    }

    public function confirmDeleteStatus($id)
    {
        $this->checkPermission('settings.update');
        $status = HardwareStatus::find($id);
        
        if (!$status) {
            $this->dispatch('error', 'Hardware status not found.');
            return;
        }
        
        if ($status->is_protected) {
            $this->dispatch('error', 'Cannot delete protected hardware status.');
            return;
        }
        
        // Check if status is in use
        if ($status->organizationHardware()->count() > 0) {
            $this->dispatch('error', 'Cannot delete hardware status that is in use.');
            return;
        }
        
        $this->confirmingStatusDelete = $id;
    }

    public function deleteStatus()
    {
        $this->checkPermission('settings.update');
        
        try {
            HardwareStatus::findOrFail($this->confirmingStatusDelete)->delete();
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
            'sort_order' => $this->hardwareTypes->count() + 1,
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
            'sort_order' => $this->hardwareStatuses->count() + 1,
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