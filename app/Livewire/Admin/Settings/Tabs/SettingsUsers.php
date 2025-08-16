<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Contracts\SettingsRepositoryInterface;
use App\Models\Organization;
use App\Models\Department;
use App\Models\DepartmentGroup;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SettingsUsers extends Component
{
    // User Management Settings
    public ?int $defaultOrganizationId = null;
    public bool $allowUserRegistration = false;
    public bool $requireEmailVerification = true;
    public string $defaultUserRole = 'client';
    public bool $autoAssignToDefaultOrganization = true;
    public int $passwordMinLength = 8;
    public bool $requireStrongPasswords = true;

    public bool $hasUnsavedChanges = false;

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
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $repository = app(SettingsRepositoryInterface::class);
            
            // Load user management settings
            $this->defaultOrganizationId = $repository->get('users.default_organization_id', null);
            $this->allowUserRegistration = (bool) $repository->get('users.allow_registration', false);
            $this->requireEmailVerification = (bool) $repository->get('users.require_email_verification', true);
            $this->defaultUserRole = $repository->get('users.default_role', 'client');
            $this->autoAssignToDefaultOrganization = (bool) $repository->get('users.auto_assign_default_organization', true);
            $this->passwordMinLength = (int) $repository->get('users.password_min_length', 8);
            $this->requireStrongPasswords = (bool) $repository->get('users.require_strong_passwords', true);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to load user settings: ' . $e->getMessage());
        }
    }

    public function refreshData()
    {
        $this->loadData();
    }

    #[Computed]
    public function organizations()
    {
        return Organization::orderBy('name')->get();
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

    // Setting update handlers
    public function updatedDefaultOrganizationId()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedAllowUserRegistration()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedRequireEmailVerification()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedDefaultUserRole()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedAutoAssignToDefaultOrganization()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedPasswordMinLength()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedRequireStrongPasswords()
    {
        $this->hasUnsavedChanges = true;
    }

    public function saveSettings()
    {
        $this->checkPermission('settings.update');

        $this->validate([
            'defaultOrganizationId' => 'nullable|exists:organizations,id',
            'allowUserRegistration' => 'boolean',
            'requireEmailVerification' => 'boolean',
            'defaultUserRole' => 'required|in:client,support,admin',
            'autoAssignToDefaultOrganization' => 'boolean',
            'passwordMinLength' => 'required|integer|min:6|max:50',
            'requireStrongPasswords' => 'boolean',
        ]);

        try {
            $repository = app(SettingsRepositoryInterface::class);
            
            $repository->set('users.default_organization_id', $this->defaultOrganizationId, 'integer');
            $repository->set('users.allow_registration', $this->allowUserRegistration, 'boolean');
            $repository->set('users.require_email_verification', $this->requireEmailVerification, 'boolean');
            $repository->set('users.default_role', $this->defaultUserRole, 'string');
            $repository->set('users.auto_assign_default_organization', $this->autoAssignToDefaultOrganization, 'boolean');
            $repository->set('users.password_min_length', $this->passwordMinLength, 'integer');
            $repository->set('users.require_strong_passwords', $this->requireStrongPasswords, 'boolean');

            $this->hasUnsavedChanges = false;
            $this->dispatch('saved', 'User settings saved successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save user settings: ' . $e->getMessage());
        }
    }

    public function resetToDefaults()
    {
        $this->checkPermission('settings.update');
        
        try {
            $repository = app(SettingsRepositoryInterface::class);
            
            // Reset all user settings
            $repository->reset('users.default_organization_id');
            $repository->reset('users.allow_registration');
            $repository->reset('users.require_email_verification');
            $repository->reset('users.default_role');
            $repository->reset('users.auto_assign_default_organization');
            $repository->reset('users.password_min_length');
            $repository->reset('users.require_strong_passwords');
            
            // Reload data
            $this->loadData();
            $this->hasUnsavedChanges = false;
            
            $this->dispatch('reset', 'User settings reset to defaults successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }

    public function getUserRoleOptionsProperty()
    {
        return [
            'client' => 'Client - Standard user access',
            'support' => 'Support - Staff member access',
            'admin' => 'Administrator - Full system access',
        ];
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
        return view('livewire.admin.settings.tabs.users');
    }
}