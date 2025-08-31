<?php

namespace App\Services;

use App\Models\Setting;
use App\Contracts\SettingsRepositoryInterface;
use App\Models\TicketStatus as TicketStatusModel;
use Illuminate\Support\Facades\Cache;

class TicketColorService
{
    /**
     * Default priority color mappings
     */
    private const DEFAULT_PRIORITY_COLORS = [
        'low' => 'gray',
        'normal' => 'blue',
        'high' => 'orange',
        'urgent' => 'red',
        'critical' => 'red',
    ];

    /**
     * Get CSS classes for a ticket status
     */
    public function getStatusClasses(string $status): string
    {
        $colors = $this->getStatusColors();
        $colorName = $colors[$status] ?? $this->getDefaultStatusColor($status);
        
        return $this->buildCssClasses($colorName);
    }

    /**
     * Get CSS classes for a ticket priority
     */
    public function getPriorityClasses(string $priority): string
    {
        $colors = $this->getPriorityColors();
        $colorName = $colors[$priority] ?? $this->getDefaultPriorityColor($priority);
        
        return $this->buildCssClasses($colorName);
    }

    /**
     * Get all status color mappings from settings (legacy) and database
     */
    public function getStatusColors(): array
    {
        return Cache::remember('ticket_status_colors', 3600, function () {
            // First get dynamic statuses from database
            $dynamicStatusColors = [];
            $ticketStatuses = TicketStatusModel::active()->get();
            foreach ($ticketStatuses as $status) {
                $dynamicStatusColors[$status->key] = $status->color;
            }

            // Then get legacy settings-based colors
            $settingsValue = app(SettingsRepositoryInterface::class)->get('ticket_status_colors', self::DEFAULT_STATUS_COLORS);
            
            // Ensure we always return an array
            if (is_string($settingsValue)) {
                $decoded = json_decode($settingsValue, true);
                $settingsColors = is_array($decoded) ? $decoded : self::DEFAULT_STATUS_COLORS;
            } else {
                $settingsColors = is_array($settingsValue) ? $settingsValue : self::DEFAULT_STATUS_COLORS;
            }

            // Merge dynamic colors (priority) with settings colors (fallback)
            return array_merge($settingsColors, $dynamicStatusColors);
        });
    }

    /**
     * Get all priority color mappings from settings
     */
    public function getPriorityColors(): array
    {
        return Cache::remember('ticket_priority_colors', 3600, function () {
            $value = app(SettingsRepositoryInterface::class)->get('ticket_priority_colors', self::DEFAULT_PRIORITY_COLORS);
            
            // Ensure we always return an array
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : self::DEFAULT_PRIORITY_COLORS;
            }
            
            return is_array($value) ? $value : self::DEFAULT_PRIORITY_COLORS;
        });
    }

    /**
     * Update status colors setting (legacy method)
     */
    public function updateStatusColors(array $colors): void
    {
        Setting::set('ticket_status_colors', $colors, 'json', 'ticket');
        Cache::forget('ticket_status_colors');
    }

    /**
     * Set color for a specific status
     */
    public function setStatusColor(string $statusKey, string $color): void
    {
        // Update the ticket status model if it exists
        $ticketStatus = TicketStatusModel::where('key', $statusKey)->first();
        if ($ticketStatus) {
            $ticketStatus->update(['color' => $color]);
        }
        
        // Also update the legacy settings for backward compatibility
        $statusColors = $this->getStatusColors();
        $statusColors[$statusKey] = $color;
        $this->updateStatusColors($statusColors);
        
        Cache::forget('ticket_status_colors');
    }

    /**
     * Get color for a specific status
     */
    public function getStatusColor(string $statusKey): string
    {
        $colors = $this->getStatusColors();
        return $colors[$statusKey] ?? $this->getDefaultStatusColor($statusKey);
    }

    /**
     * Update priority colors setting
     */
    public function updatePriorityColors(array $colors): void
    {
        Setting::set('ticket_priority_colors', $colors, 'json', 'ticket');
        Cache::forget('ticket_priority_colors');
    }

    /**
     * Get available color palette
     */
    public function getColorPalette(): array
    {
        return array_keys(self::COLOR_PALETTE);
    }

    /**
     * Get available color palette with values for dropdowns
     */
    public function getColorPaletteWithValues(): array
    {
        return array_combine(
            array_keys(self::COLOR_PALETTE),
            array_keys(self::COLOR_PALETTE)
        );
    }

    /**
     * Get color details for a specific color name
     */
    public function getColorDetails(string $colorName): array
    {
        return self::COLOR_PALETTE[$colorName] ?? self::COLOR_PALETTE['gray'];
    }

    /**
     * Get default status color
     */
    private function getDefaultStatusColor(string $status): string
    {
        return self::DEFAULT_STATUS_COLORS[$status] ?? 'gray';
    }

    /**
     * Get default priority color
     */
    private function getDefaultPriorityColor(string $priority): string
    {
        return self::DEFAULT_PRIORITY_COLORS[$priority] ?? 'gray';
    }

    /**
     * Build CSS classes string from color name
     */
    private function buildCssClasses(string $colorName): string
    {
        $colorDetails = $this->getColorDetails($colorName);
        
        return implode(' ', [
            $colorDetails['bg'],
            $colorDetails['text'],
            $colorDetails['dark_bg'],
            $colorDetails['dark_text']
        ]);
    }

    /**
     * Get preview classes for admin interface (without dark mode classes)
     */
    public function getPreviewClasses(string $colorName): string
    {
        $colorDetails = $this->getColorDetails($colorName);
        
        return $colorDetails['bg'] . ' ' . $colorDetails['text'];
    }

    /**
     * Reset status colors to defaults
     */
    public function resetStatusColorsToDefaults(): void
    {
        // Reset both database ticket statuses and settings
        $ticketStatuses = TicketStatusModel::all();
        foreach ($ticketStatuses as $status) {
            $defaultColor = self::DEFAULT_STATUS_COLORS[$status->key] ?? '#6b7280';
            $status->update(['color' => $defaultColor]);
        }
        
        $this->updateStatusColors(self::DEFAULT_STATUS_COLORS);
    }

    /**
     * Clear the ticket status colors cache
     */
    public function clearStatusColorsCache(): void
    {
        Cache::forget('ticket_status_colors');
    }

    /**
     * Reset priority colors to defaults
     */
    public function resetPriorityColorsToDefaults(): void
    {
        $this->updatePriorityColors(self::DEFAULT_PRIORITY_COLORS);
    }

    /**
     * Get all default colors for seeding
     */
    public static function getDefaultSettings(): array
    {
        return [
            [
                'key' => 'ticket_status_colors',
                'value' => json_encode(self::DEFAULT_STATUS_COLORS),
                'type' => 'json',
                'label' => 'Ticket Status Colors',
                'description' => 'Color configuration for ticket status badges',
                'group' => 'ticket',
                'validation_rules' => json_encode(['required', 'json']),
                'is_public' => false,
                'is_encrypted' => false,
            ],
            [
                'key' => 'ticket_priority_colors',
                'value' => json_encode(self::DEFAULT_PRIORITY_COLORS),
                'type' => 'json',
                'label' => 'Ticket Priority Colors',
                'description' => 'Color configuration for ticket priority badges',
                'group' => 'ticket',
                'validation_rules' => json_encode(['required', 'json']),
                'is_public' => false,
                'is_encrypted' => false,
            ],
        ];
    }
}