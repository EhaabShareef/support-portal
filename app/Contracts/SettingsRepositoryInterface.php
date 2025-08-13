<?php

namespace App\Contracts;

use Illuminate\Support\Collection;
use App\Models\Setting;

interface SettingsRepositoryInterface
{
    /**
     * Retrieve all settings.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Retrieve settings for a given group.
     */
    public function group(string $group): Collection;

    /**
     * Get a setting value by key.
     */
    public function get(string $key, $default = null);

    /**
     * Create a new setting.
     */
    public function create(array $data): Setting;

    /**
     * Update an existing setting.
     */
    public function update(int $id, array $data): Setting;

    /**
     * Delete a setting.
     */
    public function delete(int $id): bool;

    /**
     * List all available groups.
     */
    public function groups(): array;

    /**
     * Clear cached settings.
     */
    public function clearCache(): void;
}
