<?php

namespace App\Livewire\Admin\Settings\Tabs;

use App\Models\Setting;
use App\Services\SettingsRepository;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ApplicationSettings extends Component
{
    public bool $showSettingModal = false;
    public bool $settingEditMode = false;
    public ?int $selectedSettingId = null;
    public array $settingForm = [
        'key' => '',
        'value' => '',
        'type' => 'string',
        'group' => 'general',
        'label' => '',
        'description' => '',
        'is_public' => false,
        'is_encrypted' => false,
        'validation_rules' => [],
    ];

    public string $validationRulesText = '';

    // Delete confirmations
    public ?int $confirmingSettingDelete = null;

    #[Computed]
    public function applicationSettings()
    {
        $repository = app(SettingsRepository::class);
        return $repository->getAllSettings()->groupBy('group');
    }

    public function createSetting()
    {
        $this->checkPermission('settings.update');
        $this->resetSettingForm();
        $this->settingEditMode = false;
        $this->showSettingModal = true;
    }

    public function editSetting($id)
    {
        $this->checkPermission('settings.update');
        $setting = Setting::findOrFail($id);
        $this->selectedSettingId = $id;
        $this->settingForm = $setting->toArray();
        $this->validationRulesText = $setting->getValidationRulesString();
        $this->settingEditMode = true;
        $this->showSettingModal = true;
    }

    public function saveSetting()
    {
        $this->checkPermission('settings.update');
        
        $rules = [
            'settingForm.key' => 'required|string|max:255',
            'settingForm.value' => 'nullable',
            'settingForm.type' => 'required|in:string,integer,float,boolean,json,array',
            'settingForm.group' => 'required|string|max:255',
            'settingForm.label' => 'required|string|max:255',
            'settingForm.description' => 'nullable|string',
            'settingForm.is_public' => 'boolean',
            'settingForm.is_encrypted' => 'boolean',
        ];

        if (!$this->settingEditMode) {
            $rules['settingForm.key'] .= '|unique:settings,key';
        } else {
            $rules['settingForm.key'] .= '|unique:settings,key,' . $this->selectedSettingId;
        }

        $validated = $this->validate($rules);

        // Parse validation rules
        if ($this->validationRulesText) {
            $validated['settingForm']['validation_rules'] = explode('|', $this->validationRulesText);
        } else {
            $validated['settingForm']['validation_rules'] = null;
        }

        try {
            $repository = app(SettingsRepository::class);
            
            if ($this->settingEditMode) {
                $repository->updateSetting($this->selectedSettingId, $validated['settingForm']);
                $message = 'Setting updated successfully.';
            } else {
                $repository->createSetting($validated['settingForm']);
                $message = 'Setting created successfully.';
            }

            $this->closeSettingModal();
            $this->dispatch('flash', $message, 'success');
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to save setting: ' . $e->getMessage(), 'error');
        }
    }

    public function confirmDeleteSetting($id)
    {
        $this->checkPermission('settings.update');
        $this->confirmingSettingDelete = $id;
    }

    public function deleteSetting()
    {
        $this->checkPermission('settings.update');
        
        try {
            $repository = app(SettingsRepository::class);
            $repository->deleteSetting($this->confirmingSettingDelete);
            $this->confirmingSettingDelete = null;
            $this->dispatch('flash', 'Setting deleted successfully.', 'success');
        } catch (\Exception $e) {
            $this->dispatch('flash', 'Failed to delete setting: ' . $e->getMessage(), 'error');
        }
    }

    public function closeSettingModal()
    {
        $this->showSettingModal = false;
        $this->resetSettingForm();
    }

    public function cancelDelete()
    {
        $this->confirmingSettingDelete = null;
    }

    private function resetSettingForm()
    {
        $this->settingForm = [
            'key' => '',
            'value' => '',
            'type' => 'string',
            'group' => 'general',
            'label' => '',
            'description' => '',
            'is_public' => false,
            'is_encrypted' => false,
            'validation_rules' => [],
        ];
        $this->validationRulesText = '';
        $this->selectedSettingId = null;
        $this->resetErrorBag('settingForm');
    }

    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            abort(403, "You don't have permission to {$permission}.");
        }
    }

    public function render()
    {
        return view('livewire.admin.settings.tabs.application-settings');
    }
}