<?php

namespace App\Livewire;

use App\Models\DashboardWidget;
use App\Models\UserWidgetSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CustomizeDashboard extends Component
{
    public $showModal = false;
    public $widgets = [];
    public $userSettings = [];

    protected $listeners = [
        'open-customize' => 'openModal',
        'close-customize' => 'closeModal',
    ];

    public function mount(): void
    {
        $this->loadWidgets();
    }

    /**
     * Load available widgets and user settings
     */
    public function loadWidgets(): void
    {
        $user = Auth::user();
        $role = $user->roles->first()?->name ?? 'client';

        // Get all active widgets that the user has permission to see
        $availableWidgets = DashboardWidget::active()
            ->ordered()
            ->get()
            ->filter(function ($widget) use ($user) {
                return !$widget->permission || $user->can($widget->permission);
            });

        // Get user's current settings
        $currentSettings = UserWidgetSetting::where('user_id', $user->id)
            ->with('widget')
            ->get()
            ->keyBy('widget_id');

        $this->widgets = $availableWidgets->map(function ($widget) use ($currentSettings, $user) {
            $setting = $currentSettings->get($widget->id);
            
            return [
                'id' => $widget->id,
                'key' => $widget->key,
                'name' => $widget->name,
                'description' => $widget->description,
                'default_size' => $widget->default_size,
                'default_order' => $widget->default_order,
                'visible' => $setting ? $setting->visible : true,
                'size' => $setting ? $setting->getEffectiveSize() : $widget->default_size,
                'sort_order' => $setting ? $setting->getEffectiveOrder() : $widget->default_order,
                'can_view' => $widget->isVisibleForUser($user),
            ];
        })->toArray();

        // Sort by current order
        usort($this->widgets, function ($a, $b) {
            return $a['sort_order'] <=> $b['sort_order'];
        });
    }

    /**
     * Open the modal
     */
    public function openModal(): void
    {
        $this->showModal = true;
        $this->loadWidgets();
    }

    /**
     * Close the modal
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->dispatch('close-customize'); // Notify parent component
    }

    /**
     * Toggle widget visibility
     */
    public function toggleVisibility(int $index): void
    {
        if (isset($this->widgets[$index])) {
            $this->widgets[$index]['visible'] = !$this->widgets[$index]['visible'];
        }
    }

    /**
     * Change widget size
     */
    public function changeSize(int $index, string $size): void
    {
        if (isset($this->widgets[$index])) {
            $this->widgets[$index]['size'] = $size;
        }
    }

    /**
     * Move widget up in order
     */
    public function moveUp(int $index): void
    {
        if ($index > 0) {
            $temp = $this->widgets[$index];
            $this->widgets[$index] = $this->widgets[$index - 1];
            $this->widgets[$index - 1] = $temp;
            
            // Update sort orders
            $this->updateSortOrders();
        }
    }

    /**
     * Move widget down in order
     */
    public function moveDown(int $index): void
    {
        if ($index < count($this->widgets) - 1) {
            $temp = $this->widgets[$index];
            $this->widgets[$index] = $this->widgets[$index + 1];
            $this->widgets[$index + 1] = $temp;
            
            // Update sort orders
            $this->updateSortOrders();
        }
    }

    /**
     * Update sort orders after reordering
     */
    private function updateSortOrders(): void
    {
        foreach ($this->widgets as $index => &$widget) {
            $widget['sort_order'] = $index + 1;
        }
    }

    /**
     * Reset to default settings
     */
    public function resetToDefaults(): void
    {
        $user = Auth::user();
        
        // Delete existing user settings
        UserWidgetSetting::where('user_id', $user->id)->delete();
        
        // Reload widgets with defaults
        $this->loadWidgets();
        
        session()->flash('message', 'Dashboard reset to default settings.');
    }

    /**
     * Save changes
     */
    public function saveChanges(): void
    {
        $user = Auth::user();

        foreach ($this->widgets as $widget) {
            if (!$widget['can_view']) {
                continue; // Skip widgets user can't view
            }

            UserWidgetSetting::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'widget_id' => $widget['id'],
                ],
                [
                    'visible' => $widget['visible'],
                    'size' => $widget['size'],
                    'sort_order' => $widget['sort_order'],
                ]
            );
        }

        $this->dispatch('widgets-updated');
        $this->closeModal();
        
        session()->flash('message', 'Dashboard settings saved successfully.');
    }

    /**
     * Get available sizes
     */
    public function getAvailableSizes(): array
    {
        return config('dashboard.sizes', [
            '1x1' => ['label' => 'Small'],
            '2x1' => ['label' => 'Wide'],
            '2x2' => ['label' => 'Medium'],
            '3x2' => ['label' => 'Large'],
            '3x3' => ['label' => 'Extra Large'],
        ]);
    }

    public function render()
    {
        return view('livewire.customize-dashboard', [
            'availableSizes' => $this->getAvailableSizes(),
        ]);
    }
}