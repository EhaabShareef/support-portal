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

        // Get all active widgets that the user has permission to access
        $availableWidgets = DashboardWidget::active()
            ->forRole($role)
            ->ordered()
            ->get()
            ->filter(function ($widget) use ($user) {
                return $widget->isAccessibleForUser($user);
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
                'default_order' => $widget->sort_order,
                'available_sizes' => $widget->available_sizes,
                'visible' => $setting ? $setting->is_visible : true,
                'size' => $setting ? $setting->getEffectiveSize() : $widget->default_size,
                'sort_order' => $setting ? $setting->getEffectiveOrder() : $widget->sort_order,
                'can_view' => $widget->isAccessibleForUser($user),
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
        // Reset state
        $this->widgets = [];
        $this->userSettings = [];
        
        $this->showModal = true;
        $this->loadWidgets();
    }

    /**
     * Close the modal
     */
    public function closeModal(): void
    {
        $this->showModal = false;
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
        
        // Explicit authorization check - verify user can access dashboard
        if (!$user->can('dashboard.access')) {
            abort(403, 'Insufficient permissions to modify dashboard settings.');
        }
        
        // Verify user's role-specific dashboard permission
        $userRole = $user->roles->first()?->name ?? 'client';
        $requiredPermission = "dashboard.{$userRole}";
        if (!$user->can($requiredPermission)) {
            abort(403, 'Insufficient role permissions to modify dashboard settings.');
        }
        
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
        
        // Explicit authorization check - verify user can access dashboard
        if (!$user->can('dashboard.access')) {
            abort(403, 'Insufficient permissions to modify dashboard settings.');
        }
        
        // Verify user's role-specific dashboard permission
        $userRole = $user->roles->first()?->name ?? 'client';
        $requiredPermission = "dashboard.{$userRole}";
        if (!$user->can($requiredPermission)) {
            abort(403, 'Insufficient role permissions to modify dashboard settings.');
        }

        foreach ($this->widgets as $widget) {
            if (!$widget['can_view']) {
                continue; // Skip widgets user can't view
            }
            
            // Additional server-side validation: verify widget is accessible for user's role
            $dbWidget = DashboardWidget::find($widget['id']);
            if (!$dbWidget || !$dbWidget->isAccessibleForUser($user)) {
                continue; // Skip widgets that are not accessible
            }

            // Validate size is available for this widget
            $size = $widget['size'];
            if (!in_array($size, $widget['available_sizes'])) {
                $size = $widget['default_size'] ?? '2x2';
            }

            UserWidgetSetting::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'widget_id' => $widget['id'],
                ],
                [
                    'is_visible' => (bool) $widget['visible'],
                    'size' => $size,
                    'sort_order' => (int) ($widget['sort_order'] ?? 0),
                ]
            );
        }

        // Refresh the widget data after save
        $this->loadWidgets();
        
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