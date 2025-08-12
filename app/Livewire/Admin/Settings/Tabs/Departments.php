<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Models\Department;
use App\Models\DepartmentGroup;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Departments extends Component
{
    public bool $showDeptModal = false;
    public bool $deptEditMode = false;
    public ?int $selectedDeptId = null;
    public array $deptForm = [
        'name' => '',
        'description' => '',
        'department_group_id' => '',
        'email' => '',
        'is_active' => true,
        'sort_order' => 0,
    ];

    // Delete confirmations
    public ?int $confirmingDeptDelete = null;

    #[Computed]
    public function departments()
    {
        return Department::with('departmentGroup')->withCount(['users', 'tickets'])->ordered()->get();
    }

    #[Computed]
    public function availableDeptGroups()
    {
        return DepartmentGroup::active()->ordered()->get();
    }

    public function createDept()
    {
        $this->checkPermission('departments.create');
        $this->resetDeptForm();
        $this->deptEditMode = false;
        $this->showDeptModal = true;
    }

    public function editDept($id)
    {
        $this->checkPermission('departments.update');
        $dept = Department::findOrFail($id);
        $this->selectedDeptId = $id;
        $this->deptForm = $dept->toArray();
        $this->deptEditMode = true;
        $this->showDeptModal = true;
    }

    public function saveDept()
    {
        $this->checkPermission($this->deptEditMode ? 'departments.update' : 'departments.create');

        $validated = $this->validate([
            'deptForm.name' => 'required|string|max:255',
            'deptForm.description' => 'nullable|string',
            'deptForm.department_group_id' => 'nullable|exists:department_groups,id',
            'deptForm.email' => 'nullable|email|max:255',
            'deptForm.is_active' => 'boolean',
            'deptForm.sort_order' => 'integer|min:0',
        ]);

        try {
            if ($this->deptEditMode) {
                Department::findOrFail($this->selectedDeptId)->update($validated['deptForm']);
                $message = 'Department updated successfully.';
            } else {
                Department::create($validated['deptForm']);
                $message = 'Department created successfully.';
            }

            $this->closeDeptModal();
            $this->dispatch('flash', $message, 'success');
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to save department: ' . $e->getMessage(), 'error');
        }
    }

    public function confirmDeleteDept($id)
    {
        $this->checkPermission('departments.delete');
        $dept = Department::withCount(['users', 'tickets'])->findOrFail($id);
        if ($dept->users_count > 0 || $dept->tickets_count > 0) {
            $this->dispatch('flash', 'Cannot delete department with associated users or tickets.', 'error');
            return;
        }
        $this->confirmingDeptDelete = $id;
    }

    public function deleteDept()
    {
        $this->checkPermission('departments.delete');
        
        try {
            Department::findOrFail($this->confirmingDeptDelete)->delete();
            $this->confirmingDeptDelete = null;
            $this->dispatch('flash', 'Department deleted successfully.', 'success');
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to delete department: ' . $e->getMessage(), 'error');
        }
    }

    public function closeDeptModal()
    {
        $this->showDeptModal = false;
        $this->resetDeptForm();
    }

    public function cancelDelete()
    {
        $this->confirmingDeptDelete = null;
    }

    private function resetDeptForm()
    {
        $this->deptForm = [
            'name' => '',
            'description' => '',
            'department_group_id' => '',
            'email' => '',
            'is_active' => true,
            'sort_order' => 0,
        ];
        $this->selectedDeptId = null;
        $this->resetErrorBag('deptForm');
    }

    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to {$permission}.");
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.tabs.departments');
    }
}