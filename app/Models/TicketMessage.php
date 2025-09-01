<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TicketMessageAttachment;

class TicketMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'sender_id',
        'message',
        'is_internal',
        'is_system_message',
        'is_log',
        'metadata',
// In 2025_01_01_000029_add_email_fields_to_tickets_and_messages.php

public function up(): void
{
    Schema::table('tickets', function (Blueprint $table) {
        $table->string('email_thread_id')->nullable()->after('latest_message_at');
        $table->string('email_reply_address')->nullable()->after('email_thread_id');
        $table->index('email_thread_id', 'idx_tickets_email_thread');
        $table->unique('email_reply_address', 'uq_tickets_email_reply_address');
    });

    Schema::table('ticket_messages', function (Blueprint $table) {
        $table->string('email_message_id')->nullable()->after('metadata');
        $table->string('email_in_reply_to')->nullable()->after('email_message_id');
        $table->text('email_references')->nullable()->after('email_in_reply_to');
        $table->json('email_headers')->nullable()->after('email_references');
        $table->index('email_message_id', 'idx_ticket_messages_email_message_id');
        $table->index('email_in_reply_to', 'idx_ticket_messages_email_in_reply_to');
    });
}

public function down(): void
{
    Schema::table('tickets', function (Blueprint $table) {
        $table->dropIndex('idx_tickets_email_thread');
        $table->dropUnique('uq_tickets_email_reply_address');
        $table->dropColumn(['email_thread_id', 'email_reply_address']);
    });

    Schema::table('ticket_messages', function (Blueprint $table) {
        $table->dropIndex('idx_ticket_messages_email_message_id');
        $table->dropIndex('idx_ticket_messages_email_in_reply_to');
        $table->dropColumn([
            'email_message_id',
            'email_in_reply_to',
            'email_references',
            'email_headers',
        ]);
    });
}

    protected $casts = [
        'is_internal' => 'boolean',
        'is_system_message' => 'boolean',
        'is_log' => 'boolean',
        'metadata' => 'array',
        'email_headers' => 'array',
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
    public function attachments(): HasMany
    {
        return $this->hasMany(TicketMessageAttachment::class);
    }
}
