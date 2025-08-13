<?php

namespace App\Services;

use App\Contracts\SettingsRepositoryInterface;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsRepository implements SettingsRepositoryInterface
{
    private const CACHE_KEY = 'app_settings_repository';
    private const CACHE_TTL = 3600; // 1 hour

    public function all(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::orderBy('group')->orderBy('key')->get();
        });
    }

    public function group(string $group): Collection
    {
        return $this->all()->where('group', $group);
    }

    public function get(string $key, $default = null)
    {
        $setting = $this->all()->firstWhere('key', $key);
        return $setting ? $setting->value : $default;
    }

    public function create(array $data): Setting
    {
        $setting = Setting::create($data);
        $this->clearCache();
        return $setting;
    }

    public function update(int $id, array $data): Setting
    {
        $setting = Setting::findOrFail($id);
        $setting->update($data);
        $this->clearCache();
        return $setting;
    }

    public function delete(int $id): bool
    {
        $setting = Setting::findOrFail($id);
        $result = $setting->delete();
        $this->clearCache();
        return $result;
    }

    public function groups(): array
    {
        return $this->all()->pluck('group')->unique()->sort()->values()->toArray();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // Backwards compatibility
    public function getAllSettings(): Collection
    {
        return $this->all();
    }

    public function getSettingsByGroup(string $group): Collection
    {
        return $this->group($group);
    }

    public function getSetting(string $key, $default = null)
    {
        return $this->get($key, $default);
    }

    public function createSetting(array $data): Setting
    {
        return $this->create($data);
    }

    public function updateSetting(int $id, array $data): Setting
    {
        return $this->update($id, $data);
    }

    public function deleteSetting(int $id): bool
    {
        return $this->delete($id);
    }

    public function getAvailableGroups(): array
    {
        return $this->groups();
    }
}