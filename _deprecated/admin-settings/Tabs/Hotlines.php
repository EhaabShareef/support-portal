<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Services\HotlineService;
use Livewire\Component;

class Hotlines extends Component
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

    public function mount()
    {
        // Load hotlines
        $hotlineService = app(HotlineService::class);
        $this->hotlines = $hotlineService->getHotlinesForAdmin();
    }

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
            $this->dispatch('flash', 'Hotline not found.', 'error');
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
            $this->dispatch('flash', 'Hotline saved successfully.', 'success');
            
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to save hotline: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteHotline($key)
    {
        $this->checkPermission('settings.update');
        
        if (!isset($this->hotlines[$key])) {
            $this->dispatch('flash', 'Hotline not found.', 'error');
            return;
        }

        try {
            unset($this->hotlines[$key]);
            
            $hotlineService = app(HotlineService::class);
            $hotlineService->updateHotlines($this->hotlines);
            
            $this->dispatch('flash', 'Hotline deleted successfully.', 'success');
            
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to delete hotline: ' . $e->getMessage(), 'error');
        }
    }

    public function toggleHotlineStatus($key)
    {
        $this->checkPermission('settings.update');
        
        if (!isset($this->hotlines[$key])) {
            $this->dispatch('flash', 'Hotline not found.', 'error');
            return;
        }

        try {
            $this->hotlines[$key]['is_active'] = !$this->hotlines[$key]['is_active'];
            
            $hotlineService = app(HotlineService::class);
            $hotlineService->updateHotlines($this->hotlines);
            
            $status = $this->hotlines[$key]['is_active'] ? 'enabled' : 'disabled';
            $this->dispatch('flash', "Hotline {$status} successfully.", 'success');
            
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to update hotline status: ' . $e->getMessage(), 'error');
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

    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to {$permission}.");
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.tabs.hotlines');
    }
}