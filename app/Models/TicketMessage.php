<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketMessage extends Model
{
    public $timestamps = false; // Since you are using a custom timestamp column

    protected $fillable = [
        'ticket_id',
        'sender_id',
        'message',
        'timestamp',
    ];

    // Ticket
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // Sender (User)
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
