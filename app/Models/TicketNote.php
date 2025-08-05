<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'note',
        'user_id',
        'is_internal',
        'color',
        'type',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
