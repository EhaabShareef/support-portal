<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationSubscriptionStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label', 
        'color',
        'sort_order',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'subscription_status', 'key');
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
    public function getColorStyleAttribute(): string
    {
        // Fallback for custom colors not covered by Tailwind
        if (str_starts_with($this->color, '#')) {
            return "background-color: {$this->color}; border-color: {$this->color};";
        }
        
        return '';
    }

    // Helper methods
    public static function getDefault(): ?OrganizationSubscriptionStatus
    {
        return static::where('key', 'active')->first() ?? static::active()->first();
    }

    public static function options(): array
    {
        return static::active()
                     ->ordered()
                     ->get()
                     ->pluck('label', 'key')
                     ->toArray();
    }
}