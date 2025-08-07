<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any tickets.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('tickets.read');
    }

    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // First check if user has basic read permission
        if (!$user->can('tickets.read')) {
            return false;
        }

        // Admin can view all tickets
        if ($user->hasRole('admin')) {
            return true;
        }

        // Support can view tickets in their department or department group
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

        // Client can view tickets from their organization
        if ($user->hasRole('client') && $user->organization_id === $ticket->organization_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create tickets.
     */
    public function create(User $user): bool
    {
        return $user->can('tickets.create');
    }

    /**
     * Determine whether the user can update the ticket.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // First check if user has update permission
        if (!$user->can('tickets.update')) {
            return false;
        }

        // Admin can update any ticket
        if ($user->hasRole('admin')) {
            return true;
        }

        // Support can update tickets in their department or department group
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
     * Determine whether the user can delete the ticket.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->can('tickets.delete');
    }

    /**
     * Determine whether the user can assign tickets.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        // First check if user has assign permission
        if (!$user->can('tickets.assign')) {
            return false;
        }

        // Admin can assign any ticket
        if ($user->hasRole('admin')) {
            return true;
        }

        // Support can assign tickets in their department or department group
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
}