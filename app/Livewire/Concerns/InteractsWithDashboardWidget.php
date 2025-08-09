<?php

namespace App\Livewire\Concerns;

use App\Contracts\DashboardWidgetInterface;
use App\Models\DashboardWidget;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

trait InteractsWithDashboardWidget
{
    public string $widgetKey;
    public string $size;
    public array $options = [];
    public bool $dataLoaded = false;
    public bool $hasError = false;
    public ?string $errorMessage = null;

    /**
     * Mount the widget component
     */
    public function mountWidget(string $widgetKey, string $size = '1x1', array $options = []): void
    {
        $this->widgetKey = $widgetKey;
        $this->size = $size;
        $this->options = $options;

        // Check permissions
        $widget = DashboardWidget::where('key', $widgetKey)->first();
        if ($widget && $widget->permission) {
            $this->authorize($widget->permission);
        }

        // Check if user can view this widget
        if (!$this->canView(Auth::user())) {
            abort(403, 'You do not have permission to view this widget.');
        }
    }

    /**
     * Get the widget key
     */
    public function getWidgetKey(): string
    {
        return $this->widgetKey;
    }

    /**
     * Get the current size
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * Set the widget size
     */
    public function setSize(string $size): void
    {
        $this->size = $size;
    }

    /**
     * Get widget options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set widget options
     */
    public function setOptions(array $options): void
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Check if user can view this widget
     */
    public function canView(User $user): bool
    {
        $widget = DashboardWidget::where('key', $this->widgetKey)->first();
        
        if (!$widget) {
            return false;
        }

        return $widget->isVisibleForUser($user);
    }

    /**
     * Load widget data (to be overridden by concrete implementations)
     */
    public function loadData(): void
    {
        try {
            $this->performDataLoad();
            $this->dataLoaded = true;
            $this->hasError = false;
            $this->errorMessage = null;
        } catch (\Exception $e) {
            $this->hasError = true;
            $this->errorMessage = 'Failed to load widget data';
            logger()->error("Widget {$this->widgetKey} failed to load data", [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    /**
     * Perform the actual data loading (to be implemented by widgets)
     */
    protected function performDataLoad(): void
    {
        // Override this method in concrete widget implementations
    }

    /**
     * Check if data is loaded
     */
    public function isDataLoaded(): bool
    {
        return $this->dataLoaded;
    }

    /**
     * Refresh widget data
     */
    public function refreshData(): void
    {
        $this->dataLoaded = false;
        $this->loadData();
        $this->dispatch('widgetRefreshed', $this->widgetKey);
    }

    /**
     * Get CSS classes for the widget container
     */
    public function getContainerClasses(): string
    {
        $sizeClasses = [
            '1x1' => 'col-span-1 row-span-1',
            '2x1' => 'col-span-1 md:col-span-2 row-span-1',
            '2x2' => 'col-span-1 md:col-span-2 row-span-2',
            '3x2' => 'col-span-1 md:col-span-2 lg:col-span-3 row-span-2',
            '3x3' => 'col-span-1 md:col-span-2 lg:col-span-3 row-span-3',
        ];

        $sizeClass = $sizeClasses[$this->size] ?? $sizeClasses['1x1'];

        return "dashboard-widget {$sizeClass} rounded-lg shadow-md bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20";
    }

    /**
     * Listeners for widget events
     */
    protected function getListeners(): array
    {
        return [
            'refreshAllWidgets' => 'refreshData',
            "refreshWidget.{$this->widgetKey}" => 'refreshData',
        ];
    }
}