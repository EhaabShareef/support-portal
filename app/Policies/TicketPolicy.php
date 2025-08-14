<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Ticket;
use App\Contracts\SettingsRepositoryInterface;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
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

        // Clients can view tickets from their organization but with restrictions
        if ($user->hasRole('client') && $user->organization_id === $ticket->organization_id) {
            // Check reopen window for closed tickets
            if ($ticket->status === 'closed') {
                $reopenLimit = app(SettingsRepositoryInterface::class)->get('tickets.reopen_window_days', 3);
                $isWithinWindow = $ticket->closed_at && now()->diffInDays($ticket->closed_at) <= $reopenLimit;
                return $isWithinWindow;
            }
            return true;
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

    /**
     * Determine whether the user can escalate the priority of a ticket.
     */
    public function escalatePriority(User $user, Ticket $ticket, string $newPriority): bool
    {
        // Clients cannot escalate priority above current level
        if ($user->hasRole('client')) {
            return TicketPriority::compare($newPriority, $ticket->priority) <= 0;
        }

        // Admin and support can escalate priorities
        return $user->hasRole(['admin', 'support']) && $this->update($user, $ticket);
    }

    /**
     * Determine whether the user can reopen a closed ticket.
     */
    public function reopen(User $user, Ticket $ticket): bool
    {
        // Only applies to closed tickets
        if ($ticket->status !== 'closed') {
            return false;
        }

        // Admin and support can always reopen
        if ($user->hasRole(['admin', 'support'])) {
            return $this->update($user, $ticket);
        }

        // Clients can only reopen within the window
        if ($user->hasRole('client') && $user->organization_id === $ticket->organization_id) {
            $reopenLimit = app(SettingsRepositoryInterface::class)->get('tickets.reopen_window_days', 3);
            return $ticket->closed_at && now()->diffInDays($ticket->closed_at) <= $reopenLimit;
        }

        return false;
    }

    /**
     * Determine whether the user can set a specific status for a ticket.
     */
    public function setStatus(User $user, Ticket $ticket, string $status): bool
    {
        // First check if user can update the ticket
        if (!$this->update($user, $ticket)) {
            return false;
        }

        // Admin can set any status
        if ($user->hasRole('admin')) {
            return true;
        }

        // Support staff can only set statuses allowed for their department group
        if ($user->hasRole('support') && $user->department?->department_group_id) {
            $allowedStatuses = array_keys(TicketStatus::optionsForDepartmentGroup($user->department->department_group_id));
            return in_array($status, $allowedStatuses);
        }

        // Clients and users without department groups can use default statuses
        return in_array($status, array_keys(TicketStatus::options()));
    }
}