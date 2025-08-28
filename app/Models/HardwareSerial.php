<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Ticket;

class HardwareSerial extends Model
{
    use HasFactory;

    protected $fillable = ['organization_hardware_id', 'serial', 'notes'];

    public function hardware()
    {
        return $this->belongsTo(OrganizationHardware::class, 'organization_hardware_id');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_hardware_serial')
            ->withTimestamps();
    }
}
