<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationHardware extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organization_hardware';

    protected $fillable = [
        // Legacy fields (deprecated in UI)
        'asset_tag',
        'hardware_type',
        'serial_number',
        'specifications',
        'purchase_date',
        'purchase_price',
        'warranty_start',
        'warranty_expiration',
        'status',
        'location',
        'last_maintenance',
        'next_maintenance',

        // New simplified contract-first fields
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
        'purchase_price' => 'decimal:2',
        'warranty_start' => 'date',
        'warranty_expiration' => 'date',
        'custom_fields' => 'array',
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
}
