<?php

namespace App\Livewire;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class OrganizationUserForm extends Component
{
    public Organization $organization;
    public ?User $user = null;
    public bool $isEditing = false;

    public array $form = [
        'name' => '',
        'username' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'is_active' => true,
        'timezone' => 'UTC',
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.username' => 'required|string|max:255|alpha_dash',
        'form.email' => 'required|email|max:255',
        'form.password' => 'required|string|min:8|confirmed',
        'form.password_confirmation' => 'required|string|min:8',
        'form.is_active' => 'boolean',
        'form.timezone' => 'required|string|max:50',
    ];

    protected $messages = [
        'form.name.required' => 'Full name is required',
        'form.username.required' => 'Username is required',
        'form.username.alpha_dash' => 'Username can only contain letters, numbers, dashes, and underscores',
        'form.username.unique' => 'This username is already taken',
        'form.email.required' => 'Email address is required',
        'form.email.email' => 'Please enter a valid email address',
        'form.email.unique' => 'This email address is already registered',
        'form.password.required' => 'Password is required',
        'form.password.min' => 'Password must be at least 8 characters',
        'form.password.confirmed' => 'Password confirmation does not match',
        'form.password_confirmation.required' => 'Password confirmation is required',
    ];

    public function mount(Organization $organization)
    {
        $this->organization = $organization;
        $this->resetToNew();
    }
    
    private function resetToNew()
    {
        $this->isEditing = false;
        $this->user = null;
        $this->form = [
            'name' => '',
            'username' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'is_active' => true,
            'timezone' => 'UTC',
        ];
    }

    #[On('newUser')]
    public function newUser()
    {
        $this->resetToNew();
    }

    #[On('editUser')]
    public function editUser($userId)
    {
        $this->user = User::findOrFail($userId);
        
        // Only allow editing client users from this organization
        if (!$this->user->hasRole('client') || $this->user->organization_id !== $this->organization->id) {
            session()->flash('error', 'You can only edit client users belonging to this organization.');
            return;
        }
        
        $this->isEditing = true;
        $this->loadUserData();
    }

    private function loadUserData()
    {
        if (!$this->user) return;

        $this->form = [
            'name' => $this->user->name,
            'username' => $this->user->username,
            'email' => $this->user->email,
            'password' => '',
            'password_confirmation' => '',
            'is_active' => $this->user->is_active,
            'timezone' => $this->user->timezone ?? 'UTC',
        ];
    }

    public function updatedFormUsername()
    {
        // Auto-generate username from name if username is empty
        if (empty($this->form['username']) && !empty($this->form['name'])) {
            $this->form['username'] = Str::slug($this->form['name'], '');
        }
    }

    public function save()
    {
        // Add unique rules, excluding current user if editing
        $usernameRule = 'required|string|max:255|alpha_dash|unique:users,username';
        $emailRule = 'required|email|max:255|unique:users,email';
        
        if ($this->isEditing && $this->user) {
            $usernameRule .= ',' . $this->user->id;
            $emailRule .= ',' . $this->user->id;
        }
        
        $this->rules['form.username'] = $usernameRule;
        $this->rules['form.email'] = $emailRule;

        // Password is only required for new users
        if ($this->isEditing) {
            if (empty($this->form['password'])) {
                // Remove password validation if not changing password
                unset($this->rules['form.password']);
                unset($this->rules['form.password_confirmation']);
            }
        }
        
        $this->validate();

        $data = [
            'name' => $this->form['name'],
            'username' => $this->form['username'],
            'email' => $this->form['email'],
            'is_active' => $this->form['is_active'],
            'timezone' => $this->form['timezone'] ?: 'UTC',
            'organization_id' => $this->organization->id,
        ];

        // Only update password if provided
        if (!empty($this->form['password'])) {
            $data['password'] = $this->form['password'];
        }

        if ($this->isEditing && $this->user) {
            // Only allow editing client users from this organization
            if (!$this->user->hasRole('client') || $this->user->organization_id !== $this->organization->id) {
                $this->addError('form.name', 'You can only edit client users belonging to this organization.');
                return;
            }
            
            $this->user->update($data);
            $message = 'User updated successfully.';
        } else {
            // Create new user
            $data['uuid'] = Str::uuid();
            $user = User::create($data);
            
            // Ensure user has client role (User model boot method should handle this, but let's be explicit)
            $clientRole = Role::where('name', 'client')->first();
            if ($clientRole && !$user->hasRole('client')) {
                $user->assignRole($clientRole);
            }
            
            $message = 'Client user created successfully.';
        }

        $this->dispatch('userSaved');
        $this->dispatch('refreshOrganization');
        session()->flash('message', $message);
        
        // Reset form and dispatch close modal event
        $this->reset(['form', 'isEditing', 'user']);
    }

    public function cancel()
    {
        $this->reset(['form', 'isEditing', 'user']);
        $this->dispatch('userCancelled');
    }

    public function render()
    {
        return view('livewire.organization-user-form');
    }
}