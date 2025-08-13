<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Traits\ValidatesOrganizations;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class ManageOrganizations extends Component
{
    use WithPagination, ValidatesOrganizations;

    public $search = '';
    public $statusFilter = 'all';
    public $subscriptionFilter = 'all';
    public $showForm = false;
    public $deleteId = null;

    public $form = [
        'id' => null,
        'name' => '',
        'company' => '',
        'company_contact' => '',
        'tin_no' => '',
        'email' => '',
        'phone' => '',
        'is_active' => true,
        'subscription_status' => 'trial',
        'notes' => '',
    ];

    protected $rules = [];

    public function mount()
    {
        // Check permissions
        if (!auth()->user()->can('organizations.read')) {
            abort(403, 'You do not have permission to view organizations.');
        }
    }

    #[Computed]
    public function canCreate()
    {
        return auth()->user()->hasRole('admin') || auth()->user()->can('organizations.create');
    }

    #[Computed]
    public function canEdit()
    {
        return auth()->user()->hasRole('admin') || auth()->user()->can('organizations.update');
    }

    #[Computed]
    public function canDelete()
    {
        return auth()->user()->hasRole('admin') || auth()->user()->can('organizations.delete');
    }

    public function render()
    {
        $query = Organization::query()
            ->withCount(['users', 'contracts', 'hardware', 'tickets'])
            ->where(function ($query) {
                if ($this->search) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('company', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                }
            });

        // Apply filters
        if ($this->statusFilter !== 'all') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        if ($this->subscriptionFilter !== 'all') {
            $query->where('subscription_status', $this->subscriptionFilter);
        }

        // Role-based filtering
        $user = auth()->user();
        if ($user->hasRole('client')) {
            $query->where('id', $user->organization_id);
        }

        $organizations = $query->orderBy('name')->paginate(10);

        return view('livewire.manage-organizations', compact('organizations'));
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
        if (auth()->user()->hasRole('client') && $organization->id !== auth()->user()->organization_id) {
            session()->flash('error', 'You can only edit your own organization.');
            return;
        }

        $this->form = $organization->toArray();
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

        $organization = Organization::updateOrCreate(
            ['id' => $data['id']],
            $data
        );

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
            session()->flash('error', 'Cannot delete organization with associated users, tickets, or contracts.');
            return;
        }

        $this->deleteId = $id;
    }

    public function delete()
    {
        $organization = Organization::findOrFail($this->deleteId);
        
        if ($organization->canBeDeleted()) {
            $organization->delete();
            session()->flash('message', 'Organization deleted successfully.');
        } else {
            session()->flash('error', 'Cannot delete organization with associated data.');
        }

        $this->deleteId = null;
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
}
