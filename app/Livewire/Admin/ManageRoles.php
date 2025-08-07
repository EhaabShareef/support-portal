<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class ManageRoles extends Component
{
    use WithPagination;

    public $search = '';
    
    // Modal and form properties
    public $showModal = false;
    public $editMode = false;
    public $roleId = null;
    
    public $form = [
        'name' => '',
        'guard_name' => 'web',
        'description' => '',
    ];
    
    // Permission management
    public $selectedPermissions = [];
    
    // Confirmation properties
    public $confirmingRoleDeletion = false;
    public $roleToDelete = null;

    protected function rules()
    {
        $rules = [
            'form.name' => 'required|string|max:255|unique:roles,name',
            'form.guard_name' => 'required|string',
            'form.description' => 'nullable|string|max:500',
        ];

        // For edit mode, adjust unique rules
        if ($this->editMode && $this->roleId) {
            $rules['form.name'] = 'required|string|max:255|unique:roles,name,' . $this->roleId;
        }

        return $rules;
    }

    protected $messages = [
        'form.name.required' => 'Role name is required',
        'form.name.unique' => 'Role name already exists',
        'form.guard_name.required' => 'Guard name is required',
    ];

    public function updating($field)
    {
        if ($field === 'search') {
            $this->resetPage();
        }
    }

    public function mount()
    {
        // Check permission
        if (!auth()->user()->can('users.edit') && !auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to manage roles.');
        }
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
        $this->selectedPermissions = [];
    }

    public function openEditModal($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        
        $this->roleId = $role->id;
        $this->form = [
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'description' => $role->description ?? '',
        ];
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->editMode = true;
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
            'guard_name' => 'web',
            'description' => '',
        ];
        $this->roleId = null;
        $this->selectedPermissions = [];
    }

    public function saveRole()
    {
        // Prevent modification of admin role
        if ($this->editMode && $this->form['name'] === 'admin') {
            session()->flash('error', 'Admin role cannot be modified.');
            return;
        }

        $this->validate();

        try {
            DB::beginTransaction();

            if ($this->editMode) {
                $role = Role::findOrFail($this->roleId);
                $role->update([
                    'name' => $this->form['name'],
                    'description' => $this->form['description'],
                ]);
            } else {
                $role = Role::create([
                    'name' => $this->form['name'],
                    'guard_name' => $this->form['guard_name'],
                    'description' => $this->form['description'],
                ]);
            }

            // Sync permissions
            $permissions = Permission::whereIn('name', $this->selectedPermissions)->get();
            $role->syncPermissions($permissions);

            DB::commit();

            session()->flash('message', $this->editMode ? 'Role updated successfully.' : 'Role created successfully.');
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function confirmDelete($roleId)
    {
        $role = Role::findOrFail($roleId);
        
        // Prevent deletion of system roles
        if (in_array($role->name, ['admin', 'support', 'client'])) {
            session()->flash('error', 'System roles cannot be deleted.');
            return;
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            session()->flash('error', 'Cannot delete role with assigned users. Please reassign users first.');
            return;
        }

        $this->roleToDelete = $role;
        $this->confirmingRoleDeletion = true;
    }

    public function deleteRole()
    {
        if ($this->roleToDelete) {
            $this->roleToDelete->delete();
            session()->flash('message', 'Role deleted successfully.');
        }

        $this->confirmingRoleDeletion = false;
        $this->roleToDelete = null;
    }

    public function cancelDelete()
    {
        $this->confirmingRoleDeletion = false;
        $this->roleToDelete = null;
    }

    public function togglePermission($permissionName)
    {
        if (in_array($permissionName, $this->selectedPermissions)) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, [$permissionName]);
        } else {
            $this->selectedPermissions[] = $permissionName;
        }
    }

    public function toggleAllPermissionsForModule($module)
    {
        $modulePermissions = Permission::where('name', 'like', $module . '.%')->pluck('name')->toArray();
        
        $allSelected = count(array_intersect($modulePermissions, $this->selectedPermissions)) === count($modulePermissions);
        
        if ($allSelected) {
            // Remove all module permissions
            $this->selectedPermissions = array_diff($this->selectedPermissions, $modulePermissions);
        } else {
            // Add all module permissions
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $modulePermissions));
        }
    }

    /**
     * Toggle all permissions for a specific action across all modules
     */
    public function toggleAllPermissionsForAction($action)
    {
        $actionPermissions = Permission::where('name', 'like', '%.'. $action)->pluck('name')->toArray();
        
        $allSelected = count(array_intersect($actionPermissions, $this->selectedPermissions)) === count($actionPermissions);
        
        if ($allSelected) {
            // Remove all action permissions
            $this->selectedPermissions = array_diff($this->selectedPermissions, $actionPermissions);
        } else {
            // Add all action permissions
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $actionPermissions));
        }
    }

    /**
     * Check if all permissions for a module are selected
     */
    public function isModuleFullySelected($module): bool
    {
        $modulePermissions = Permission::where('name', 'like', $module . '.%')->pluck('name')->toArray();
        return count(array_intersect($modulePermissions, $this->selectedPermissions)) === count($modulePermissions);
    }

    /**
     * Check if all permissions for an action are selected
     */
    public function isActionFullySelected($action): bool
    {
        $actionPermissions = Permission::where('name', 'like', '%.'. $action)->pluck('name')->toArray();
        return count(array_intersect($actionPermissions, $this->selectedPermissions)) === count($actionPermissions);
    }

    /**
     * Check if a specific permission is selected
     */
    public function isPermissionSelected($module, $action): bool
    {
        return in_array("{$module}.{$action}", $this->selectedPermissions);
    }

    /**
     * Get permission matrix data organized for UI display
     */
    public function getPermissionMatrix(): array
    {
        $modules = config('modules.modules');
        $groups = config('modules.groups');
        $actionLabels = config('modules.action_labels');
        
        $matrix = [];
        
        foreach ($groups as $groupKey => $group) {
            $matrix[$groupKey] = [
                'label' => $group['label'],
                'description' => $group['description'],
                'icon' => $group['icon'],
                'modules' => []
            ];
            
            foreach ($group['modules'] as $moduleKey) {
                if (isset($modules[$moduleKey])) {
                    $module = $modules[$moduleKey];
                    $matrix[$groupKey]['modules'][$moduleKey] = [
                        'label' => $module['label'],
                        'description' => $module['description'],
                        'icon' => $module['icon'],
                        'actions' => []
                    ];
                    
                    foreach ($module['actions'] as $action) {
                        $matrix[$groupKey]['modules'][$moduleKey]['actions'][$action] = [
                            'label' => $actionLabels[$action] ?? ucfirst($action),
                            'permission' => "{$moduleKey}.{$action}",
                            'selected' => $this->isPermissionSelected($moduleKey, $action)
                        ];
                    }
                }
            }
        }
        
        return $matrix;
    }

    /**
     * Get all unique actions across all modules
     */
    public function getAllActions(): array
    {
        $modules = config('modules.modules');
        $actions = [];
        
        foreach ($modules as $module) {
            $actions = array_merge($actions, $module['actions']);
        }
        
        return array_unique($actions);
    }

    public function render()
    {
        $query = Role::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        }

        $roles = $query->withCount('users')
                      ->orderBy('name')
                      ->paginate(10);

        return view('livewire.admin.manage-roles', [
            'roles' => $roles,
            'permissionMatrix' => $this->getPermissionMatrix(),
            'allActions' => $this->getAllActions(),
            'actionLabels' => config('modules.action_labels'),
        ]);
    }
}