<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'sender_id',
        'message',
        'is_internal',
        'is_system_message',
        'metadata',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'is_system_message' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($message) {
            // Update the ticket's latest_message_at timestamp
            if ($message->ticket) {
                $message->ticket->update([
                    'latest_message_at' => $message->created_at
                ]);
            }
        });
    }

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

    // Attachments
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
