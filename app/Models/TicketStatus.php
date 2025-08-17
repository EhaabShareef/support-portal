<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

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
        return static::active()->ordered()->pluck('name', 'key')->toArray();
    }

    public static function optionsForDepartmentGroup($departmentGroupId): array
    {
        return static::active()
            ->ordered()
            ->where(function ($query) use ($departmentGroupId) {
                $query->whereHas('departmentGroups', function ($q) use ($departmentGroupId) {
                    $q->where('department_group_id', $departmentGroupId);
                })->orWhereDoesntHave('departmentGroups');
            })
            ->pluck('name', 'key')
            ->toArray();
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
    }
}
