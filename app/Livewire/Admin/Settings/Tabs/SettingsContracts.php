<?php

namespace App\Livewire\Admin\Settings\Tabs;

use Livewire\Component;

class SettingsContracts extends Component
{
    // Contract Types Management
    public array $contractTypes = [
        ['id' => 1, 'name' => 'Annual Support', 'slug' => 'annual_support', 'sort_order' => 1, 'is_protected' => true],
        ['id' => 2, 'name' => 'Monthly Support', 'slug' => 'monthly_support', 'sort_order' => 2, 'is_protected' => true],
        ['id' => 3, 'name' => 'Project-Based', 'slug' => 'project_based', 'sort_order' => 3, 'is_protected' => false],
        ['id' => 4, 'name' => 'Maintenance', 'slug' => 'maintenance', 'sort_order' => 4, 'is_protected' => false],
    ];

    // Contract Statuses Management  
    public array $contractStatuses = [
        ['id' => 1, 'name' => 'Active', 'slug' => 'active', 'sort_order' => 1, 'is_protected' => true],
        ['id' => 2, 'name' => 'Expired', 'slug' => 'expired', 'sort_order' => 2, 'is_protected' => true],
        ['id' => 3, 'name' => 'Suspended', 'slug' => 'suspended', 'sort_order' => 3, 'is_protected' => false],
        ['id' => 4, 'name' => 'Cancelled', 'slug' => 'cancelled', 'sort_order' => 4, 'is_protected' => false],
    ];

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
        // In future implementation, these would load from database lookup tables
        // For now, using hardcoded data as placeholder
        
        // TODO: Load from ContractType and ContractStatus models when migrations are created
        // $this->contractTypes = ContractType::orderBy('sort_order')->get()->toArray();
        // $this->contractStatuses = ContractStatus::orderBy('sort_order')->get()->toArray();
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
        $type = collect($this->contractTypes)->firstWhere('id', $id);
        
        if (!$type) {
            $this->dispatch('error', 'Contract type not found.');
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
                $index = collect($this->contractTypes)->search(fn($item) => $item['id'] === $this->selectedTypeId);
                if ($index !== false) {
                    $this->contractTypes[$index] = array_merge($this->contractTypes[$index], $this->typeForm);
                }
                $message = 'Contract type updated successfully.';
            } else {
                // Add new type
                $newId = collect($this->contractTypes)->max('id') + 1;
                $this->contractTypes[] = array_merge($this->typeForm, ['id' => $newId]);
                $message = 'Contract type created successfully.';
            }

            // TODO: Save to database when lookup tables are implemented
            
            $this->closeTypeModal();
            $this->dispatch('saved', $message);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save contract type: ' . $e->getMessage());
        }
    }

    public function confirmDeleteType($id)
    {
        $this->checkPermission('settings.update');
        $type = collect($this->contractTypes)->firstWhere('id', $id);
        
        if ($type && $type['is_protected']) {
            $this->dispatch('error', 'Cannot delete protected contract type.');
            return;
        }
        
        $this->confirmingTypeDelete = $id;
    }

    public function deleteType()
    {
        $this->checkPermission('settings.update');
        
        try {
            $this->contractTypes = collect($this->contractTypes)
                ->reject(fn($item) => $item['id'] === $this->confirmingTypeDelete)
                ->values()
                ->toArray();
                
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
        $status = collect($this->contractStatuses)->firstWhere('id', $id);
        
        if (!$status) {
            $this->dispatch('error', 'Contract status not found.');
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
                $index = collect($this->contractStatuses)->search(fn($item) => $item['id'] === $this->selectedStatusId);
                if ($index !== false) {
                    $this->contractStatuses[$index] = array_merge($this->contractStatuses[$index], $this->statusForm);
                }
                $message = 'Contract status updated successfully.';
            } else {
                // Add new status
                $newId = collect($this->contractStatuses)->max('id') + 1;
                $this->contractStatuses[] = array_merge($this->statusForm, ['id' => $newId]);
                $message = 'Contract status created successfully.';
            }

            // TODO: Save to database when lookup tables are implemented
            
            $this->closeStatusModal();
            $this->dispatch('saved', $message);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save contract status: ' . $e->getMessage());
        }
    }

    public function confirmDeleteStatus($id)
    {
        $this->checkPermission('settings.update');
        $status = collect($this->contractStatuses)->firstWhere('id', $id);
        
        if ($status && $status['is_protected']) {
            $this->dispatch('error', 'Cannot delete protected contract status.');
            return;
        }
        
        $this->confirmingStatusDelete = $id;
    }

    public function deleteStatus()
    {
        $this->checkPermission('settings.update');
        
        try {
            $this->contractStatuses = collect($this->contractStatuses)
                ->reject(fn($item) => $item['id'] === $this->confirmingStatusDelete)
                ->values()
                ->toArray();
                
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
            'sort_order' => count($this->contractTypes) + 1,
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
            'sort_order' => count($this->contractStatuses) + 1,
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