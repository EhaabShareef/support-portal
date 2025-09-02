<?php

namespace App\Livewire\Organization;

use App\Models\Organization;
use App\Traits\ValidatesOrganizations;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class ManageOrganizations extends Component
{
    use WithPagination, ValidatesOrganizations;

    public bool $showForm = false;
    public ?int $deleteId = null;
    public array $form = [
        'id' => '',
        'name' => '',
        'company' => '',
        'company_contact' => '',
        'tin_no' => '',
        'is_active' => true,
        'subscription_status' => 'trial',
        'notes' => '',
        'primary_user_id' => '',
    ];

    public string $search = '';
    public string $statusFilter = 'all';
    public string $subscriptionFilter = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'subscriptionFilter' => ['except' => 'all'],
    ];

    public function mount()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSubscriptionFilter()
    {
        $this->resetPage();
    }

    public function getCanCreateProperty()
    {
        $user = auth()->user();
        // Admin role should always have access
        if ($user->hasRole('admin')) {
            return true;
        }
        // Check specific permission
        return $user->can('organizations.create');
    }

    public function getCanEditProperty()
    {
        $user = auth()->user();
        // Admin role should always have access
        if ($user->hasRole('admin')) {
            return true;
        }
        // Check specific permission
        return $user->can('organizations.update');
    }

    public function getCanDeleteProperty()
    {
        $user = auth()->user();
        // Admin role should always have access
        if ($user->hasRole('admin')) {
            return true;
        }
        // Check specific permission
        return $user->can('organizations.delete');
    }

    public function render()
    {
        $query = Organization::query()
            ->with(['primaryUser', 'users'])
            ->withCount(['users', 'contracts', 'hardware', 'tickets'])
            ->where(function ($query) {
                if ($this->search) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('company', 'like', "%{$this->search}%")
                        ->orWhere('company_contact', 'like', "%{$this->search}%");
                }
            });

        // Apply filters
        if ($this->statusFilter !== 'all') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        if ($this->subscriptionFilter !== 'all') {
            $query->where('subscription_status', $this->subscriptionFilter);
        }

        // Role-based filtering - Updated for new relationship structure
        $user = auth()->user();
        if ($user->hasRole('client')) {
            // Get organizations where user is a member
            $userOrgIds = $user->organizations->pluck('id');
            $query->whereIn('id', $userOrgIds);
        }

        $organizations = $query->orderBy('name')->paginate(10);

        return view('livewire.organization.manage-organizations', compact('organizations'));
    }

    public function create()
    {
        if (!$this->canCreate) {
            session()->flash('error', 'You do not have permission to create organizations.');
            return;
        }

        $this->reset('form');
        $this->form['is_active'] = true;
        $this->form['subscription_status'] = 'trial';
        $this->form['primary_user_id'] = '';
        $this->showForm = true;
    }

    public function edit($id) 
    {
        if (!$this->canEdit) {
            session()->flash('error', 'You do not have permission to edit organizations.');
            return;
        }

        $organization = Organization::findOrFail($id);
        
        // Check if client can only edit their own organization
        if (auth()->user()->hasRole('client')) {
            $userOrgIds = auth()->user()->organizations->pluck('id');
            if (!$userOrgIds->contains($organization->id)) {
                session()->flash('error', 'You can only edit your own organization.');
                return;
            }
        }

        $this->form = $organization->toArray();
        $this->form['primary_user_id'] = $organization->primary_user_id;
        $this->showForm = true;
    }

    public function save()
    {
        // Get validation rules with exclusion if editing
        $rules = $this->getOrganizationValidationRulesWithExclusion($this->form['id'] ?? null);

        $this->validate($rules, $this->getOrganizationValidationMessages());

        $data = $this->form;
        foreach (['company', 'tin_no'] as $nullableField) {
            if ($data[$nullableField] === '') {
                $data[$nullableField] = null;
            }
        }

        // Handle primary user assignment
        $primaryUserId = $data['primary_user_id'] ?? null;
        unset($data['primary_user_id']); // Remove from organization data

        $organization = Organization::updateOrCreate(
            ['id' => $data['id']],
            $data
        );

        // Set primary user if specified
        if ($primaryUserId && $organization->id) {
            try {
                \App\Models\OrganizationUser::setPrimaryUser($primaryUserId, $organization->id);
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to set primary user: ' . $e->getMessage());
                return;
            }
        }

        session()->flash('message', $this->form['id'] ? 'Organization updated successfully.' : 'Organization created successfully.');
        $this->showForm = false;
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        if (!$this->canDelete) {
            session()->flash('error', 'You do not have permission to delete organizations.');
            return;
        }

        $organization = Organization::findOrFail($id);
        
        if (!$organization->canBeDeleted()) {
            session()->flash('error', 'This organization cannot be deleted because it has associated records.');
            return;
        }

        $this->deleteId = $id;
    }

    public function delete()
    {
        if (!$this->deleteId || !$this->canDelete) {
            session()->flash('error', 'You do not have permission to delete organizations.');
            return;
        }

        $organization = Organization::findOrFail($this->deleteId);
        
        if (!$organization->canBeDeleted()) {
            session()->flash('error', 'This organization cannot be deleted because it has associated records.');
            return;
        }

        $organization->delete();
        session()->flash('message', 'Organization deleted successfully.');
        $this->deleteId = null;
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        if (!$this->canEdit) {
            session()->flash('error', 'You do not have permission to edit organizations.');
            return;
        }

        $organization = Organization::findOrFail($id);
        $organization->update(['is_active' => !$organization->is_active]);
        
        $status = $organization->is_active ? 'activated' : 'deactivated';
        session()->flash('message', "Organization {$status} successfully.");
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->reset('form');
        $this->resetPage();
    }

    public function cancelDelete()
    {
        $this->deleteId = null;
    }

    // Remove custom validation methods - using ValidatesOrganizations trait instead

    #[On('refreshOrganizations')]
    public function refreshOrganizations()
    {
        $this->resetPage();
    }

    // Temporary debug method to check permissions
    public function debugPermissions()
    {
        $user = auth()->user();
        $debug = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'roles' => $user->roles->pluck('name')->toArray(),
            'has_admin_role' => $user->hasRole('admin'),
            'can_create_org' => $user->can('organizations.create'),
            'can_update_org' => $user->can('organizations.update'),
            'can_delete_org' => $user->can('organizations.delete'),
            'all_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ];
        
        session()->flash('debug', json_encode($debug, JSON_PRETTY_PRINT));
    }


}
