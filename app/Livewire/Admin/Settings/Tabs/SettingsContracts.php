<?php

namespace App\Livewire\Admin\Settings\Tabs;

use Livewire\Component;
use App\Models\ContractType;
use App\Models\ContractStatus;
use Livewire\Attributes\Computed;

class SettingsContracts extends Component
{
    #[Computed]
    public function contractTypes()
    {
        return ContractType::ordered()->get();
    }

    #[Computed]
    public function contractStatuses()
    {
        return ContractStatus::ordered()->get();
    }

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
        // Data is now loaded via computed properties
        // This method kept for compatibility with listeners
    }

    public function refreshData()
    {
        $this->loadData();
    }

    // Contract Type Methods
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
        $type = ContractType::find($id);
        
        if (!$type) {
            $this->dispatch('error', 'Contract type not found.');
            return;
        }

        if ($type->is_protected) {
            $this->dispatch('error', 'Cannot edit protected contract type.');
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
                    ? 'unique:contract_types,slug,' . $this->selectedTypeId
                    : 'unique:contract_types,slug',
            ],
            'typeForm.sort_order' => 'integer|min:0',
            'typeForm.is_protected' => 'boolean',
        ]);

        try {
            if ($this->typeEditMode) {
                $type = ContractType::findOrFail($this->selectedTypeId);
                $type->update($this->typeForm);
                $message = 'Contract type updated successfully.';
            } else {
                ContractType::create($this->typeForm);
                $message = 'Contract type created successfully.';
            }
            
            $this->closeTypeModal();
            $this->dispatch('saved', $message);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save contract type: ' . $e->getMessage());
        }
    }

    public function confirmDeleteType($id)
    {
        $this->checkPermission('settings.update');
        $type = ContractType::find($id);
        
        if (!$type) {
            $this->dispatch('error', 'Contract type not found.');
            return;
        }
        
        if ($type->is_protected) {
            $this->dispatch('error', 'Cannot delete protected contract type.');
            return;
        }
        
        // Check if type is in use
        if ($type->organizationContracts()->count() > 0) {
            $this->dispatch('error', 'Cannot delete contract type that is in use.');
            return;
        }
        
        $this->confirmingTypeDelete = $id;
    }

    public function deleteType()
    {
        $this->checkPermission('settings.update');
        
        try {
            ContractType::findOrFail($this->confirmingTypeDelete)->delete();
            $this->confirmingTypeDelete = null;
            $this->dispatch('saved', 'Contract type deleted successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete contract type: ' . $e->getMessage());
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

    // Contract Status Methods
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
        $status = ContractStatus::find($id);
        
        if (!$status) {
            $this->dispatch('error', 'Contract status not found.');
            return;
        }

        if ($status->is_protected) {
            $this->dispatch('error', 'Cannot edit protected contract status.');
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
                    ? 'unique:contract_statuses,slug,' . $this->selectedStatusId
                    : 'unique:contract_statuses,slug',
            ],
            'statusForm.sort_order' => 'integer|min:0',
            'statusForm.is_protected' => 'boolean',
        ]);

        try {
            if ($this->statusEditMode) {
                $status = ContractStatus::findOrFail($this->selectedStatusId);
                $status->update($this->statusForm);
                $message = 'Contract status updated successfully.';
            } else {
                ContractStatus::create($this->statusForm);
                $message = 'Contract status created successfully.';
            }
            
            $this->closeStatusModal();
            $this->dispatch('saved', $message);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save contract status: ' . $e->getMessage());
        }
    }

    public function confirmDeleteStatus($id)
    {
        $this->checkPermission('settings.update');
        $status = ContractStatus::find($id);
        
        if (!$status) {
            $this->dispatch('error', 'Contract status not found.');
            return;
        }
        
        if ($status->is_protected) {
            $this->dispatch('error', 'Cannot delete protected contract status.');
            return;
        }
        
        // Check if status is in use
        if ($status->organizationContracts()->count() > 0) {
            $this->dispatch('error', 'Cannot delete contract status that is in use.');
            return;
        }
        
        $this->confirmingStatusDelete = $id;
    }

    public function deleteStatus()
    {
        $this->checkPermission('settings.update');
        
        try {
            ContractStatus::findOrFail($this->confirmingStatusDelete)->delete();
            $this->confirmingStatusDelete = null;
            $this->dispatch('saved', 'Contract status deleted successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete contract status: ' . $e->getMessage());
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
            'sort_order' => $this->contractTypes->count() + 1,
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
            'sort_order' => $this->contractStatuses->count() + 1,
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
        return view('livewire.admin.settings.tabs.contracts');
    }
}