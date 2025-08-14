<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Models\Department;
use App\Models\DepartmentGroup;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SettingsOrganization extends Component
{
    // Department Group Management
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

    // Department Management
    public bool $showDeptModal = false;
    public bool $deptEditMode = false;
    public ?int $selectedDeptId = null;
    public array $deptForm = [
        'name' => '',
        'description' => '',
        'department_group_id' => null,
        'email' => '',
        'is_active' => true,
        'sort_order' => 0,
    ];

    // Delete confirmations
    public ?int $confirmingDeptGroupDelete = null;
    public ?int $confirmingDeptDelete = null;

    protected $listeners = ['tabChanged' => 'refreshData'];

    public function mount()
    {
        $this->checkPermission('settings.read');
    }

    public function refreshData()
    {
        // Refresh computed properties by clearing cache
        unset($this->departmentGroups);
        unset($this->departments);
    }

    #[Computed]
    public function departmentGroups()
    {
        return DepartmentGroup::withCount('departments')->ordered()->get();
    }

    #[Computed]
    public function departments()
    {
        return Department::with(['departmentGroup:id,name'])
            ->withCount(['users', 'tickets'])
            ->ordered()
            ->get();
    }

    // Department Group Methods
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
            $this->dispatch('saved', $message);
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save department group: ' . $e->getMessage());
        }
    }

    public function confirmDeleteDeptGroup($id)
    {
        $this->checkPermission('department-groups.delete');
        $group = DepartmentGroup::withCount('departments')->findOrFail($id);
        if ($group->departments_count > 0) {
            $this->dispatch('error', 'Cannot delete department group with associated departments.');
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
            $this->dispatch('saved', 'Department group deleted successfully.');
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete department group: ' . $e->getMessage());
        }
    }

    public function closeDeptGroupModal()
    {
        $this->showDeptGroupModal = false;
        $this->resetDeptGroupForm();
    }

    public function cancelDeptGroupDelete()
    {
        $this->confirmingDeptGroupDelete = null;
    }

    // Department Methods
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
            $this->dispatch('saved', $message);
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save department: ' . $e->getMessage());
        }
    }

    public function confirmDeleteDept($id)
    {
        $this->checkPermission('departments.delete');
        $dept = Department::withCount(['users', 'tickets'])->findOrFail($id);
        if ($dept->users_count > 0 || $dept->tickets_count > 0) {
            $this->dispatch('error', 'Cannot delete department with associated users or tickets.');
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
            $this->dispatch('saved', 'Department deleted successfully.');
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete department: ' . $e->getMessage());
        }
    }

    public function closeDeptModal()
    {
        $this->showDeptModal = false;
        $this->resetDeptForm();
    }

    public function cancelDeptDelete()
    {
        $this->confirmingDeptDelete = null;
    }

    // Helper Methods
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

    private function resetDeptForm()
    {
        $this->deptForm = [
            'name' => '',
            'description' => '',
            'department_group_id' => null,
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
        return view('livewire.admin.settings.tabs.organization');
    }
}