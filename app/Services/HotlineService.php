<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HotlineService
{
    private const CACHE_KEY = 'support_hotlines';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get all hotline numbers
     */
    public function getHotlines(): Collection
    {
        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
                $setting = Setting::where('key', 'support_hotlines')->first();
                
                if (!$setting || !$setting->value) {
                    return collect($this->getDefaultHotlines());
                }

                $hotlines = $setting->value;
                
                if (!is_array($hotlines)) {
                    Log::warning('Invalid hotlines setting format, using defaults');
                    return collect($this->getDefaultHotlines());
                }

                return collect($hotlines)
                    ->filter(fn($hotline) => $hotline['is_active'] ?? false)
                    ->sortBy('sort_order');
            });
        } catch (\Exception $e) {
            Log::error('Failed to load hotlines', ['error' => $e->getMessage()]);
            return collect($this->getDefaultHotlines());
        }
    }

    /**
     * Get a specific hotline by key
     */
    public function getHotline(string $key): ?array
    {
        return $this->getHotlines()->get($key);
    }

    /**
     * Get hotline numbers as a formatted string for display
     */
    public function getHotlinesText(): string
    {
        $hotlines = $this->getHotlines();
        
        if ($hotlines->isEmpty()) {
            return 'Please contact your support team for assistance.';
        }

        $lines = $hotlines->map(function ($hotline, $key) {
            return "• {$hotline['name']}: {$hotline['number']} ({$hotline['description']})";
        });

        return $lines->join("\n");
    }

    /**
     * Update hotlines setting
     */
    public function updateHotlines(array $hotlines): bool
    {
        try {
            Setting::updateOrCreate(
                ['key' => 'support_hotlines'],
                [
                    'value' => json_encode($hotlines),
                    'type' => 'json',
                    'label' => 'Support Hotline Numbers',
                    'description' => 'Technical support hotline numbers for different systems',
                    'group' => 'support',
                    'validation_rules' => ['required', 'json'],
                    'is_public' => true,
                    'is_encrypted' => false,
                ]
            );

            $this->clearCache();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update hotlines', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Clear hotlines cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get default hotlines structure
     */
    private function getDefaultHotlines(): array
    {
        return [
            'pms_hotline' => [
                'name' => 'PMS Hotline',
                'number' => '+1-800-PMS-HELP',
                'description' => 'Property Management System technical support',
                'is_active' => true,
                'sort_order' => 1,
            ],
            'pos_hotline' => [
                'name' => 'POS Hotline', 
                'number' => '+1-800-POS-HELP',
                'description' => 'Point of Sale system technical support',
                'is_active' => true,
                'sort_order' => 2,
            ],
            'mc_hotline' => [
                'name' => 'MC Hotline',
                'number' => '+1-800-MC-HELP',
                'description' => 'Management Center technical support',
                'is_active' => true,
                'sort_order' => 3,
            ],
            'manager_on_duty' => [
                'name' => 'Manager on Duty',
                'number' => '+1-800-MOD-HELP',
                'description' => 'Emergency escalation and management assistance',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];
    }

    /**
     * Validate hotline data structure
     */
    public function validateHotline(array $hotline): array
    {
        $errors = [];

        if (empty($hotline['name'])) {
            $errors[] = 'Hotline name is required';
        }

        if (empty($hotline['number'])) {
            $errors[] = 'Hotline number is required';
        }

        if (empty($hotline['description'])) {
            $errors[] = 'Hotline description is required';
        }

        if (!isset($hotline['is_active'])) {
            $hotline['is_active'] = true;
        }

        if (!isset($hotline['sort_order'])) {
            $hotline['sort_order'] = 1;
        }

        return $errors;
    }

    /**
     * Get hotlines for admin interface
     */
    public function getHotlinesForAdmin(): array
    {
        try {
            $setting = Setting::where('key', 'support_hotlines')->first();
            
            if (!$setting || !$setting->value) {
                return $this->getDefaultHotlines();
            }

            $hotlines = $setting->value;
            return is_array($hotlines) ? $hotlines : $this->getDefaultHotlines();
        } catch (\Exception $e) {
            Log::error('Failed to load hotlines for admin', ['error' => $e->getMessage()]);
            return $this->getDefaultHotlines();
        }
    }
}