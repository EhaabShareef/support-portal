<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleEventType extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'description',
        'color',
        'tailwind_classes',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'event_type_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    // Accessors & Mutators
    public function getTailwindClassesAttribute(): string
    {
        return $this->attributes['tailwind_classes'] ?? 'bg-blue-500 text-white border-blue-600';
    }

    public function getColorStyleAttribute(): string
    {
        // Fallback for custom colors not covered by Tailwind
        if (str_starts_with($this->color, '#')) {
            return "background-color: {$this->color}; border-color: {$this->color};";
        }
        
        return '';
    }

    // Helper methods
    public static function getDefault(): ?ScheduleEventType
    {
        return static::where('label', 'Office Support')->first() ?? static::active()->first();
    }

    public static function options(): array
    {
        return static::active()
                     ->ordered()
                     ->get()
                     ->pluck('label', 'id')
                     ->toArray();
    }
}
