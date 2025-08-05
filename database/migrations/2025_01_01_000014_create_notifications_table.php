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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Public-facing ID
            
            // Who receives the notification
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Notification content
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info'); // info, success, warning, error
            $table->string('action_url')->nullable(); // Where to go when clicked
            $table->json('data')->nullable(); // Additional data
            
            // Status tracking
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_important')->default(false);
            
            // Optional: Link to related model
            $table->morphs('notifiable');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes (notifiable_type/notifiable_id index auto-created by morphs())
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['user_id', 'is_important']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};