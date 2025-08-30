<?php

namespace App\Livewire\Admin\Settings;

use App\Contracts\SettingsRepositoryInterface;
use Livewire\Component;
use Livewire\Attributes\Computed;

abstract class BaseSettingsComponent extends Component
{
    public bool $hasUnsavedChanges = false;
    public string $flashMessage = '';
    public string $flashType = 'success';
    public bool $showFlash = false;

    protected SettingsRepositoryInterface $settingsRepository;

    public function mount()
    {
        $this->checkPermission('settings.read');
        $this->settingsRepository = app(SettingsRepositoryInterface::class);
        $this->loadData();
    }

    /**
     * Load data for the settings component
     */
    abstract protected function loadData(): void;

    /**
     * Save settings data
     */
    abstract protected function saveData(): void;

    /**
     * Get the settings group name
     */
    abstract protected function getSettingsGroup(): string;

    /**
     * Get the component title
     */
    abstract protected function getTitle(): string;

    /**
     * Get the component description
     */
    abstract protected function getDescription(): string;

    /**
     * Get the component icon
     */
    abstract protected function getIcon(): string;

    /**
     * Check if user has permission
     */
    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to {$permission}.");
        }
    }

    /**
     * Save settings
     */
    public function saveSettings()
    {
        $this->checkPermission('settings.update');
        
        try {
            $this->saveData();
            $this->hasUnsavedChanges = false;
            $this->showSuccess('Settings saved successfully.');
        } catch (\Exception $e) {
            $this->showError('Failed to save settings: ' . $e->getMessage());
        }
    }

    /**
     * Reset settings to defaults
     */
    public function resetToDefaults()
    {
        $this->checkPermission('settings.update');
        
        try {
            $this->resetData();
            $this->loadData();
            $this->hasUnsavedChanges = false;
            $this->showSuccess('Settings reset to defaults successfully.');
        } catch (\Exception $e) {
            $this->showError('Failed to reset settings: ' . $e->getMessage());
        }
    }

    /**
     * Reset data to defaults (override in child classes)
     */
    protected function resetData(): void
    {
        // Override in child classes if needed
    }

    /**
     * Show success message
     */
    protected function showSuccess(string $message): void
    {
        $this->flashMessage = $message;
        $this->flashType = 'success';
        $this->showFlash = true;
        
        // Auto-hide after 5 seconds
        $this->dispatch('flash-message', [
            'message' => $message,
            'type' => 'success'
        ]);
    }

    /**
     * Show error message
     */
    protected function showError(string $message): void
    {
        $this->flashMessage = $message;
        $this->flashType = 'error';
        $this->showFlash = true;
        
        $this->dispatch('flash-message', [
            'message' => $message,
            'type' => 'error'
        ]);
    }

    /**
     * Show warning message
     */
    protected function showWarning(string $message): void
    {
        $this->flashMessage = $message;
        $this->flashType = 'warning';
        $this->showFlash = true;
        
        $this->dispatch('flash-message', [
            'message' => $message,
            'type' => 'warning'
        ]);
    }

    /**
     * Hide flash message
     */
    public function hideFlash(): void
    {
        $this->showFlash = false;
    }

    /**
     * Mark as having unsaved changes
     */
    protected function markAsChanged(): void
    {
        $this->hasUnsavedChanges = true;
    }

    /**
     * Get settings for this group
     */
    #[Computed]
    protected function getGroupSettings()
    {
        return $this->settingsRepository->group($this->getSettingsGroup());
    }

    /**
     * Get a setting value
     */
    protected function getSetting(string $key, $default = null)
    {
        return $this->settingsRepository->get($key, $default);
    }

    /**
     * Set a setting value
     */
    protected function setSetting(string $key, $value, string $type = 'string'): void
    {
        $this->settingsRepository->set($key, $value, $type);
        $this->markAsChanged();
    }

    /**
     * Get component data for the shell
     */
    public function getComponentData(): array
    {
        return [
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'icon' => $this->getIcon(),
            'hasUnsavedChanges' => $this->hasUnsavedChanges,
        ];
    }

    /**
     * Refresh data
     */
    public function refreshData(): void
    {
        $this->loadData();
    }

    /**
     * Handle tab change event
     */
    public function tabChanged(): void
    {
        $this->refreshData();
    }
}
