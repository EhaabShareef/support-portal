<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HardwareSerial extends Model
{
    use HasFactory;

    protected $fillable = ['organization_hardware_id', 'serial', 'notes'];

    public function hardware()
    {
        return $this->belongsTo(OrganizationHardware::class, 'organization_hardware_id');
    }
}
