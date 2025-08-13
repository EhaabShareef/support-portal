<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_public',
        'is_encrypted',
        'validation_rules',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_encrypted' => 'boolean',
        'validation_rules' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        // Clear cache when settings are modified
        static::saved(function () {
            Cache::forget('app_settings');
        });

        static::deleted(function () {
            Cache::forget('app_settings');
        });
    }

    /**
     * Get setting value with proper type casting
     */
    public function getValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            $value = Crypt::decryptString($value);
        }

        return match ($this->type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            'array' => json_decode($value, true) ?? [],
            default => $value,
        };
    }

    /**
     * Set setting value with proper type handling
     */
    public function setValueAttribute($value)
    {
        if (in_array($this->type, ['json', 'array'])) {
            $value = json_encode($value);
        }

        if ($this->is_encrypted && $value) {
            $value = Crypt::encryptString($value);
        }

        $this->attributes['value'] = $value;
    }

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $settings = Cache::remember('app_settings', 3600, function () {
            return static::all()->keyBy('key');
        });

        $setting = $settings->get($key);

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'string', string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'label' => ucwords(str_replace(['_', '-'], ' ', $key)),
                'group' => $group,
            ]
        );
    }

    /**
     * Get settings by group
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)->get()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        })->toArray();
    }

    /**
     * Scope for public settings
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get all available groups
     */
    public static function getGroups(): array
    {
        return static::distinct('group')->pluck('group')->sort()->values()->toArray();
    }

    /**
     * Get validation rules for this setting
     */
    public function getValidationRulesString(): string
    {
        if (!$this->validation_rules) {
            return '';
        }

        return collect($this->validation_rules)->implode('|');
    }
}