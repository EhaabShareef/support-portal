<?php

namespace App\Policies;

use App\Models\ScheduleEventType;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ScheduleEventTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('schedule-event-types.read');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ScheduleEventType $scheduleEventType): bool
    {
        return $user->can('schedule-event-types.read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only Super Admin and Admin can create schedule event types
        return $user->hasAnyRole(['Super Admin', 'Admin']) && $user->can('schedule-event-types.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ScheduleEventType $scheduleEventType): bool
    {
        // Only Super Admin and Admin can update schedule event types
        return $user->hasAnyRole(['Super Admin', 'Admin']) && $user->can('schedule-event-types.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScheduleEventType $scheduleEventType): bool
    {
        // Only Super Admin and Admin can delete schedule event types
        // Additional check: can't delete if schedules are using this event type
        if (!$user->hasAnyRole(['Super Admin', 'Admin']) || !$user->can('schedule-event-types.delete')) {
            return false;
        }

        // Check if any schedules are using this event type
        return !$scheduleEventType->schedules()->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ScheduleEventType $scheduleEventType): bool
    {
        // Only Super Admin can restore deleted schedule event types
        return $user->hasRole('Super Admin') && $user->can('schedule-event-types.delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ScheduleEventType $scheduleEventType): bool
    {
        // Only Super Admin can force delete schedule event types
        return $user->hasRole('Super Admin') && $user->can('schedule-event-types.delete');
    }
}