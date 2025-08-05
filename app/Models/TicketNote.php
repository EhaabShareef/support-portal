<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketNote extends Model
{
    public $timestamps = false; // Since you are using a custom timestamp column

    protected $fillable = [
        'ticket_id',
        'note',
        'user_id',
        'internal_yn',
        'color',
        'timestamp',
    ];

    // Ticket
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
