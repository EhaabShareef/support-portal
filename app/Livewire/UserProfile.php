<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;

class UserProfile extends Component
{
    use WithFileUploads;

    public $showModal = false;
    public $tab = 'profile'; // 'profile' or 'password'
    
    // Profile form
    public $name;
    public $email;
    public $username;
    public $avatar;
    public $newAvatar;
    public $previewAvatar;
    
    // Password form
    public $currentPassword = '';
    public $newPassword = '';
    public $newPasswordConfirmation = '';

    protected function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username,' . Auth::id()],
        ];

        if ($this->newAvatar) {
            $rules['newAvatar'] = ['image', 'max:2048']; // 2MB max
        }

        if ($this->tab === 'password') {
            $rules = [
                'currentPassword' => ['required', 'current_password'],
                'newPassword' => ['required', Password::defaults(), 'confirmed'],
                'newPasswordConfirmation' => ['required'],
            ];
        }

        return $rules;
    }

    protected $messages = [
        'currentPassword.current_password' => 'The current password is incorrect.',
        'newPassword.confirmed' => 'The password confirmation does not match.',
        'newAvatar.image' => 'The file must be an image.',
        'newAvatar.max' => 'The image must not be larger than 2MB.',
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->username = $user->username;
        $this->avatar = $user->avatar;
    }

    #[On('openProfileModal')]
    public function openModal($tab = 'profile')
    {
        $this->tab = $tab;
        $this->showModal = true;
        
        // Reset forms
        $this->resetErrorBag();
        $this->resetPasswordForm();
        
        // Reload user data
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->username = $user->username;
        $this->avatar = $user->avatar;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetPasswordForm();
        $this->resetErrorBag();
        $this->newAvatar = null;
        $this->previewAvatar = null;
    }

    public function switchTab($tab)
    {
        $this->tab = $tab;
        $this->resetErrorBag();
    }

    public function updatedNewAvatar()
    {
        $this->validate(['newAvatar' => 'image|max:2048']);
        
        if ($this->newAvatar) {
            $this->previewAvatar = $this->newAvatar->temporaryUrl();
        }
    }

    public function removeAvatar()
    {
        $this->newAvatar = null;
        $this->previewAvatar = null;
        
        // Also remove from database if updating
        $user = Auth::user();
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->update(['avatar' => null]);
            $this->avatar = null;
            session()->flash('message', 'Avatar removed successfully.');
        }
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username,' . Auth::id()],
            'newAvatar' => $this->newAvatar ? ['image', 'max:2048'] : [],
        ]);

        try {
            $user = Auth::user();
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'username' => $this->username,
            ];

            // Handle avatar upload
            if ($this->newAvatar) {
                // Delete old avatar
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                // Store new avatar
                $avatarPath = $this->newAvatar->store('avatars', 'public');
                $data['avatar'] = $avatarPath;
                $this->avatar = $avatarPath;
            }

            $user->update($data);
            
            $this->newAvatar = null;
            $this->previewAvatar = null;
            $this->closeModal();
            
            session()->flash('message', 'Profile updated successfully.');
            
        } catch (\Exception $e) {
            logger()->error('Failed to update profile', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Failed to update profile. Please try again.');
        }
    }

    public function updatePassword()
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'newPassword' => ['required', Password::defaults(), 'confirmed'],
            'newPasswordConfirmation' => ['required'],
        ]);

        try {
            Auth::user()->update([
                'password' => Hash::make($this->newPassword)
            ]);
            
            $this->resetPasswordForm();
            $this->closeModal();
            
            session()->flash('message', 'Password updated successfully.');
            
        } catch (\Exception $e) {
            logger()->error('Failed to update password', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Failed to update password. Please try again.');
        }
    }

    private function resetPasswordForm()
    {
        $this->currentPassword = '';
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';
    }

    public function render()
    {
        return view('livewire.user-profile');
    }
}