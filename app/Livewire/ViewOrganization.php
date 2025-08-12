<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\OrganizationContract;
use App\Models\OrganizationHardware;
use App\Models\User;
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
        if ($user->hasRole('client') && $organization->id !== $user->organization_id) {
            abort(403, 'You can only view your own organization.');
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
        return $user->hasRole('client') && $this->organization->id === $user->organization_id;
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
            'email' => $this->organization->email,
            'phone' => $this->organization->phone,
            'is_active' => $this->organization->is_active,
            'subscription_status' => $this->organization->subscription_status,
            'notes' => $this->organization->notes,
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
        $this->confirmingDelete = false;
        $this->syncForm();
    }

    public function save(): void
    {
        if (! $this->canEdit) {
            session()->flash('error', 'You do not have permission to edit this organization.');

            return;
        }

        $rules = $this->getOrganizationValidationRulesWithExclusion($this->organization->id);
        $validated = $this->validate($rules, $this->getOrganizationValidationMessages());

        $this->organization->update($validated['form']);
        $this->organization->refresh();

        $this->editMode = false;
        $this->syncForm();
        session()->flash('message', 'Organization updated successfully.');
    }

    public function confirmDelete()
    {
        if (! $this->canDelete) {
            session()->flash('error', 'You do not have permission to delete this organization.');

            return;
        }

        if (! $this->organization->canBeDeleted()) {
            session()->flash('error', 'Cannot delete organization with associated users, tickets, or contracts.');

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

        if (! $this->organization->canBeDeleted()) {
            session()->flash('error', 'Cannot delete organization with associated users, tickets, or contracts.');

            return;
        }

        $this->organization->delete();

        return redirect()->route('organizations.index')->with('message', 'Organization deleted successfully.');
    }

    public function toggleActive()
    {
        if (! auth()->user()->hasRole('admin') && ! auth()->user()->can('organizations.update')) {
            session()->flash('error', 'You do not have permission to change organization status.');

            return;
        }

        $this->organization->update([
            'is_active' => ! $this->organization->is_active,
        ]);

        $this->organization->refresh();
        $this->syncForm();

        $status = $this->organization->is_active ? 'activated' : 'deactivated';
        session()->flash('message', "Organization {$status} successfully.");
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    #[On('refreshOrganization')]
    public function refreshOrganization()
    {
        $this->organization->refresh();
        $this->organization->load(['users.roles', 'contracts', 'hardware.contract', 'tickets.client', 'tickets.owner', 'tickets.department']);
    }

    public function deleteContract($contractId)
    {
        $contract = OrganizationContract::findOrFail($contractId);
        
        // Check if contract has hardware assigned
        if ($contract->hardware()->count() > 0) {
            session()->flash('error', 'Cannot delete contract with assigned hardware. Please remove hardware first.');
            return;
        }

        $contract->delete();
        $this->refreshOrganization();
        session()->flash('message', 'Contract deleted successfully.');
    }

    public function deleteHardware($hardwareId)
    {
        $hardware = OrganizationHardware::findOrFail($hardwareId);
        $hardware->delete();
        $this->refreshOrganization();
        session()->flash('message', 'Hardware deleted successfully.');
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        
        // Only allow deletion of client users from this organization
        if (!$user->hasRole('client') || $user->organization_id !== $this->organization->id) {
            session()->flash('error', 'You can only delete client users belonging to this organization.');
            return;
        }

        // Check if user has any tickets
        if ($user->tickets()->count() > 0 || $user->assignedTickets()->count() > 0) {
            session()->flash('error', 'Cannot delete user with existing tickets. Please reassign or resolve tickets first.');
            return;
        }

        $userName = $user->name;
        $user->delete();
        $this->refreshOrganization();
        session()->flash('message', "User '{$userName}' deleted successfully.");
    }

    #[Computed]
    public function filteredTickets()
    {
        return $this->organization->tickets()
            ->with(['client', 'department', 'owner'])
            ->whereNotIn('status', ['closed', 'solution_provided']) // Hide closed tickets by default
            ->when($this->ticketSearch, function ($query) {
                $query->where(function ($q) {
                    $q->where('subject', 'like', '%'.$this->ticketSearch.'%')
                        ->orWhere('ticket_number', 'like', '%'.$this->ticketSearch.'%')
                        ->orWhereHas('client', function ($clientQuery) {
                            $clientQuery->where('name', 'like', '%'.$this->ticketSearch.'%');
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.view-organization');
    }
}
