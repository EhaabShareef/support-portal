<?php

namespace App\Services;

use App\Models\Setting;
use App\Contracts\SettingsRepositoryInterface;
use App\Models\TicketStatus as TicketStatusModel;
use Illuminate\Support\Facades\Cache;

class TicketColorService
{
    /**
     * Default status color mappings with background and text colors
     */
    private const DEFAULT_STATUS_COLORS = [
        'open' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
        'in_progress' => ['bg' => '#fed7aa', 'text' => '#c2410c'],
        'waiting' => ['bg' => '#fef3c7', 'text' => '#a16207'],
        'resolved' => ['bg' => '#dcfce7', 'text' => '#166534'],
        'closed' => ['bg' => '#f3f4f6', 'text' => '#374151'],
        'cancelled' => ['bg' => '#fecaca', 'text' => '#dc2626'],
    ];

    /**
     * Default priority color mappings with background and text colors
     */
    private const DEFAULT_PRIORITY_COLORS = [
        'low' => ['bg' => '#f3f4f6', 'text' => '#374151'],
        'normal' => ['bg' => '#dbeafe', 'text' => '#1e40af'],
        'high' => ['bg' => '#fed7aa', 'text' => '#c2410c'],
        'urgent' => ['bg' => '#fecaca', 'text' => '#dc2626'],
        'critical' => ['bg' => '#fef2f2', 'text' => '#991b1b'],
    ];

    /**
     * Default hex color palette
     */
    private const DEFAULT_COLOR_PALETTE = [
        '#6b7280', // gray
        '#ef4444', // red
        '#f97316', // orange
        '#eab308', // yellow
        '#22c55e', // green
        '#3b82f6', // blue
        '#6366f1', // indigo
        '#a855f7', // purple
        '#ec4899', // pink
        '#0ea5e9', // sky
        '#14b8a6', // teal
        '#10b981', // emerald
        '#84cc16', // lime
        '#f59e0b', // amber
        '#f43f5e', // rose
        '#8b5cf6', // violet
        '#d946ef', // fuchsia
        '#06b6d4', // cyan
        '#475569', // slate
        '#71717a', // zinc
        '#52525b', // neutral
        '#78716c', // stone
    ];

    /**
     * Generate CSS classes from background and text colors
     */
    private function generateCssClasses(array $colors): array
    {
        $bgColor = $colors['bg'] ?? '#f3f4f6';
        $textColor = $colors['text'] ?? '#374151';
        
        return [
            'bg' => "background-color: {$bgColor};",
            'text' => "color: {$textColor};",
            'dark_bg' => "background-color: {$bgColor};",
            'dark_text' => "color: {$textColor};",
            'bg_hex' => $bgColor,
            'text_hex' => $textColor
        ];
    }

    /**
     * Convert hex color to RGB
     */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Check if color is light
     */
    private function isLightColor(array $rgb): bool
    {
        $brightness = ($rgb['r'] * 299 + $rgb['g'] * 587 + $rgb['b'] * 114) / 1000;
        return $brightness > 128;
    }

    /**
     * Adjust color brightness
     */
    private function adjustBrightness(string $hex, float $factor): string
    {
        $rgb = $this->hexToRgb($hex);
        $rgb['r'] = max(0, min(255, round($rgb['r'] * $factor)));
        $rgb['g'] = max(0, min(255, round($rgb['g'] * $factor)));
        $rgb['b'] = max(0, min(255, round($rgb['b'] * $factor)));
        
        return sprintf("#%02x%02x%02x", $rgb['r'], $rgb['g'], $rgb['b']);
    }

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
        return self::DEFAULT_COLOR_PALETTE;
    }

    /**
     * Get available color palette with values for dropdowns
     */
    public function getColorPaletteWithValues(): array
    {
        $palette = [];
        foreach (self::DEFAULT_COLOR_PALETTE as $hex) {
            $palette[$hex] = $hex;
        }
        return $palette;
    }

    /**
     * Get color details for a specific color configuration
     */
    public function getColorDetails($colors): array
    {
        if (is_string($colors)) {
            // Backward compatibility: convert single hex to array format
            $colors = ['bg' => $colors, 'text' => $this->getContrastColor($colors)];
        }
        return $this->generateCssClasses($colors);
    }

    /**
     * Get contrasting text color for a background color
     */
    public function getContrastColor(string $bgColor): string
    {
        $rgb = $this->hexToRgb($bgColor);
        $brightness = ($rgb['r'] * 299 + $rgb['g'] * 587 + $rgb['b'] * 114) / 1000;
        return $brightness > 128 ? '#1f2937' : '#f9fafb';
    }

    /**
     * Get default status color
     */
    private function getDefaultStatusColor(string $status): array
    {
        return self::DEFAULT_STATUS_COLORS[$status] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
    }

    /**
     * Get default priority color
     */
    private function getDefaultPriorityColor(string $priority): array
    {
        return self::DEFAULT_PRIORITY_COLORS[$priority] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
    }

    /**
     * Build CSS classes string from color configuration
     */
    private function buildCssClasses($colors): string
    {
        $colorDetails = $this->getColorDetails($colors);
        
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
    public function getPreviewClasses($colors): string
    {
        $colorDetails = $this->getColorDetails($colors);
        
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