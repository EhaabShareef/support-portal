<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Determine if the given role can be updated by the user.
     */
    public function update(User $user, Role $role): bool
    {
        // Only admins can update roles, but never allow updating the admin role
        return $user->hasRole('admin') && $role->name !== 'admin';
    }

    /**
     * Determine if the given role can be deleted by the user.
     */
    public function delete(User $user, Role $role): bool
    {
        // Only admins can delete roles, but never allow deleting the admin role
        return $user->hasRole('admin') && $role->name !== 'admin';
    }

    /**
     * Determine if the user can view any roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can view the role.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can create roles.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }
}