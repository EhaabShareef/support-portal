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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // What was affected
            $table->morphs('subject'); // The model that was changed
            $table->string('event'); // created, updated, deleted, etc.
            $table->string('description')->nullable(); // Human-readable description
            
            // Who performed the action
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            
            // Change details
            $table->json('old_values')->nullable(); // Before changes
            $table->json('new_values')->nullable(); // After changes
            $table->json('properties')->nullable(); // Additional context
            
            // Context information
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('batch_uuid')->nullable(); // Group related changes
            
            $table->timestamps();
            
            // Indexes for querying activity (subject_type/subject_id index auto-created by morphs())
            $table->index(['user_id', 'created_at']);
            $table->index(['event', 'created_at']);
            $table->index('batch_uuid');
            $table->index('created_at'); // For cleanup/archiving
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};