<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Contracts\SettingsRepositoryInterface;
use App\Models\Organization;
use Livewire\Attributes\Computed;
use Livewire\Component;

class SettingsUsers extends Component
{
    // User Management Settings
    public ?int $defaultOrganizationId = null;
    public bool $allowUserRegistration = false;
    public bool $requireEmailVerification = true;
    public string $defaultUserRole = 'client';
    public bool $autoAssignToDefaultOrganization = true;
    public int $passwordMinLength = 8;
    public bool $requireStrongPasswords = true;

    public bool $hasUnsavedChanges = false;

    protected $listeners = ['tabChanged' => 'refreshData'];

    public function mount()
    {
        $this->checkPermission('settings.read');
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $repository = app(SettingsRepositoryInterface::class);
            
            // Load user management settings
            $this->defaultOrganizationId = $repository->get('users.default_organization_id', null);
            $this->allowUserRegistration = (bool) $repository->get('users.allow_registration', false);
            $this->requireEmailVerification = (bool) $repository->get('users.require_email_verification', true);
            $this->defaultUserRole = $repository->get('users.default_role', 'client');
            $this->autoAssignToDefaultOrganization = (bool) $repository->get('users.auto_assign_default_organization', true);
            $this->passwordMinLength = (int) $repository->get('users.password_min_length', 8);
            $this->requireStrongPasswords = (bool) $repository->get('users.require_strong_passwords', true);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to load user settings: ' . $e->getMessage());
        }
    }

    public function refreshData()
    {
        $this->loadData();
    }

    #[Computed]
    public function organizations()
    {
        return Organization::orderBy('name')->get();
    }

    // Setting update handlers
    public function updatedDefaultOrganizationId()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedAllowUserRegistration()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedRequireEmailVerification()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedDefaultUserRole()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedAutoAssignToDefaultOrganization()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedPasswordMinLength()
    {
        $this->hasUnsavedChanges = true;
    }

    public function updatedRequireStrongPasswords()
    {
        $this->hasUnsavedChanges = true;
    }

    public function saveSettings()
    {
        $this->checkPermission('settings.update');

        $this->validate([
            'defaultOrganizationId' => 'nullable|exists:organizations,id',
            'allowUserRegistration' => 'boolean',
            'requireEmailVerification' => 'boolean',
            'defaultUserRole' => 'required|in:client,support,admin',
            'autoAssignToDefaultOrganization' => 'boolean',
            'passwordMinLength' => 'required|integer|min:6|max:50',
            'requireStrongPasswords' => 'boolean',
        ]);

        try {
            $repository = app(SettingsRepositoryInterface::class);
            
            $repository->set('users.default_organization_id', $this->defaultOrganizationId, 'integer');
            $repository->set('users.allow_registration', $this->allowUserRegistration, 'boolean');
            $repository->set('users.require_email_verification', $this->requireEmailVerification, 'boolean');
            $repository->set('users.default_role', $this->defaultUserRole, 'string');
            $repository->set('users.auto_assign_default_organization', $this->autoAssignToDefaultOrganization, 'boolean');
            $repository->set('users.password_min_length', $this->passwordMinLength, 'integer');
            $repository->set('users.require_strong_passwords', $this->requireStrongPasswords, 'boolean');

            $this->hasUnsavedChanges = false;
            $this->dispatch('saved', 'User settings saved successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save user settings: ' . $e->getMessage());
        }
    }

    public function resetToDefaults()
    {
        $this->checkPermission('settings.update');
        
        try {
            $repository = app(SettingsRepositoryInterface::class);
            
            // Reset all user settings
            $repository->reset('users.default_organization_id');
            $repository->reset('users.allow_registration');
            $repository->reset('users.require_email_verification');
            $repository->reset('users.default_role');
            $repository->reset('users.auto_assign_default_organization');
            $repository->reset('users.password_min_length');
            $repository->reset('users.require_strong_passwords');
            
            // Reload data
            $this->loadData();
            $this->hasUnsavedChanges = false;
            
            $this->dispatch('reset', 'User settings reset to defaults successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }

    public function getUserRoleOptionsProperty()
    {
        return [
            'client' => 'Client - Standard user access',
            'support' => 'Support - Staff member access',
            'admin' => 'Administrator - Full system access',
        ];
    }

    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to {$permission}.");
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.tabs.users');
    }
}