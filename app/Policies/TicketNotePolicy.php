<?php

namespace App\Policies;

use App\Models\TicketNote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketNotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'support']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TicketNote $ticketNote): bool
    {
        // Admin can view any note
        if ($user->hasRole('admin')) {
            return true;
        }

        // Support can view notes on tickets they can access
        if ($user->hasRole('support')) {
            $ticket = $ticketNote->ticket;
            // Check if same department
            if ($user->department_id === $ticket->department_id) {
                return true;
            }
            // Check if same department group
            if ($user->department?->department_group_id && 
                $user->department->department_group_id === $ticket->department?->department_group_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'support']);
    }

    /**
     * Determine whether the user can create notes for a specific ticket.
     */
    public function createForTicket(User $user, $ticket): bool
    {
        // First check if user can create notes in general
        if (!$this->create($user)) {
            return false;
        }

        // Admin can create notes for any ticket
        if ($user->hasRole('admin')) {
            return true;
        }

        // Support can create notes for tickets in their department or department group
        if ($user->hasRole('support')) {
            // Check if same department
            if ($user->department_id === $ticket->department_id) {
                return true;
            }
            // Check if same department group
            if ($user->department?->department_group_id && 
                $user->department->department_group_id === $ticket->department?->department_group_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TicketNote $ticketNote): bool
    {
        // Admin can edit any note
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can edit their own notes
        if ($ticketNote->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TicketNote $ticketNote): bool
    {
        // Admin can delete any note
        if ($user->hasRole('admin')) {
            return true;
        }

        // Users can delete their own notes
        if ($ticketNote->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TicketNote $ticketNote): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TicketNote $ticketNote): bool
    {
        return $user->hasRole('admin');
    }
}
