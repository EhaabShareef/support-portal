<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $password
 * @property bool $active_yn
 * @property int|null $department_id
 * @property int|null $organization_id
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\Organization|null $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'username',
        'email',
        'email_verified_at',
        'password',
        'avatar',
        'is_active',
        'last_login_at',
        'timezone',
        'preferences',
        'department_id',
        'organization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'preferences' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = Str::uuid();
            }
        });
        
        static::created(function ($user) {
            // Automatically assign client role if no role is assigned
            if ($user->roles()->count() === 0) {
                $clientRole = \Spatie\Permission\Models\Role::where('name', 'client')->first();
                if ($clientRole) {
                    $user->assignRole($clientRole);
                }
            }
        });
    }
    
    // Hash Password if not already hashed
    public function setPasswordAttribute($value)
    {
        if (substr($value, 0, 4) === '$2y$') {
            $this->attributes['password'] = $value;
        } else {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    // Department Group (via Department)
    public function getDepartmentGroupAttribute()
    {
        return $this->department?->departmentGroup;
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'client_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'owner_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function createdSchedules()
    {
        return $this->hasMany(Schedule::class, 'created_by');
    }

    public function createdScheduleEventTypes()
    {
        return $this->hasMany(ScheduleEventType::class, 'created_by');
    }

    /**
     * Get the user's widget settings
     */
    public function widgetSettings()
    {
        return $this->hasMany(UserWidgetSetting::class);
    }

    /**
     * Get visible widgets for the user in order
     */
    public function getVisibleWidgets()
    {
        return $this->widgetSettings()
            ->with('widget')
            ->visible()
            ->ordered()
            ->get()
            ->filter(function ($setting) {
                return $setting->widget && $setting->widget->isVisibleForUser($this);
            });
    }

    /**
     * Check if user has admin role (workaround for teams configuration)
     */
    public function isAdmin(): bool
    {
        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->where('roles.name', 'admin')
            ->exists();
    }

    /**
     * Get the user's avatar URL or return null
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar && file_exists(public_path('storage/' . $this->avatar))) {
            return asset('storage/' . $this->avatar);
        }
        
        return null;
    }

    /**
     * Get user initials for avatar
     */
    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->name ?? 'User');
        if (count($names) >= 2) {
            return strtoupper(substr($names[0], 0, 1) . substr($names[1], 0, 1));
        }
        return strtoupper(substr($names[0] ?? 'U', 0, 2));
    }
}
