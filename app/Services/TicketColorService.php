<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class TicketColorService
{
    /**
     * Available color palette mapping simple names to Tailwind classes
     */
    private const COLOR_PALETTE = [
        'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'dark_bg' => 'dark:bg-red-900/40', 'dark_text' => 'dark:text-red-300'],
        'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'dark_bg' => 'dark:bg-blue-900/40', 'dark_text' => 'dark:text-blue-300'],
        'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'dark_bg' => 'dark:bg-green-900/40', 'dark_text' => 'dark:text-green-300'],
        'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'dark_bg' => 'dark:bg-yellow-900/40', 'dark_text' => 'dark:text-yellow-200'],
        'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'dark_bg' => 'dark:bg-orange-900/40', 'dark_text' => 'dark:text-orange-200'],
        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'dark_bg' => 'dark:bg-purple-900/40', 'dark_text' => 'dark:text-purple-200'],
        'pink' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-800', 'dark_bg' => 'dark:bg-pink-900/40', 'dark_text' => 'dark:text-pink-100'],
        'gray' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'dark_bg' => 'dark:bg-gray-900/40', 'dark_text' => 'dark:text-gray-200'],
        'teal' => ['bg' => 'bg-teal-100', 'text' => 'text-teal-800', 'dark_bg' => 'dark:bg-teal-900/40', 'dark_text' => 'dark:text-teal-200'],
        'cyan' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-800', 'dark_bg' => 'dark:bg-cyan-900/40', 'dark_text' => 'dark:text-cyan-100'],
        'lime' => ['bg' => 'bg-lime-100', 'text' => 'text-lime-800', 'dark_bg' => 'dark:bg-lime-900/40', 'dark_text' => 'dark:text-lime-100'],
        'emerald' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'dark_bg' => 'dark:bg-emerald-900/40', 'dark_text' => 'dark:text-emerald-900'],
    ];

    /**
     * Default status color mappings
     */
    private const DEFAULT_STATUS_COLORS = [
        'open' => 'lime',
        'in_progress' => 'blue',
        'awaiting_customer_response' => 'yellow',
        'awaiting_case_closure' => 'cyan',
        'sales_engagement' => 'pink',
        'monitoring' => 'teal',
        'solution_provided' => 'emerald',
        'closed' => 'red',
        'on_hold' => 'gray',
    ];

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
     * Get all status color mappings from settings
     */
    public function getStatusColors(): array
    {
        return Cache::remember('ticket_status_colors', 3600, function () {
            $value = Setting::get('ticket_status_colors', self::DEFAULT_STATUS_COLORS);
            
            // Ensure we always return an array
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : self::DEFAULT_STATUS_COLORS;
            }
            
            return is_array($value) ? $value : self::DEFAULT_STATUS_COLORS;
        });
    }

    /**
     * Get all priority color mappings from settings
     */
    public function getPriorityColors(): array
    {
        return Cache::remember('ticket_priority_colors', 3600, function () {
            $value = Setting::get('ticket_priority_colors', self::DEFAULT_PRIORITY_COLORS);
            
            // Ensure we always return an array
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : self::DEFAULT_PRIORITY_COLORS;
            }
            
            return is_array($value) ? $value : self::DEFAULT_PRIORITY_COLORS;
        });
    }

    /**
     * Update status colors setting
     */
    public function updateStatusColors(array $colors): void
    {
        Setting::set('ticket_status_colors', $colors, 'json');
        Cache::forget('ticket_status_colors');
    }

    /**
     * Update priority colors setting
     */
    public function updatePriorityColors(array $colors): void
    {
        Setting::set('ticket_priority_colors', $colors, 'json');
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
        $this->updateStatusColors(self::DEFAULT_STATUS_COLORS);
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
                'group' => 'ticket_colors',
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
                'group' => 'ticket_colors',
                'validation_rules' => json_encode(['required', 'json']),
                'is_public' => false,
                'is_encrypted' => false,
            ],
        ];
    }
}