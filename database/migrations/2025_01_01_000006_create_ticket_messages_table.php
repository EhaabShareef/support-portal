<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')
                  ->constrained('tickets')
                  ->onDelete('cascade');
                  
            $table->foreignId('sender_id')
                  ->constrained('users')
                  ->onDelete('cascade');
                  
            $table->text('message');
            $table->boolean('is_internal')->default(false); // Internal vs customer-visible
            $table->boolean('is_system_message')->default(false); // System-generated messages
            $table->json('metadata')->nullable(); // For storing additional data (attachments, etc.)
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['ticket_id', 'created_at']);
            $table->index(['sender_id', 'created_at']);
            $table->index(['is_internal', 'is_system_message']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};