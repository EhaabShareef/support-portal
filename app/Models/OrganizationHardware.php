<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Ticket;

class OrganizationHardware extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organization_hardware';

    protected $fillable = [
        // Legacy fields (kept for compatibility)
        'asset_tag',
        'hardware_type',
        'serial_number',
        'purchase_date',
        'location',
        'last_maintenance',
        'next_maintenance',

        // New contract-first fields
        'organization_id',
        'contract_id',
        'hardware_type_id',
        'brand',
        'model',
        'quantity',
        'serial_required',
        'remarks',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'last_maintenance' => 'datetime',
        'next_maintenance' => 'datetime',
        'serial_required' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function contract()
    {
        return $this->belongsTo(OrganizationContract::class, 'contract_id');
    }

    public function type()
    {
        return $this->belongsTo(HardwareType::class, 'hardware_type_id');
    }

    public function serials()
    {
        return $this->hasMany(HardwareSerial::class, 'organization_hardware_id');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_organization_hardware')
            ->withPivot('maintenance_note')
            ->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-populate legacy hardware_type field based on hardware_type_id
            if ($model->hardware_type_id && !$model->hardware_type) {
                $hardwareType = HardwareType::find($model->hardware_type_id);
                if ($hardwareType) {
                    // Map hardware type names to enum values
                    $model->hardware_type = self::mapHardwareTypeToEnum($hardwareType->slug);
                }
            }
        });

        static::updating(function ($model) {
            // Auto-populate legacy hardware_type field based on hardware_type_id
            if ($model->hardware_type_id && !$model->hardware_type) {
                $hardwareType = HardwareType::find($model->hardware_type_id);
                if ($hardwareType) {
                    // Map hardware type names to enum values
                    $model->hardware_type = self::mapHardwareTypeToEnum($hardwareType->slug);
                }
            }
        });
    }

    protected static function mapHardwareTypeToEnum($slug)
    {
        // Map HardwareType slugs to enum values
        return match($slug) {
            'desktop_computer', 'desktop' => 'desktop',
            'laptop_computer', 'laptop' => 'laptop', 
            'server' => 'server',
            'printer' => 'printer',
            'monitor' => 'monitor',
            'network_equipment' => 'router', // closest match
            'mobile_device' => 'tablet',
            'storage_device' => 'storage',
            'peripheral' => 'other',
            default => 'other'
        };
    }
}
