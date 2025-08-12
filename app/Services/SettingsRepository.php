<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsRepository
{
    private const CACHE_KEY = 'app_settings_repository';
    private const CACHE_TTL = 3600; // 1 hour

    public function getAllSettings(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::orderBy('group')->orderBy('key')->get();
        });
    }

    public function getSettingsByGroup(string $group): Collection
    {
        return $this->getAllSettings()->where('group', $group);
    }

    public function getSetting(string $key, $default = null)
    {
        $setting = $this->getAllSettings()->firstWhere('key', $key);
        return $setting ? $setting->value : $default;
    }

    public function createSetting(array $data): Setting
    {
        $setting = Setting::create($data);
        $this->clearCache();
        return $setting;
    }

    public function updateSetting(int $id, array $data): Setting
    {
        $setting = Setting::findOrFail($id);
        $setting->update($data);
        $this->clearCache();
        return $setting;
    }

    public function deleteSetting(int $id): bool
    {
        $setting = Setting::findOrFail($id);
        $result = $setting->delete();
        $this->clearCache();
        return $result;
    }

    public function getAvailableGroups(): array
    {
        return $this->getAllSettings()->pluck('group')->unique()->sort()->values()->toArray();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}