<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SchedulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('schedules.read');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Schedule $schedule): bool
    {
        // Super Admin and Admin can view all schedules
        if ($user->hasAnyRole(['Super Admin', 'Admin'])) {
            return $user->can('schedules.read');
        }

        // Client users can only view schedules for users in their organization
        if ($user->hasRole('Client')) {
            // Check if the schedule belongs to a user in the same organization
            return $user->can('schedules.read') && 
                   $schedule->user->organization_id === $user->organization_id;
        }

        // Agent users can view schedules for users in their department
        if ($user->hasRole('Agent')) {
            return $user->can('schedules.read') && 
                   $schedule->user->department_id === $user->department_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only Super Admin and Admin can create schedules
        return $user->hasAnyRole(['Super Admin', 'Admin']) && $user->can('schedules.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Schedule $schedule): bool
    {
        // Only Super Admin and Admin can update schedules
        return $user->hasAnyRole(['Super Admin', 'Admin']) && $user->can('schedules.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Schedule $schedule): bool
    {
        // Only Super Admin and Admin can delete schedules
        return $user->hasAnyRole(['Super Admin', 'Admin']) && $user->can('schedules.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Schedule $schedule): bool
    {
        // Only Super Admin can restore deleted schedules
        return $user->hasRole('Super Admin') && $user->can('schedules.delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Schedule $schedule): bool
    {
        // Only Super Admin can force delete schedules
        return $user->hasRole('Super Admin') && $user->can('schedules.delete');
    }
}