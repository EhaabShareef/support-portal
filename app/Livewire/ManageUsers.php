<?php

namespace App\Livewire;

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

    public array $form = [
        'name' => '',
        'username' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'is_active' => true,
    ];

    protected $rules = [
        'form.name' => 'required|string|max:255',
        'form.username' => 'required|string|max:255',
        'form.email' => 'required|email',
        'form.password' => 'required|min:8|confirmed',
        'form.is_active' => 'boolean',
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
        if (!$user->can('users.manage') && !$user->hasRole(['Admin', 'Super Admin'])) {
            abort(403, 'You do not have permission to manage users.');
        }

        // Clients can only manage users in their own organization
        if ($user->hasRole('Client') && $organization->id !== $user->organization_id) {
            abort(403, 'You can only manage users in your own organization.');
        }

        $this->organization = $organization;
    }

    public function render()
    {
        $users = User::where('organization_id', $this->organization->id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Client');
            })
            ->with('roles')
            ->latest()
            ->paginate(10);

        return view('livewire.manage-users', [
            'users' => $users
        ]);
    }

    public function create()
    {
        $this->reset(['form', 'editingUser']);
        
        $this->form = [
            'name' => '',
            'username' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'is_active' => true,
        ];
        
        $this->showForm = true;
    }

    public function edit($id)
    {
        $this->editingUser = User::findOrFail($id);

        // Only allow editing Client users from this organization
        if (!$this->editingUser->hasRole('Client') || $this->editingUser->organization_id !== $this->organization->id) {
            session()->flash('error', 'You can only edit client users belonging to this organization.');
            return;
        }

        $this->form = [
            'name' => $this->editingUser->name,
            'username' => $this->editingUser->username,
            'email' => $this->editingUser->email,
            'password' => '',
            'password_confirmation' => '',
            'is_active' => $this->editingUser->is_active,
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
        $data['organization_id'] = $this->organization->id;

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
            $user->assignRole('Client'); // Assign Client role
            $message = 'User created successfully.';
        }

        $this->reset(['showForm', 'form', 'editingUser']);
        session()->flash('message', $message);
    }

    public function confirmDelete($id)
    {
        $user = User::findOrFail($id);
        
        // Only allow deletion of Client users from this organization
        if (!$user->hasRole('Client') || $user->organization_id !== $this->organization->id) {
            session()->flash('error', 'You can only delete client users belonging to this organization.');
            return;
        }
        
        $this->deleteId = $id;
    }

    public function delete()
    {
        $user = User::findOrFail($this->deleteId);
        
        // Only allow deletion of Client users from this organization
        if (!$user->hasRole('Client') || $user->organization_id !== $this->organization->id) {
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
}