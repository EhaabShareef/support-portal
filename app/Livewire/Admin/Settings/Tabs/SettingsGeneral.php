<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Contracts\SettingsRepositoryInterface;
use App\Services\HotlineService;
use Livewire\Component;

class SettingsGeneral extends Component
{
    public array $hotlines = [];
    public bool $showAddForm = false;
    public string $editingKey = '';
    public array $newHotlineForm = [
        'name' => '',
        'number' => '',
        'description' => '',
    ];
    public array $editHotlineForm = [
        'name' => '',
        'number' => '',
        'description' => '',
    ];

    // Theme settings (future expansion)
    public array $themeSettings = [
        'primary_color' => '#3b82f6',
        'allow_user_theme_selection' => false,
    ];

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
            // Load hotlines
            $hotlineService = app(HotlineService::class);
            $this->hotlines = $hotlineService->getHotlinesForAdmin();

            // Load theme settings (future)
            $repository = app(SettingsRepositoryInterface::class);
            $this->themeSettings['primary_color'] = $repository->get('theme.primary_color', '#3b82f6');
            $this->themeSettings['allow_user_theme_selection'] = $repository->get('theme.allow_user_theme_selection', false);
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to load general settings: ' . $e->getMessage());
        }
    }

    public function refreshData()
    {
        $this->loadData();
    }

    // Hotline Management Methods
    public function addNewHotline()
    {
        $this->checkPermission('settings.update');
        $this->showAddForm = true;
        $this->newHotlineForm = [
            'name' => '',
            'number' => '',
            'description' => '',
        ];
    }

    public function cancelAddHotline()
    {
        $this->showAddForm = false;
        $this->newHotlineForm = [
            'name' => '',
            'number' => '',
            'description' => '',
        ];
    }

    public function saveNewHotline()
    {
        $this->checkPermission('settings.update');
        
        $this->validate([
            'newHotlineForm.name' => 'required|string|max:255',
            'newHotlineForm.number' => 'required|string|max:255',
            'newHotlineForm.description' => 'required|string|max:500',
        ]);

        try {
            $hotlineService = app(HotlineService::class);
            
            // Generate key from name
            $key = strtolower(str_replace(' ', '_', $this->newHotlineForm['name']));
            $key = preg_replace('/[^a-z0-9_]/', '', $key);
            
            // Ensure unique key
            $originalKey = $key;
            $counter = 1;
            while (isset($this->hotlines[$key])) {
                $key = $originalKey . '_' . $counter;
                $counter++;
            }
            
            // Add to hotlines with defaults
            $this->hotlines[$key] = [
                'name' => $this->newHotlineForm['name'],
                'number' => $this->newHotlineForm['number'],
                'description' => $this->newHotlineForm['description'],
                'is_active' => true,
                'sort_order' => count($this->hotlines) + 1,
            ];

            // Save to database
            $hotlineService->updateHotlines($this->hotlines);
            
            $this->showAddForm = false;
            $this->newHotlineForm = ['name' => '', 'number' => '', 'description' => ''];
            $this->dispatch('saved', 'Hotline added successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to add hotline: ' . $e->getMessage());
        }
    }

    public function startEditHotline($key)
    {
        $this->checkPermission('settings.update');
        $hotline = $this->hotlines[$key] ?? null;
        
        if (!$hotline) {
            $this->dispatch('error', 'Hotline not found.');
            return;
        }

        $this->editingKey = $key;
        $this->editHotlineForm = [
            'name' => $hotline['name'],
            'number' => $hotline['number'],
            'description' => $hotline['description'],
        ];
    }

    public function cancelEditHotline()
    {
        $this->editingKey = '';
        $this->editHotlineForm = [
            'name' => '',
            'number' => '',
            'description' => '',
        ];
    }

    public function saveEditHotline()
    {
        $this->checkPermission('settings.update');
        
        $this->validate([
            'editHotlineForm.name' => 'required|string|max:255',
            'editHotlineForm.number' => 'required|string|max:255',
            'editHotlineForm.description' => 'required|string|max:500',
        ]);

        try {
            $hotlineService = app(HotlineService::class);
            
            if (!isset($this->hotlines[$this->editingKey])) {
                $this->dispatch('error', 'Hotline not found.');
                return;
            }
            
            // Update existing hotline, preserve other fields
            $this->hotlines[$this->editingKey] = array_merge($this->hotlines[$this->editingKey], [
                'name' => $this->editHotlineForm['name'],
                'number' => $this->editHotlineForm['number'],
                'description' => $this->editHotlineForm['description'],
            ]);

            // Save to database
            $hotlineService->updateHotlines($this->hotlines);
            
            $this->editingKey = '';
            $this->editHotlineForm = ['name' => '', 'number' => '', 'description' => ''];
            $this->dispatch('saved', 'Hotline updated successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to update hotline: ' . $e->getMessage());
        }
    }

    public function deleteHotline($key)
    {
        $this->checkPermission('settings.update');
        
        if (!isset($this->hotlines[$key])) {
            $this->dispatch('error', 'Hotline not found.');
            return;
        }

        try {
            unset($this->hotlines[$key]);
            
            $hotlineService = app(HotlineService::class);
            $hotlineService->updateHotlines($this->hotlines);
            
            $this->dispatch('saved', 'Hotline deleted successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to delete hotline: ' . $e->getMessage());
        }
    }

    public function toggleHotlineStatus($key)
    {
        $this->checkPermission('settings.update');
        
        if (!isset($this->hotlines[$key])) {
            $this->dispatch('error', 'Hotline not found.');
            return;
        }

        try {
            $this->hotlines[$key]['is_active'] = !$this->hotlines[$key]['is_active'];
            
            $hotlineService = app(HotlineService::class);
            $hotlineService->updateHotlines($this->hotlines);
            
            $status = $this->hotlines[$key]['is_active'] ? 'enabled' : 'disabled';
            $this->dispatch('saved', "Hotline {$status} successfully.");
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to update hotline status: ' . $e->getMessage());
        }
    }


    public function resetToDefaults()
    {
        $this->checkPermission('settings.update');
        
        try {
            // Reset hotlines to defaults
            $hotlineService = app(HotlineService::class);
            $repository = app(SettingsRepositoryInterface::class);
            
            // Delete existing hotlines setting to restore defaults
            $repository->reset('support_hotlines');
            
            // Reload data
            $this->loadData();
            
            $this->dispatch('reset', 'General settings reset to defaults successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }

    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to {$permission}.");
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.tabs.general');
    }
}