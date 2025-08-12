<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Move existing descriptions to ticket_messages
        $tickets = DB::table('tickets')
            ->whereNotNull('description')
            ->where('description', '!=', '')
            ->select('id', 'client_id', 'description', 'created_at')
            ->get();

        foreach ($tickets as $ticket) {
            DB::table('ticket_messages')->insert([
                'ticket_id' => $ticket->id,
                'sender_id' => $ticket->client_id,
                'message' => $ticket->description,
                'is_internal' => false,
                'is_system_message' => false,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->created_at,
            ]);
        }

        // Step 2: Mark description column as deprecated (keep for now)
        Schema::table('tickets', function (Blueprint $table) {
            $table->text('description')->nullable()->comment('DEPRECATED: Use ticket_messages instead')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the deprecation comment
        Schema::table('tickets', function (Blueprint $table) {
            $table->text('description')->nullable()->comment('')->change();
        });

        // Note: We don't delete the ticket_messages created from descriptions
        // as they may have been modified or are part of the conversation flow
    }
};
