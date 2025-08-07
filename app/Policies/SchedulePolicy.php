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
        // Admin can view all schedules
        if ($user->hasRole('admin')) {
            return $user->can('schedules.read');
        }

        // Client users can only view schedules for users in their organization
        if ($user->hasRole('client')) {
            // Check if the schedule belongs to a user in the same organization
            return $user->can('schedules.read') && 
                   $schedule->user->organization_id === $user->organization_id;
        }

        // Support users can view schedules for users in their department
        if ($user->hasRole('support')) {
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
        // Only Admin can create schedules
        return $user->hasRole('admin') && $user->can('schedules.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Schedule $schedule): bool
    {
        // Only Admin can update schedules
        return $user->hasRole('admin') && $user->can('schedules.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Schedule $schedule): bool
    {
        // Only Admin can delete schedules
        return $user->hasRole('admin') && $user->can('schedules.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Schedule $schedule): bool
    {
        // Only Admin can restore deleted schedules
        return $user->hasRole('admin') && $user->can('schedules.delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Schedule $schedule): bool
    {
        // Only Admin can force delete schedules
        return $user->hasRole('admin') && $user->can('schedules.delete');
    }
}