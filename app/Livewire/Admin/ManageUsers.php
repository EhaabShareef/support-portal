<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\DepartmentGroup;
use App\Models\Organization;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ManageUsers extends Component
{
    use WithPagination;

    public $search = '';
    public $filterRole = '';
    public $filterDepartmentGroup = '';
    public $filterOrganization = '';
    public $showCreateForm = false;
    public $showEditForm = false;
    public $editingUser = null;

    // Form fields
    public $name = '';
    public $username = '';
    public $email = '';
    public $password = '';
    public $organization_id = '';
    public $department_group_id = '';
    public $roles = [];
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:users,username',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
        'organization_id' => 'nullable|exists:organizations,id',
        'department_group_id' => 'nullable|exists:department_groups,id',
        'roles' => 'array',
        'is_active' => 'boolean',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->username = '';
        $this->email = '';
        $this->password = '';
        $this->organization_id = '';
        $this->department_group_id = '';
        $this->roles = [];
        $this->is_active = true;
        $this->editingUser = null;
    }

    public function createUser()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'organization_id' => $this->organization_id ?: null,
            'department_group_id' => $this->department_group_id ?: null,
            'is_active' => $this->is_active,
            'email_verified_at' => now(),
        ]);

        // Assign roles
        if (!empty($this->roles)) {
            $user->assignRole($this->roles);
        }

        $this->resetForm();
        $this->showCreateForm = false;
        session()->flash('message', 'User created successfully.');
    }

    public function editUser($userId)
    {
        $this->editingUser = User::findOrFail($userId);
        
        $this->name = $this->editingUser->name;
        $this->username = $this->editingUser->username;
        $this->email = $this->editingUser->email;
        $this->organization_id = $this->editingUser->organization_id;
        $this->department_group_id = $this->editingUser->department_group_id;
        $this->roles = $this->editingUser->roles->pluck('name')->toArray();
        $this->is_active = $this->editingUser->is_active;
        
        $this->showEditForm = true;
    }

    public function updateUser()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($this->editingUser->id)],
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->editingUser->id)],
            'password' => 'nullable|min:8',
            'organization_id' => 'nullable|exists:organizations,id',
            'department_group_id' => 'nullable|exists:department_groups,id',
            'roles' => 'array',
            'is_active' => 'boolean',
        ]);

        $updateData = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'organization_id' => $this->organization_id ?: null,
            'department_group_id' => $this->department_group_id ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $updateData['password'] = Hash::make($this->password);
        }

        $this->editingUser->update($updateData);

        // Update roles
        $this->editingUser->syncRoles($this->roles);

        $this->resetForm();
        $this->showEditForm = false;
        session()->flash('message', 'User updated successfully.');
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();
        session()->flash('message', 'User deleted successfully.');
    }

    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => !$user->is_active]);
        session()->flash('message', 'User status updated successfully.');
    }

    public function render()
    {
        $query = User::query()
            ->with(['organization', 'departmentGroup', 'roles'])
            ->orderBy('name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('username', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterRole) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->filterRole);
            });
        }

        if ($this->filterDepartmentGroup) {
            $query->where('department_group_id', $this->filterDepartmentGroup);
        }

        if ($this->filterOrganization) {
            $query->where('organization_id', $this->filterOrganization);
        }

        $users = $query->paginate(15);

        return view('livewire.admin.manage-users', [
            'users' => $users,
            'roles' => Role::all(),
            'departmentGroups' => DepartmentGroup::where('is_active', true)->orderBy('name')->get(),
            'organizations' => Organization::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
