<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TicketMessageAttachment extends Model
{
    protected $fillable = [
        'ticket_message_id',
        'uuid',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected static function booted()
    {
        static::creating(function ($attachment) {
            if (empty($attachment->uuid)) {
                $attachment->uuid = (string) Str::uuid();
            }
        });
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }

    public function ticketMessage(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }
}
