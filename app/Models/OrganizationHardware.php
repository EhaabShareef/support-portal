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
        'asset_tag',
        'organization_id',
        'contract_id',
        'hardware_type',
        'brand',
        'model',
        'serial_number',
        'specifications',
        'purchase_date',
        'purchase_price',
        'warranty_start',
        'warranty_expiration',
        'status',
        'location',
        'remarks',
        'custom_fields',
        'last_maintenance',
        'next_maintenance',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'warranty_start' => 'date',
        'warranty_expiration' => 'date',
        'custom_fields' => 'array',
        'last_maintenance' => 'datetime',
        'next_maintenance' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function contract()
    {
        return $this->belongsTo(OrganizationContract::class, 'contract_id');
    }
}
