<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingEmail extends Model
{
    protected $fillable = [
        'message_id',
        'from',
        'to',
        'subject',
        'body',
        'ticket_id',
        'user_id',
        'headers',
        'attachments',
        'processed_at',
    ];

    protected $casts = [
        'headers' => 'array',
        'attachments' => 'array',
        'processed_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
