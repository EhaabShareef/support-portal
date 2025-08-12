<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Models\DepartmentGroup;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DepartmentGroups extends Component
{
    public bool $showDeptGroupModal = false;
    public bool $deptGroupEditMode = false;
    public ?int $selectedDeptGroupId = null;
    public array $deptGroupForm = [
        'name' => '',
        'description' => '',
        'color' => '#3B82F6',
        'is_active' => true,
        'sort_order' => 0,
    ];

    // Delete confirmations
    public ?int $confirmingDeptGroupDelete = null;

    #[Computed]
    public function departmentGroups()
    {
        return DepartmentGroup::withCount('departments')->ordered()->get();
    }

    public function createDeptGroup()
    {
        $this->checkPermission('department-groups.create');
        $this->resetDeptGroupForm();
        $this->deptGroupEditMode = false;
        $this->showDeptGroupModal = true;
    }

    public function editDeptGroup($id)
    {
        $this->checkPermission('department-groups.update');
        $group = DepartmentGroup::findOrFail($id);
        $this->selectedDeptGroupId = $id;
        $this->deptGroupForm = $group->toArray();
        $this->deptGroupEditMode = true;
        $this->showDeptGroupModal = true;
    }

    public function saveDeptGroup()
    {
        $this->checkPermission($this->deptGroupEditMode ? 'department-groups.update' : 'department-groups.create');

        $validated = $this->validate([
            'deptGroupForm.name' => 'required|string|max:255',
            'deptGroupForm.description' => 'nullable|string',
            'deptGroupForm.color' => 'nullable|string|max:7',
            'deptGroupForm.is_active' => 'boolean',
            'deptGroupForm.sort_order' => 'integer|min:0',
        ]);

        try {
            if ($this->deptGroupEditMode) {
                DepartmentGroup::findOrFail($this->selectedDeptGroupId)->update($validated['deptGroupForm']);
                $message = 'Department group updated successfully.';
            } else {
                DepartmentGroup::create($validated['deptGroupForm']);
                $message = 'Department group created successfully.';
            }

            $this->closeDeptGroupModal();
            $this->dispatch('flash', $message, 'success');
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to save department group: ' . $e->getMessage(), 'error');
        }
    }

    public function confirmDeleteDeptGroup($id)
    {
        $this->checkPermission('department-groups.delete');
        $group = DepartmentGroup::withCount('departments')->findOrFail($id);
        if ($group->departments_count > 0) {
            $this->dispatch('flash', 'Cannot delete department group with associated departments.', 'error');
            return;
        }
        $this->confirmingDeptGroupDelete = $id;
    }

    public function deleteDeptGroup()
    {
        $this->checkPermission('department-groups.delete');
        
        try {
            DepartmentGroup::findOrFail($this->confirmingDeptGroupDelete)->delete();
            $this->confirmingDeptGroupDelete = null;
            $this->dispatch('flash', 'Department group deleted successfully.', 'success');
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to delete department group: ' . $e->getMessage(), 'error');
        }
    }

    public function closeDeptGroupModal()
    {
        $this->showDeptGroupModal = false;
        $this->resetDeptGroupForm();
    }

    public function cancelDelete()
    {
        $this->confirmingDeptGroupDelete = null;
    }

    private function resetDeptGroupForm()
    {
        $this->deptGroupForm = [
            'name' => '',
            'description' => '',
            'color' => '#3B82F6',
            'is_active' => true,
            'sort_order' => 0,
        ];
        $this->selectedDeptGroupId = null;
        $this->resetErrorBag('deptGroupForm');
    }

    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to {$permission}.");
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.tabs.department-groups');
    }
}