<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketCcRecipient extends Model
{
    protected $fillable = [
        'ticket_id',
        'email',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
