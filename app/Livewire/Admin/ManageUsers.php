<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\Organization;
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

    public $filterOrganization = '';

    public $filterStatus = '';

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
        'role' => 'Client', // Default to Client role
    ];

    // Confirmation properties
    public $confirmingUserDeletion = false;

    public $userToDelete = null;

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
        if ($this->form['role'] === 'Agent') {
            $rules['form.department_id'] = 'required|exists:departments,id';
        } elseif ($this->form['role'] === 'Client') {
            $rules['form.organization_id'] = 'required|exists:organizations,id';
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
        'form.department_id.required' => 'Department is required for Agents',
        'form.organization_id.required' => 'Organization is required for Clients',
    ];

    public function updating($field)
    {
        if (in_array($field, ['search', 'filterRole', 'filterDepartment', 'filterOrganization', 'filterStatus'])) {
            $this->resetPage();
        }

        // Clear department/organization when role changes
        if ($field === 'form.role') {
            if ($this->form['role'] !== 'Agent') {
                $this->form['department_id'] = '';
            }
            if ($this->form['role'] !== 'Client') {
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
            'role' => 'Client', // Default to Client role
        ];
        $this->userId = null;
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
        if ($this->form['role'] === 'Agent') {
            $userData['department_id'] = $this->form['department_id'];
            $userData['organization_id'] = null;
        } elseif ($this->form['role'] === 'Client') {
            $userData['organization_id'] = $this->form['organization_id'];
            $userData['department_id'] = null;
        } else { // Admin
            $userData['department_id'] = null;
            $userData['organization_id'] = null;
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
        if ($this->form['role'] === 'Agent') {
            $updateData['department_id'] = $this->form['department_id'];
            $updateData['organization_id'] = null;
        } elseif ($this->form['role'] === 'Client') {
            $updateData['organization_id'] = $this->form['organization_id'];
            $updateData['department_id'] = null;
        } else { // Admin
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

    public function render()
    {
        $query = User::query()
            ->with(['department', 'organization', 'roles'])
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
            ->when($this->filterOrganization, function ($q) {
                $q->where('organization_id', $this->filterOrganization);
            })
            ->when($this->filterStatus !== '', function ($q) {
                $q->where('is_active', (bool) $this->filterStatus);
            });

        return view('livewire.admin.manage-users', [
            'users' => $query->withCount(['tickets', 'assignedTickets', 'permissions'])->latest()->paginate(15),
            'departments' => Department::orderBy('name')->get(),
            'organizations' => Organization::orderBy('name')->get(),
            'availableRoles' => Role::orderBy('name')->pluck('name'),
        ]);
    }
}
