<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class ViewUser extends Component
{
    public User $user;

    public string $activeTab = 'details';

    public string $ticketSearch = '';

    public bool $editMode = false;

    public array $form = [];

    public array $permissions = [];

    public function mount(User $user)
    {
        // Check permissions - only admins can view user details
        if (! auth()->user()->hasRole('Super Admin') && ! auth()->user()->hasRole('Admin')) {
            abort(403, 'You do not have permission to view user details.');
        }

        $this->user = $user->load(['department', 'organization', 'roles', 'permissions']);
        $this->syncForm();
        $this->syncPermissions();
    }

    protected function syncForm(): void
    {
        $this->form = [
            'name' => $this->user->name,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'is_active' => $this->user->is_active,
            'department_id' => $this->user->department_id,
            'organization_id' => $this->user->organization_id,
        ];
    }

    protected function syncPermissions(): void
    {
        // Get permissions through roles (role-based permissions)
        $rolePermissions = $this->user->getAllPermissions()->pluck('name')->toArray();
        $allPermissions = Permission::all()->pluck('name')->toArray();

        $this->permissions = [];
        foreach ($allPermissions as $permission) {
            $this->permissions[$permission] = in_array($permission, $rolePermissions);
        }
    }

    #[Computed]
    public function canEdit()
    {
        return auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Admin');
    }

    #[Computed]
    public function ticketHistory()
    {
        if (! $this->user->hasRole('Client') || ! $this->user->organization) {
            return collect();
        }

        return $this->user->organization->tickets()
            ->with(['department', 'assigned', 'client'])
            ->when($this->ticketSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('subject', 'like', '%'.$this->ticketSearch.'%')
                        ->orWhere('ticket_number', 'like', '%'.$this->ticketSearch.'%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function groupedPermissions()
    {
        $grouped = [];
        foreach ($this->permissions as $permission => $hasPermission) {
            $parts = explode('.', $permission);
            $module = $parts[0] ?? 'general';
            $action = $parts[1] ?? $permission;

            if (! isset($grouped[$module])) {
                $grouped[$module] = [];
            }
            $grouped[$module][$action] = [
                'permission' => $permission,
                'has_permission' => $hasPermission,
            ];
        }

        return $grouped;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function toggleActive()
    {
        if (! $this->canEdit) {
            session()->flash('error', 'You do not have permission to change user status.');

            return;
        }

        $this->user->update([
            'is_active' => ! $this->user->is_active,
        ]);

        $this->user->refresh();
        $this->syncForm();

        $status = $this->user->is_active ? 'activated' : 'deactivated';
        session()->flash('message', "User {$status} successfully.");
    }

    public function enableEdit()
    {
        if (! $this->canEdit) {
            session()->flash('error', 'You do not have permission to edit this user.');

            return;
        }

        $this->editMode = true;
        $this->syncForm();
    }

    public function cancel()
    {
        $this->editMode = false;
        $this->syncForm();
    }

    public function save()
    {
        if (! $this->canEdit) {
            session()->flash('error', 'You do not have permission to edit this user.');

            return;
        }

        $validated = $this->validate([
            'form.name' => 'required|string|max:255',
            'form.username' => 'required|string|max:255|unique:users,username,'.$this->user->id,
            'form.email' => 'required|email|unique:users,email,'.$this->user->id,
            'form.is_active' => 'boolean',
            'form.department_id' => 'nullable|exists:departments,id',
            'form.organization_id' => 'nullable|exists:organizations,id',
        ]);

        $this->user->update($validated['form']);
        $this->user->refresh();

        $this->editMode = false;
        $this->syncForm();
        session()->flash('message', 'User updated successfully.');
    }

    public function updatePermission($permission, $granted)
    {
        // Disable direct permission editing - permissions should be managed through roles
        session()->flash('error', 'Permissions cannot be modified directly. Please manage permissions through roles.');
        return;
    }

    public function refreshUser()
    {
        $this->user->refresh();
        $this->user->load(['department', 'organization', 'roles', 'permissions']);
        $this->syncForm();
        $this->syncPermissions();
    }

    public function render()
    {
        return view('livewire.admin.view-user');
    }
}
