<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DepartmentGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function activeDepartments(): HasMany
    {
        return $this->departments()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function ticketStatuses(): BelongsToMany
    {
        return $this->belongsToMany(TicketStatus::class, 'department_group_ticket_status');
    }
}
