<?php

namespace App\Livewire\Organization;

use App\Models\Organization;
use App\Models\OrganizationContract;
use App\Models\OrganizationHardware;
use App\Models\User;
use App\Models\OrganizationUser;
use App\Traits\ValidatesOrganizations;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewOrganization extends Component
{
    use ValidatesOrganizations;
    
    public Organization $organization;

    public bool $editMode = false;

    public bool $confirmingDelete = false;

    public string $activeTab = 'users';

    public string $ticketSearch = '';

    public array $form = [];

    public function mount(Organization $organization)
    {
        // Check permissions
        $user = auth()->user();
        if (! $user->can('organizations.read')) {
            abort(403, 'You do not have permission to view organizations.');
        }

        // Clients can only view their own organization
        if ($user->hasRole('client')) {
            $userOrgIds = $user->organizations->pluck('id');
            if (!$userOrgIds->contains($organization->id)) {
                abort(403, 'You can only view your own organization.');
            }
        }

        $this->organization = $organization->load(['users.roles', 'contracts', 'hardware.contract', 'tickets.client', 'tickets.owner', 'tickets.department']);
        $this->syncForm();
    }

    public function pageRefresh(): void
    {
        $this->organization->refresh();
        $this->syncForm();
    }

    #[Computed]
    public function canEdit()
    {
        $user = auth()->user();
        if ($user->hasRole('admin') || $user->can('organizations.update')) {
            return true;
        }

        // Clients can edit their own organization
        $userOrgIds = $user->organizations->pluck('id');
        return $user->hasRole('client') && $userOrgIds->contains($this->organization->id);
    }

    #[Computed]
    public function canDelete()
    {
        return auth()->user()->hasRole('admin') || auth()->user()->can('organizations.delete');
    }

    protected function syncForm(): void
    {
        $this->form = [
            'name' => $this->organization->name,
            'company' => $this->organization->company,
            'company_contact' => $this->organization->company_contact,
            'tin_no' => $this->organization->tin_no,
            'is_active' => $this->organization->is_active,
            'subscription_status' => $this->organization->subscription_status,
            'notes' => $this->organization->notes,
            'primary_user_id' => $this->organization->primary_user_id,
        ];
    }

    public function enableEdit(): void
    {
        if (! $this->canEdit) {
            session()->flash('error', 'You do not have permission to edit this organization.');

            return;
        }

        $this->editMode = true;
        $this->syncForm();
    }

    public function cancel(): void
    {
        $this->editMode = false;
        $this->syncForm();
    }

    public function save(): void
    {
        if (! $this->canEdit) {
            session()->flash('error', 'You do not have permission to edit this organization.');

            return;
        }

        $this->validate([
            'form.name' => 'required|string|max:255',
            'form.company' => 'nullable|string|max:255',
            'form.company_contact' => 'nullable|string|max:255',
            'form.tin_no' => 'nullable|string|max:50',
            'form.is_active' => 'boolean',
            'form.subscription_status' => 'required|in:trial,active,suspended,cancelled',
            'form.notes' => 'nullable|string|max:1000',
            'form.primary_user_id' => 'nullable|exists:users,id',
        ]);

        // Handle primary user assignment
        $primaryUserId = $this->form['primary_user_id'] ?? null;
        unset($this->form['primary_user_id']); // Remove from organization data

        $this->organization->update($this->form);

        // Set primary user if specified
        if ($primaryUserId !== null) {
            try {
                if ($primaryUserId === '') {
                    // Clear primary user
                    $this->organization->update(['primary_user_id' => null]);
                    // Also clear from pivot table
                    $this->organization->users()->updateExistingPivot($this->organization->users()->wherePivot('is_primary', true)->pluck('users.id'), ['is_primary' => false]);
                } else {
                    // Set new primary user
                    \App\Models\OrganizationUser::setPrimaryUser($primaryUserId, $this->organization->id);
                }
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to update primary user: ' . $e->getMessage());
                return;
            }
        }

        session()->flash('message', 'Organization updated successfully.');
        $this->editMode = false;
        $this->organization->refresh();
    }

    public function confirmDelete(): void
    {
        if (! $this->canDelete) {
            session()->flash('error', 'You do not have permission to delete this organization.');

            return;
        }

        $this->confirmingDelete = true;
    }

    public function delete()
    {
        if (! $this->canDelete) {
            session()->flash('error', 'You do not have permission to delete this organization.');

            return;
        }

        if ($this->organization->canBeDeleted()) {
            $this->organization->delete();
            session()->flash('message', 'Organization deleted successfully.');

            return redirect()->route('organizations.index');
        }

        session()->flash('error', 'This organization cannot be deleted because it has associated records.');
        $this->confirmingDelete = false;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
    }

    public function setPrimaryUser(int $userId): void
    {
        if (!$this->canEdit) {
            session()->flash('error', 'You do not have permission to edit this organization.');
            return;
        }

        // Check if the user belongs to this organization
        $user = User::findOrFail($userId);
        if (!$user->organizations->contains($this->organization->id)) {
            session()->flash('error', 'This user does not belong to this organization.');
            return;
        }

        // Check if the user has client role
        if (!$user->hasRole('client')) {
            session()->flash('error', 'Only client users can be set as primary users.');
            return;
        }

        try {
            // Use the OrganizationUser model to set the primary user
            OrganizationUser::setPrimaryUser($userId, $this->organization->id);
            
            // Refresh the organization to get updated data
            $this->organization->refresh();
            
            session()->flash('message', 'Primary user updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update primary user: ' . $e->getMessage());
        }
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function getFilteredTickets()
    {
        $query = $this->organization->tickets()
            ->with(['client', 'owner', 'department', 'status', 'priority'])
            ->orderBy('created_at', 'desc');

        if ($this->ticketSearch) {
            $query->where(function ($q) {
                $q->where('subject', 'like', "%{$this->ticketSearch}%")
                  ->orWhere('ticket_number', 'like', "%{$this->ticketSearch}%");
            });
        }

        return $query->get();
    }

    public function render()
    {
        $tickets = $this->getFilteredTickets();
        
        return view('livewire.organization.view-organization', compact('tickets'));
    }
}
