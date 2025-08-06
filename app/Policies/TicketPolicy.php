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
        return $user->hasAnyRole(['Admin', 'Agent', 'Client']);
    }

    /**
     * Determine whether the user can view the ticket.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Admin can view all tickets
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Agent can view tickets in their department or department group
        if ($user->hasRole('Agent')) {
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
        if ($user->hasRole('Client') && $user->organization_id === $ticket->organization_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create tickets.
     */
    public function create(User $user): bool
    {
        // Admin and Client can create tickets
        return $user->hasAnyRole(['Admin', 'Client']);
    }

    /**
     * Determine whether the user can update the ticket.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Admin can update any ticket
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Agent can update tickets in their department or department group
        if ($user->hasRole('Agent')) {
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
        // Only Admin can delete tickets
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can assign tickets.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        // Admin can assign any ticket
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Agent can assign tickets in their department or department group
        if ($user->hasRole('Agent')) {
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