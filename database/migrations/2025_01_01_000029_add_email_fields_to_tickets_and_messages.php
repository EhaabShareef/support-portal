<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            
            // Add indexes for fast email lookups and threading
            $table->index('email_message_id', 'idx_ticket_messages_email_message_id');
            $table->index('email_in_reply_to', 'idx_ticket_messages_email_in_reply_to');
            $table->index(['email_message_id', 'ticket_id'], 'idx_ticket_messages_email_ticket');
            $table->index(['email_in_reply_to', 'ticket_id'], 'idx_ticket_messages_reply_ticket');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_email_thread');
            $table->dropColumn(['email_thread_id', 'email_reply_address']);
        });

        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->dropColumn(['email_message_id', 'email_in_reply_to', 'email_references', 'email_headers']);
        });
    }
};
