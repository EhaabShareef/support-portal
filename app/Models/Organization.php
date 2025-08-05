<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Organization
 *
 * @property int $id
 * @property string $name
 * @property string $company
 * @property string $company_contact
 * @property string $tin_no
 * @property string $email
 * @property string $phone
 * @property bool $active_yn
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrganizationContract> $contracts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrganizationHardware> $hardware
 */
class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'company',
        'company_contact',
        'tin_no',
        'email',
        'phone',
        'is_active',
        'subscription_status',
        'notes',
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => true,
        'subscription_status' => 'trial',
    ];

    public function contracts(): HasMany
    {
        return $this->hasMany(OrganizationContract::class, 'organization_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'organization_id');
    }

    public function hardware(): HasMany
    {
        return $this->hasMany(OrganizationHardware::class, 'organization_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'organization_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySubscriptionStatus($query, $status)
    {
        return $query->where('subscription_status', $status);
    }

    // Accessors & Mutators
    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function getSubscriptionStatusLabelAttribute()
    {
        return match ($this->subscription_status) {
            'trial' => 'Trial',
            'active' => 'Active',
            'suspended' => 'Suspended',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }

    // Helper methods
    public function canBeDeleted(): bool
    {
        return $this->users()->count() === 0 &&
               $this->tickets()->count() === 0 &&
               $this->contracts()->count() === 0;
    }
}
