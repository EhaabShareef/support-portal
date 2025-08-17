<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class TicketStatus extends Model
{
    protected $fillable = [
        'name',
        'key',
        'description',
        'color',
        'sort_order',
        'is_protected',
        'is_active',
    ];

    protected $casts = [
        'is_protected' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'status', 'key');
    }

    public function departmentGroups(): BelongsToMany
    {
        return $this->belongsToMany(DepartmentGroup::class, 'department_group_ticket_status');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeForDepartmentGroup(Builder $query, $departmentGroupId): Builder
    {
        return $query->whereHas('departmentGroups', function ($q) use ($departmentGroupId) {
            $q->where('department_group_id', $departmentGroupId);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public static function options(): array
    {
        return Cache::remember('ticket_status_options', 3600, function () {
            return static::active()->ordered()->pluck('name', 'key')->toArray();
        });
    }

    public static function optionsForDepartmentGroup(int $departmentGroupId): array
    {
        return static::active()
            ->forDepartmentGroupOrUngrouped($departmentGroupId)
            ->ordered()
            ->pluck('name', 'key')
            ->toArray();
    }

    public function scopeForDepartmentGroupOrUngrouped(Builder $query, int $departmentGroupId): Builder
    {
        return $query->where(function ($builder) use ($departmentGroupId) {
            $builder->whereHas('departmentGroups', function ($q) use ($departmentGroupId) {
                $q->where('department_groups.id', $departmentGroupId);
            })->orWhereDoesntHave('departmentGroups');
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->key) {
                $model->key = str($model->name)->slug('_');
            }
        });

        static::deleting(function ($model) {
            if ($model->is_protected) {
                throw new \Exception('Cannot delete protected ticket status.');
            }
        });

        // Clear cache on model changes
        static::saved(function () {
            Cache::forget('ticket_status_options');
        });

        static::deleted(function () {
            Cache::forget('ticket_status_options');
        });
    }
}
