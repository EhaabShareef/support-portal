<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Contracts\SettingsRepositoryInterface;
use App\Services\HotlineService;
use Livewire\Component;

class SettingsGeneral extends Component
{
    public array $hotlines = [];
    public bool $showHotlineModal = false;
    public bool $hotlineEditMode = false;
    public string $selectedHotlineKey = '';
    public array $hotlineForm = [
        'name' => '',
        'number' => '',
        'description' => '',
        'is_active' => true,
        'sort_order' => 1,
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
    public function openHotlineModal()
    {
        $this->checkPermission('settings.update');
        $this->hotlineEditMode = false;
        $this->selectedHotlineKey = '';
        $this->hotlineForm = [
            'name' => '',
            'number' => '',
            'description' => '',
            'is_active' => true,
            'sort_order' => count($this->hotlines) + 1,
        ];
        $this->showHotlineModal = true;
    }

    public function editHotline($key)
    {
        $this->checkPermission('settings.update');
        $hotline = $this->hotlines[$key] ?? null;
        
        if (!$hotline) {
            $this->dispatch('error', 'Hotline not found.');
            return;
        }

        $this->hotlineEditMode = true;
        $this->selectedHotlineKey = $key;
        $this->hotlineForm = $hotline;
        $this->showHotlineModal = true;
    }

    public function saveHotline()
    {
        $this->checkPermission('settings.update');
        
        $this->validate([
            'hotlineForm.name' => 'required|string|max:255',
            'hotlineForm.number' => 'required|string|max:255',
            'hotlineForm.description' => 'required|string|max:500',
            'hotlineForm.is_active' => 'boolean',
            'hotlineForm.sort_order' => 'required|integer|min:1',
        ]);

        try {
            $hotlineService = app(HotlineService::class);
            
            if ($this->hotlineEditMode && $this->selectedHotlineKey) {
                // Update existing hotline
                $this->hotlines[$this->selectedHotlineKey] = $this->hotlineForm;
            } else {
                // Add new hotline with generated key
                $key = strtolower(str_replace(' ', '_', $this->hotlineForm['name']));
                $key = preg_replace('/[^a-z0-9_]/', '', $key);
                
                // Ensure unique key
                $originalKey = $key;
                $counter = 1;
                while (isset($this->hotlines[$key])) {
                    $key = $originalKey . '_' . $counter;
                    $counter++;
                }
                
                $this->hotlines[$key] = $this->hotlineForm;
            }

            // Save to database
            $hotlineService->updateHotlines($this->hotlines);
            
            $this->showHotlineModal = false;
            $this->dispatch('saved', 'Hotline saved successfully.');
            
        } catch (\Exception $e) {
            $this->dispatch('error', 'Failed to save hotline: ' . $e->getMessage());
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

    public function closeHotlineModal()
    {
        $this->showHotlineModal = false;
        $this->hotlineEditMode = false;
        $this->selectedHotlineKey = '';
        $this->hotlineForm = [
            'name' => '',
            'number' => '',
            'description' => '',
            'is_active' => true,
            'sort_order' => 1,
        ];
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