<?php

namespace App\Contracts;

use App\Models\User;

interface DashboardWidgetInterface
{
    /**
     * Get the widget key that matches dashboard_widgets.key
     */
    public function getWidgetKey(): string;

    /**
     * Get the current size of the widget
     */
    public function getSize(): string;

    /**
     * Set the size of the widget
     */
    public function setSize(string $size): void;

    /**
     * Get widget options
     */
    public function getOptions(): array;

    /**
     * Set widget options
     */
    public function setOptions(array $options): void;

    /**
     * Check if the current user can view this widget
     */
    public function canView(User $user): bool;

    /**
     * Load widget data (lazy loading method)
     */
    public function loadData(): void;

    /**
     * Check if widget data is loaded
     */
    public function isDataLoaded(): bool;

    /**
     * Refresh widget data
     */
    public function refreshData(): void;
}