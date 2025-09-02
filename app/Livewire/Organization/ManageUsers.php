<?php

namespace App\Livewire\Organization;

use App\Models\Organization;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;


class ManageUsers extends Component
{
    use WithPagination;

    public Organization $organization;
    public $showForm = false;
    public $deleteId = null;
    public $editingUser = null;
    public $showCorporateForm = false;
    public $convertingUser = null;
    public $selectedOrganizations = [];
    public $searchQuery = '';
    public $allOrganizations = [];
    public $confirmingPrimaryUser = null;

    public array $form = [
        'name' => '',
        'username' => '',
        'email' => '',
        'phone' => '',
        'password' => '',
        'password_confirmation' => '',
        'is_active' => true,
        'timezone' => 'UTC',
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.username' => 'required|string|max:255',
        'form.email' => 'required|email',
        'form.phone' => 'nullable|string|max:32',
        'form.password' => 'required|min:8|confirmed',
        'form.is_active' => 'boolean',
        'form.timezone' => 'required|string|max:255',
    ];

    protected $messages = [
        'form.name.required' => 'Name is required',
        'form.username.required' => 'Username is required',
        'form.email.required' => 'Email is required',
        'form.email.email' => 'Please enter a valid email address',
        'form.password.required' => 'Password is required',
        'form.password.min' => 'Password must be at least 8 characters',
        'form.password.confirmed' => 'Passwords do not match',
    ];

    public function mount(Organization $organization)
    {
        // Check permissions
        $user = auth()->user();
        if (!$user->can('users.manage') && !$user->hasRole('admin')) {
            abort(403, 'You do not have permission to manage users.');
        }

        // Clients can only manage users in their own organization
        if ($user->hasRole('client') && !$user->organizations->contains($organization->id)) {
            abort(403, 'You can only manage users in your own organization.');
        }

        $this->organization = $organization;
    }

    public function render()
    {
        $users = $this->organization->users()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'client');
            })
            ->with(['roles', 'organizations'])
            ->latest()
            ->paginate(10);

        // Get all organizations for corporate user assignment
        $this->allOrganizations = Organization::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.organization.manage-users', [
            'users' => $users,
            'filteredOrganizations' => $this->filteredOrganizations
        ]);
    }

    public function create()
    {
        $this->reset(['form', 'editingUser']);
        
        $this->form = [
            'name' => '',
            'username' => '',
            'email' => '',
            'phone' => '',
            'password' => '',
            'password_confirmation' => '',
            'is_active' => true,
            'timezone' => 'UTC',
        ];
        
        $this->showForm = true;
    }

    public function edit($id)
    {
        $this->editingUser = User::findOrFail($id);

        // Only allow editing client users from this organization
        if (!$this->editingUser->hasRole('client') || !$this->editingUser->organizations->contains($this->organization->id)) {
            session()->flash('error', 'You can only edit client users belonging to this organization.');
            return;
        }

        $this->form = [
            'name' => $this->editingUser->name,
            'username' => $this->editingUser->username,
            'email' => $this->editingUser->email,
            'phone' => $this->editingUser->phone,
            'password' => '',
            'password_confirmation' => '',
            'is_active' => $this->editingUser->is_active,
            'timezone' => $this->editingUser->timezone,
        ];

        $this->showForm = true;
    }

    public function save()
    {
        // Add unique rules for username and email, excluding current user if editing
        $usernameRule = 'required|string|max:255|unique:users,username';
        $emailRule = 'required|email|unique:users,email';
        
        if ($this->editingUser) {
            $usernameRule .= ',' . $this->editingUser->id;
            $emailRule .= ',' . $this->editingUser->id;
            
            // Password is optional when editing
            if (empty($this->form['password'])) {
                unset($this->rules['form.password']);
            }
        }
        
        $this->rules['form.username'] = $usernameRule;
        $this->rules['form.email'] = $emailRule;
        
        $this->validate();

        $data = $this->form;
        $data['user_type'] = 'standard';

        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // Don't update password if empty
        }

        // Remove password confirmation
        unset($data['password_confirmation']);

        if ($this->editingUser) {
            $this->editingUser->update(array_filter($data)); // array_filter removes null/empty values
            $message = 'User updated successfully.';
        } else {
            $user = User::create($data);
            $user->assignRole('client'); // Assign client role
            
            // Create the organization-user relationship
            $user->organizations()->attach($this->organization->id, [
                'is_primary' => false
            ]);
            
            $message = 'User created successfully.';
        }

        $this->reset(['showForm', 'form', 'editingUser']);
        session()->flash('message', $message);
    }

    public function confirmDelete($id)
    {
        $user = User::findOrFail($id);
        
        // Only allow deletion of Client users from this organization
        if (!$user->hasRole('client') || !$user->organizations->contains($this->organization->id)) {
            session()->flash('error', 'You can only delete client users belonging to this organization.');
            return;
        }
        
        $this->deleteId = $id;
    }

    public function delete()
    {
        $user = User::findOrFail($this->deleteId);
        
        // Only allow deletion of Client users from this organization
        if (!$user->hasRole('client') || !$user->organizations->contains($this->organization->id)) {
            session()->flash('error', 'You can only delete client users belonging to this organization.');
            $this->reset('deleteId');
            return;
        }
        
        $user->delete();
        $this->reset('deleteId');
        session()->flash('message', 'User deleted successfully.');
    }

    public function cancel()
    {
        $this->reset(['showForm', 'form', 'editingUser']);
    }

    public function showConvertToCorporate($userId)
    {
        $this->convertingUser = User::findOrFail($userId);
        
        // Check if user is a client user from this organization
        if (!$this->convertingUser->hasRole('client') || !$this->convertingUser->organizations->contains($this->organization->id)) {
            session()->flash('error', 'You can only convert client users belonging to this organization.');
            return;
        }

        // Get all available organizations for assignment
        $this->selectedOrganizations = [$this->organization->id]; // Include current organization by default
        
        $this->showCorporateForm = true;
        $this->searchQuery = ''; // Reset search query
    }

    public function convertToCorporate()
    {
        if (!$this->convertingUser) {
            session()->flash('error', 'No user selected for conversion.');
            return;
        }

        // Validate that at least one organization is selected
        if (empty($this->selectedOrganizations)) {
            session()->flash('error', 'Please select at least one organization.');
            return;
        }

        try {
            // Start transaction
            \DB::beginTransaction();

            // Update user type to corporate
            $this->convertingUser->update(['user_type' => 'corporate']);

            // Remove existing organization relationships
            $this->convertingUser->organizations()->detach();

            // Attach to selected organizations
            foreach ($this->selectedOrganizations as $orgId) {
                $this->convertingUser->organizations()->attach($orgId, [
                    'is_primary' => false // Corporate users are never primary
                ]);
            }

            // Keep the client role - corporate is just a user type, not a role
            // The user will still have 'client' role but with access to multiple organizations

            \DB::commit();

            session()->flash('message', 'User successfully converted to corporate user.');
            $this->reset(['showCorporateForm', 'convertingUser', 'selectedOrganizations', 'searchQuery']);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Failed to convert user: ' . $e->getMessage());
        }
    }

    public function cancelCorporateConversion()
    {
        $this->reset(['showCorporateForm', 'convertingUser', 'selectedOrganizations', 'searchQuery']);
    }

    public function showManageCorporateOrganizations($userId)
    {
        $this->convertingUser = User::findOrFail($userId);
        
        // Check if user is a corporate user
        if ($this->convertingUser->user_type !== 'corporate') {
            session()->flash('error', 'This user is not a corporate user.');
            return;
        }

        // Get current organization assignments
        $this->selectedOrganizations = $this->convertingUser->organizations->pluck('id')->toArray();
        
        $this->showCorporateForm = true;
        $this->searchQuery = ''; // Reset search query
    }

    public function updateCorporateOrganizations()
    {
        if (!$this->convertingUser || $this->convertingUser->user_type !== 'corporate') {
            session()->flash('error', 'No corporate user selected.');
            return;
        }

        // Validate that at least one organization is selected
        if (empty($this->selectedOrganizations)) {
            session()->flash('error', 'Please select at least one organization.');
            return;
        }

        try {
            // Start transaction
            \DB::beginTransaction();

            // Update organization relationships
            $this->convertingUser->organizations()->sync($this->selectedOrganizations);

            \DB::commit();

            session()->flash('message', 'Corporate user organization assignments updated successfully.');
            $this->reset(['showCorporateForm', 'convertingUser', 'selectedOrganizations', 'searchQuery']);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Failed to update organization assignments: ' . $e->getMessage());
        }
    }

    public function convertToStandard()
    {
        if (!$this->convertingUser || $this->convertingUser->user_type !== 'corporate') {
            session()->flash('error', 'No corporate user selected.');
            return;
        }

        try {
            // Start transaction
            \DB::beginTransaction();

            // Update user type to standard
            $this->convertingUser->update(['user_type' => 'standard']);

            // Keep only the current organization
            $this->convertingUser->organizations()->sync([$this->organization->id]);

            \DB::commit();

            session()->flash('message', 'User successfully converted back to standard user.');
            $this->reset(['showCorporateForm', 'convertingUser', 'selectedOrganizations', 'searchQuery']);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Failed to convert user: ' . $e->getMessage());
        }
    }

    public function toggleOrganization($organizationId)
    {
        if (in_array($organizationId, $this->selectedOrganizations)) {
            $this->removeOrganization($organizationId);
        } else {
            $this->addOrganization($organizationId);
        }
    }

    public function addOrganization($organizationId)
    {
        if (!in_array($organizationId, $this->selectedOrganizations)) {
            $this->selectedOrganizations[] = $organizationId;
        }
    }

    public function removeOrganization($organizationId)
    {
        $this->selectedOrganizations = array_filter($this->selectedOrganizations, function($id) use ($organizationId) {
            return $id != $organizationId;
        });
    }

    public function getFilteredOrganizationsProperty()
    {
        if (empty(trim($this->searchQuery))) {
            return $this->allOrganizations;
        }

        return $this->allOrganizations->filter(function($org) {
            return str_contains(strtolower($org->name), strtolower(trim($this->searchQuery)));
        });
    }

    public function updatedSearchQuery()
    {
        // Reset pagination when searching
        $this->resetPage();
    }

    public function confirmSetPrimaryUser($userId)
    {
        $this->confirmingPrimaryUser = $userId;
    }

    public function setPrimaryUser()
    {
        if (!$this->confirmingPrimaryUser) {
            return;
        }

        try {
            $user = User::findOrFail($this->confirmingPrimaryUser);
            
            // Check if user belongs to this organization
            if (!$user->organizations->contains($this->organization->id)) {
                session()->flash('error', 'This user does not belong to this organization.');
                return;
            }

            // Check if the user has client role
            if (!$user->hasRole('client')) {
                session()->flash('error', 'Only client users can be set as primary users.');
                return;
            }

            // Use the OrganizationUser model to set the primary user
            \App\Models\OrganizationUser::setPrimaryUser($user->id, $this->organization->id);
            
            session()->flash('message', 'Primary user updated successfully.');
            $this->confirmingPrimaryUser = null;
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update primary user: ' . $e->getMessage());
        }
    }

    public function cancelSetPrimaryUser()
    {
        $this->confirmingPrimaryUser = null;
    }

}
