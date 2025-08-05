<?php

namespace App\Livewire;

use App\Models\Organization;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class ManageOrganizations extends Component
{
    use WithPagination;

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

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.company' => 'required|string|max:255',
        'form.company_contact' => 'required|string|max:255',
        'form.tin_no' => 'required|string|max:255|unique:organizations,tin_no',
        'form.email' => 'required|email|unique:organizations,email',
        'form.phone' => 'nullable|string|max:20',
        'form.is_active' => 'boolean',
        'form.subscription_status' => 'required|in:trial,active,suspended,cancelled',
        'form.notes' => 'nullable|string',
    ];

    public function mount()
    {
        // Check permissions
        if (!auth()->user()->can('organizations.view')) {
            abort(403, 'You do not have permission to view organizations.');
        }
    }

    #[Computed]
    public function canCreate()
    {
        return auth()->user()->hasRole('Super Admin') || auth()->user()->can('organizations.create');
    }

    #[Computed]
    public function canEdit()
    {
        return auth()->user()->hasRole('Super Admin') || auth()->user()->can('organizations.edit');
    }

    #[Computed]
    public function canDelete()
    {
        return auth()->user()->hasRole('Super Admin') || auth()->user()->can('organizations.delete');
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
        if ($user->hasRole('Client')) {
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
        if (auth()->user()->hasRole('Client') && $organization->id !== auth()->user()->organization_id) {
            session()->flash('error', 'You can only edit your own organization.');
            return;
        }

        $this->form = $organization->toArray();
        $this->showForm = true;
    }

    public function save()
    {
        // Update validation rules for editing
        $rules = $this->rules;
        if ($this->form['id']) {
            $rules['form.tin_no'] = 'required|string|max:255|unique:organizations,tin_no,' . $this->form['id'];
            $rules['form.email'] = 'required|email|unique:organizations,email,' . $this->form['id'];
        }

        $this->validate($rules);

        $organization = Organization::updateOrCreate(
            ['id' => $this->form['id']],
            $this->form
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
