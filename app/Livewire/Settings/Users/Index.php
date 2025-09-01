<?php

namespace App\Livewire\Settings\Users;

use App\Models\DepartmentGroup;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;

    // Department Groups
    public $showCreateGroupForm = false;
    public $showEditGroupForm = false;
    public $editingGroup = null;
    public $groupName = '';
    public $groupDescription = '';
    public $groupColor = '#6b7280';
    public $groupSortOrder = 0;
    public $groupIsActive = true;

    // Departments
    public $showCreateDepartmentForm = false;
    public $showEditDepartmentForm = false;
    public $editingDepartment = null;
    public $departmentName = '';
    public $departmentDescription = '';
    public $departmentGroupId = '';
    public $departmentSortOrder = 0;
    public $departmentIsActive = true;

    // Search and filters
    public $searchGroups = '';
    public $searchDepartments = '';
    public $filterGroup = '';

    protected $rules = [
        // Department Group rules
        'groupName' => 'required|string|max:255|unique:department_groups,name',
        'groupDescription' => 'nullable|string|max:500',
        'groupColor' => 'required|string|max:7',
        'groupSortOrder' => 'required|integer|min:0',
        'groupIsActive' => 'boolean',
        
        // Department rules
        'departmentName' => 'required|string|max:255',
        'departmentDescription' => 'nullable|string|max:500',
        'departmentGroupId' => 'nullable|exists:department_groups,id',
        'departmentSortOrder' => 'required|integer|min:0',
        'departmentIsActive' => 'boolean',
    ];

    public function mount()
    {
        $this->resetForms();
    }

    public function resetForms()
    {
        // Reset department group form
        $this->groupName = '';
        $this->groupDescription = '';
        $this->groupColor = '#6b7280';
        $this->groupSortOrder = 0;
        $this->groupIsActive = true;
        $this->editingGroup = null;

        // Reset department form
        $this->departmentName = '';
        $this->departmentDescription = '';
        $this->departmentGroupId = '';
        $this->departmentSortOrder = 0;
        $this->departmentIsActive = true;
        $this->editingDepartment = null;
    }

    // Department Group Methods
    public function createDepartmentGroup()
    {
        $this->validate([
            'groupName' => 'required|string|max:255|unique:department_groups,name',
            'groupDescription' => 'nullable|string|max:500',
            'groupColor' => 'required|string|max:7',
            'groupSortOrder' => 'required|integer|min:0',
            'groupIsActive' => 'boolean',
        ]);

        DepartmentGroup::create([
            'name' => $this->groupName,
            'description' => $this->groupDescription,
            'color' => $this->groupColor,
            'sort_order' => $this->groupSortOrder,
            'is_active' => $this->groupIsActive,
        ]);

        $this->resetForms();
        $this->showCreateGroupForm = false;
        session()->flash('message', 'Department group created successfully.');
    }

    public function editDepartmentGroup($groupId)
    {
        $this->editingGroup = DepartmentGroup::findOrFail($groupId);
        
        $this->groupName = $this->editingGroup->name;
        $this->groupDescription = $this->editingGroup->description;
        $this->groupColor = $this->editingGroup->color;
        $this->groupSortOrder = $this->editingGroup->sort_order;
        $this->groupIsActive = $this->editingGroup->is_active;
        
        $this->showEditGroupForm = true;
    }

    public function updateDepartmentGroup()
    {
        $this->validate([
            'groupName' => ['required', 'string', 'max:255', Rule::unique('department_groups')->ignore($this->editingGroup->id)],
            'groupDescription' => 'nullable|string|max:500',
            'groupColor' => 'required|string|max:7',
            'groupSortOrder' => 'required|integer|min:0',
            'groupIsActive' => 'boolean',
        ]);

        $this->editingGroup->update([
            'name' => $this->groupName,
            'description' => $this->groupDescription,
            'color' => $this->groupColor,
            'sort_order' => $this->groupSortOrder,
            'is_active' => $this->groupIsActive,
        ]);

        $this->resetForms();
        $this->showEditGroupForm = false;
        session()->flash('message', 'Department group updated successfully.');
    }

    public function deleteDepartmentGroup($groupId)
    {
        $group = DepartmentGroup::findOrFail($groupId);
        
        // Check if group has departments
        if ($group->departments()->count() > 0) {
            session()->flash('error', 'Cannot delete department group that has departments. Please reassign or delete the departments first.');
            return;
        }

        // Check if group has users
        if ($group->users()->count() > 0) {
            session()->flash('error', 'Cannot delete department group that has users. Please reassign the users first.');
            return;
        }

        $group->delete();
        session()->flash('message', 'Department group deleted successfully.');
    }

    public function toggleDepartmentGroupStatus($groupId)
    {
        $group = DepartmentGroup::findOrFail($groupId);
        $group->update(['is_active' => !$group->is_active]);
        session()->flash('message', 'Department group status updated successfully.');
    }

    // Department Methods
    public function createDepartment()
    {
        $this->validate([
            'departmentName' => 'required|string|max:255',
            'departmentDescription' => 'nullable|string|max:500',
            'departmentGroupId' => 'nullable|exists:department_groups,id',
            'departmentSortOrder' => 'required|integer|min:0',
            'departmentIsActive' => 'boolean',
        ]);

        Department::create([
            'name' => $this->departmentName,
            'description' => $this->departmentDescription,
            'department_group_id' => $this->departmentGroupId ?: null,
            'sort_order' => $this->departmentSortOrder,
            'is_active' => $this->departmentIsActive,
        ]);

        $this->resetForms();
        $this->showCreateDepartmentForm = false;
        session()->flash('message', 'Department created successfully.');
    }

    public function editDepartment($departmentId)
    {
        $this->editingDepartment = Department::findOrFail($departmentId);
        
        $this->departmentName = $this->editingDepartment->name;
        $this->departmentDescription = $this->editingDepartment->description;
        $this->departmentGroupId = $this->editingDepartment->department_group_id;
        $this->departmentSortOrder = $this->editingDepartment->sort_order;
        $this->departmentIsActive = $this->editingDepartment->is_active;
        
        $this->showEditDepartmentForm = true;
    }

    public function updateDepartment()
    {
        $this->validate([
            'departmentName' => 'required|string|max:255',
            'departmentDescription' => 'nullable|string|max:500',
            'departmentGroupId' => 'nullable|exists:department_groups,id',
            'departmentSortOrder' => 'required|integer|min:0',
            'departmentIsActive' => 'boolean',
        ]);

        $this->editingDepartment->update([
            'name' => $this->departmentName,
            'description' => $this->departmentDescription,
            'department_group_id' => $this->departmentGroupId ?: null,
            'sort_order' => $this->departmentSortOrder,
            'is_active' => $this->departmentIsActive,
        ]);

        $this->resetForms();
        $this->showEditDepartmentForm = false;
        session()->flash('message', 'Department updated successfully.');
    }

    public function deleteDepartment($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        
        // Check if department has tickets
        if ($department->tickets()->count() > 0) {
            session()->flash('error', 'Cannot delete department that has tickets. Please reassign or close the tickets first.');
            return;
        }

        $department->delete();
        session()->flash('message', 'Department deleted successfully.');
    }

    public function toggleDepartmentStatus($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $department->update(['is_active' => !$department->is_active]);
        session()->flash('message', 'Department status updated successfully.');
    }

    public function render()
    {
        // Department Groups query
        $groupsQuery = DepartmentGroup::query()
            ->withCount(['departments', 'users'])
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($this->searchGroups) {
            $groupsQuery->where('name', 'like', "%{$this->searchGroups}%")
                       ->orWhere('description', 'like', "%{$this->searchGroups}%");
        }

        $departmentGroups = $groupsQuery->paginate(10, ['*'], 'groups_page');

        // Departments query
        $departmentsQuery = Department::query()
            ->with('departmentGroup')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($this->searchDepartments) {
            $departmentsQuery->where('name', 'like', "%{$this->searchDepartments}%")
                           ->orWhere('description', 'like', "%{$this->searchDepartments}%");
        }

        if ($this->filterGroup) {
            $departmentsQuery->where('department_group_id', $this->filterGroup);
        }

        $departments = $departmentsQuery->paginate(10, ['*'], 'departments_page');

        // Get all active department groups for dropdowns
        $allDepartmentGroups = DepartmentGroup::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.settings.users.index', [
            'departmentGroups' => $departmentGroups,
            'departments' => $departments,
            'allDepartmentGroups' => $allDepartmentGroups,
        ]);
    }
}
