<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationContract extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'organization_contracts';

    protected $fillable = [
        'contract_number',
        'organization_id',
        'department_id',
        'type',
        'status',
        'includes_hardware',
        'is_oracle',
        'csi_number',
        'start_date',
        'end_date',
        'renewal_months',
        'csi_remarks',
        'notes',
        'service_levels',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'includes_hardware' => 'boolean',
        'is_oracle' => 'boolean',
        'service_levels' => 'array',
    ];

    /**
     * The organization this contract belongs to.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function hardware()
    {
        return $this->hasMany(OrganizationHardware::class, 'contract_id');
    }
}
