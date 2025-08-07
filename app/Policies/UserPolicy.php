<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Admin can view all users
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Agents can view users in their department
        if ($user->hasRole('support') && $user->department_id && $user->department_id === $model->department_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Admin can update any user
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can update their own profile (limited fields)
        if ($user->id === $model->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Only Admin can delete users
        // Cannot delete yourself
        return $user->hasRole('admin') && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can manage roles.
     */
    public function manageRoles(User $user): bool
    {
        return $user->hasRole('admin');
    }
}