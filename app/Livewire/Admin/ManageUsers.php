<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\Organization;
use App\Contracts\SettingsRepositoryInterface;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class ManageUsers extends Component
{
    use WithPagination;

    public $search = '';

    public $filterRole = '';

    public $filterDepartment = '';

    public $filterStatus = false;

    // Form properties
    public $showModal = false;

    public $editMode = false;

    public $userId = null;

    public $form = [
        'name' => '',
        'username' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'department_id' => '',
        'organization_id' => '',
        'is_active' => true,
        'role' => 'support', // Default to support role
    ];

    // Confirmation properties
    public $confirmingUserDeletion = false;

    public $userToDelete = null;

    // View Access properties
    public $showAccessModal = false;
    public $viewingUserId = null;

    protected function rules()
    {
        // Get all available roles from database
        $availableRoles = Role::pluck('name')->toArray();
        $rolesString = implode(',', $availableRoles);
        
        $rules = [
            'form.name' => 'required|string|max:255',
            'form.username' => 'required|string|max:255|unique:users,username',
            'form.email' => 'required|email|unique:users,email',
            'form.password' => 'required|min:8|confirmed',
            'form.is_active' => 'boolean',
            'form.role' => 'required|in:' . $rolesString,
        ];

        // Role-specific validation
        if ($this->form['role'] === 'support') {
            $rules['form.department_id'] = 'required|exists:departments,id';
        } elseif ($this->form['role'] === 'admin') {
            // Admin users get admin department automatically, but allow override
            $rules['form.department_id'] = 'nullable|exists:departments,id';
        }

        // For edit mode, adjust unique rules
        if ($this->editMode && $this->userId) {
            $rules['form.username'] = 'required|string|max:255|unique:users,username,'.$this->userId;
            $rules['form.email'] = 'required|email|unique:users,email,'.$this->userId;

            // Password is optional on update
            if (empty($this->form['password'])) {
                unset($rules['form.password']);
            }
        }

        return $rules;
    }

    protected $messages = [
        'form.name.required' => 'Name is required',
        'form.username.required' => 'Username is required',
        'form.username.unique' => 'Username already exists',
        'form.email.required' => 'Email is required',
        'form.email.unique' => 'Email already exists',
        'form.password.required' => 'Password is required',
        'form.password.min' => 'Password must be at least 8 characters',
        'form.password.confirmed' => 'Passwords do not match',
        'form.role.required' => 'Role is required',
        'form.role.in' => 'Invalid role selected',
        'form.department_id.required' => 'Department is required for Support',
        'form.organization_id.required' => 'Organization is required for Clients',
    ];

    public function updating($field)
    {
        if (in_array($field, ['search', 'filterRole', 'filterDepartment', 'filterOrganization', 'filterStatus'])) {
            $this->resetPage();
        }

        // Clear department/organization when role changes
        if ($field === 'form.role') {
            if ($this->form['role'] === 'support') {
                // Keep department selection for agents
                $this->form['organization_id'] = '';
            } elseif ($this->form['role'] === 'client') {
                // Clear department for clients (they don't have departments)
                $this->form['department_id'] = '';
            } elseif ($this->form['role'] === 'admin') {
                // Set admin department by default for admin users
                $this->setDefaultAdminDepartment();
                $this->form['organization_id'] = '';
            }
        }
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal($userId)
    {
        $this->resetForm();
        $this->editMode = true;
        $this->userId = $userId;

        $user = User::with('roles')->findOrFail($userId);

        $this->form = [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'department_id' => $user->department_id,
            'organization_id' => $user->organization_id,
            'is_active' => $user->is_active,
            'role' => $user->roles->first()?->name ?? '',
        ];

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm()
    {
        $this->form = [
            'name' => '',
            'username' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'department_id' => '',
            'organization_id' => '',
            'is_active' => true,
            'role' => 'support', // Default to support role
        ];
        $this->userId = null;
    }

    private function setDefaultAdminDepartment()
    {
        $adminDeptId = $this->getDefaultAdminDepartmentId();
        if ($adminDeptId) {
            $this->form['department_id'] = $adminDeptId;
        }
    }

    private function getDefaultAdminDepartmentId()
    {
        // Find admin department (in Admin department group)
        $adminDept = Department::whereHas('departmentGroup', function($q) {
            $q->where('name', 'Admin');
        })->where('name', 'Admin')->first();
        
        return $adminDept?->id;
    }

    public function save()
    {
        if ($this->editMode) {
            $this->update();
        } else {
            $this->create();
        }
    }

    public function create()
    {
        $this->validate();

        $userData = [
            'name' => $this->form['name'],
            'username' => $this->form['username'],
            'email' => $this->form['email'],
            'password' => $this->form['password'],
            'is_active' => $this->form['is_active'],
        ];

        // Set department/organization based on role
        if ($this->form['role'] === 'support') {
            $userData['department_id'] = $this->form['department_id'];
            $userData['organization_id'] = app(SettingsRepositoryInterface::class)->get('default_organization', 1);
        } elseif ($this->form['role'] === 'admin') {
            // Admin users get assigned to admin department or user-selected department
            $userData['department_id'] = $this->form['department_id'] ?: $this->getDefaultAdminDepartmentId();
            $userData['organization_id'] = app(SettingsRepositoryInterface::class)->get('default_organization', 1);
        } else {
            $userData['department_id'] = null;
            $userData['organization_id'] = app(SettingsRepositoryInterface::class)->get('default_organization', 1);
        }

        $user = User::create($userData);

        // Assign the role
        $user->assignRole($this->form['role']);

        session()->flash('message', 'User created successfully.');
        $this->closeModal();
    }

    public function update()
    {
        $this->validate();

        $user = User::findOrFail($this->userId);

        $updateData = [
            'name' => $this->form['name'],
            'username' => $this->form['username'],
            'email' => $this->form['email'],
            'is_active' => $this->form['is_active'],
        ];

        // Set department/organization based on role
        if ($this->form['role'] === 'support') {
            $updateData['department_id'] = $this->form['department_id'];
            $updateData['organization_id'] = null;
        } elseif ($this->form['role'] === 'client') {
            $updateData['organization_id'] = $this->form['organization_id'];
            $updateData['department_id'] = null; // Clients have no departments
        } elseif ($this->form['role'] === 'admin') {
            // Admin users get assigned to admin department or user-selected department
            $updateData['department_id'] = $this->form['department_id'] ?: $this->getDefaultAdminDepartmentId();
            $updateData['organization_id'] = null;
        } else {
            $updateData['department_id'] = null;
            $updateData['organization_id'] = null;
        }

        // Only update password if provided
        if (! empty($this->form['password'])) {
            $updateData['password'] = $this->form['password'];
        }

        $user->update($updateData);

        // Sync the role (replace existing role)
        $user->syncRoles([$this->form['role']]);

        session()->flash('message', 'User updated successfully.');
        $this->closeModal();
    }

    public function confirmDelete($userId)
    {
        $this->userToDelete = $userId;
        $this->confirmingUserDeletion = true;
    }

    public function cancelDelete()
    {
        $this->confirmingUserDeletion = false;
        $this->userToDelete = null;
    }

    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        session()->flash('message', "User {$status} successfully.");
    }

    public function deleteUser()
    {
        if ($this->userToDelete) {
            $user = User::findOrFail($this->userToDelete);

            // Soft delete by setting is_active to false
            $user->update(['is_active' => false]);

            session()->flash('message', 'User deactivated successfully.');
        }

        $this->cancelDelete();
    }

    /**
     * Open the Access modal to view user permissions
     */
    public function openAccessModal($userId)
    {
        $this->viewingUserId = $userId;
        $this->showAccessModal = true;
    }

    /**
     * Close the Access modal
     */
    public function closeAccessModal()
    {
        $this->showAccessModal = false;
        $this->viewingUserId = null;
    }

    /**
     * Get user access information for the modal
     */
    public function getUserAccessInfo(): array
    {
        if (!$this->viewingUserId) {
            return [];
        }

        $user = User::with(['roles', 'permissions', 'department', 'organization'])->findOrFail($this->viewingUserId);
        
        // Get all permissions (via roles and direct permissions)
        $allPermissions = $user->getAllPermissions();
        $rolePermissions = $user->getPermissionsViaRoles();
        $directPermissions = $user->getDirectPermissions();
        
        // Organize permissions by module
        $modules = config('modules.modules');
        $actionLabels = config('modules.action_labels');
        $organizedPermissions = [];
        
        foreach ($allPermissions as $permission) {
            [$module, $action] = explode('.', $permission->name, 2);
            
            if (!isset($organizedPermissions[$module])) {
                $organizedPermissions[$module] = [
                    'label' => $modules[$module]['label'] ?? ucfirst($module),
                    'icon' => $modules[$module]['icon'] ?? 'heroicon-o-cube',
                    'permissions' => []
                ];
            }
            
            $organizedPermissions[$module]['permissions'][] = [
                'name' => $permission->name,
                'action' => $action,
                'label' => $actionLabels[$action] ?? ucfirst($action),
                'via_role' => $rolePermissions->contains('name', $permission->name),
                'direct' => $directPermissions->contains('name', $permission->name)
            ];
        }

        return [
            'user' => $user,
            'roles' => $user->roles,
            'total_permissions' => $allPermissions->count(),
            'role_permissions_count' => $rolePermissions->count(),
            'direct_permissions_count' => $directPermissions->count(),
            'organized_permissions' => $organizedPermissions,
            'has_discrepancies' => $directPermissions->count() > 0
        ];
    }

    /**
     * Bulk action: Assign role to multiple users
     */
    public function bulkAssignRole($role, $userIds)
    {
        if (!is_array($userIds) || empty($userIds)) {
            session()->flash('error', 'No users selected.');
            return;
        }

        $users = User::whereIn('id', $userIds)->get();
        
        foreach ($users as $user) {
            $user->syncRoles([$role]);
        }

        session()->flash('message', "Role '{$role}' assigned to " . count($users) . " users.");
    }

    /**
     * Bulk action: Toggle user status
     */
    public function bulkToggleStatus($userIds, $status)
    {
        if (!is_array($userIds) || empty($userIds)) {
            session()->flash('error', 'No users selected.');
            return;
        }

        User::whereIn('id', $userIds)->update(['is_active' => $status]);
        
        $statusText = $status ? 'activated' : 'deactivated';
        session()->flash('message', count($userIds) . " users {$statusText}.");
    }

    public function render()
    {
        $query = User::query()
            ->with(['department', 'organization', 'roles'])
            ->whereHas('roles', function ($roleQuery) {
                $roleQuery->whereIn('name', ['admin', 'support']);
            })
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('username', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterRole, function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', $this->filterRole);
                });
            })
            ->when($this->filterDepartment, function ($q) {
                $q->where('department_id', $this->filterDepartment);
            })
            ->when($this->filterStatus, function ($q) {
                $q->where('is_active', false); // Show only inactive users when toggle is on
            });

        return view('livewire.admin.manage-users', [
            'users' => $query->withCount(['tickets', 'assignedTickets', 'permissions'])->latest()->paginate(15),
            'departments' => Department::orderBy('name')->get(),
            'availableRoles' => Role::whereIn('name', ['admin', 'support'])->orderBy('name')->pluck('name'),
            'userAccessInfo' => $this->showAccessModal ? $this->getUserAccessInfo() : [],
        ]);
    }
}
