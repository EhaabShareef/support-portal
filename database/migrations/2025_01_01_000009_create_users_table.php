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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Public-facing ID for API/URLs
            $table->string('name')->index(); // Indexed for search
            $table->string('username')->unique()->index();
            $table->string('email')->unique()->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable(); // User avatar image
            $table->string('phone', 32)->nullable(); // Optional phone number
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable(); // Track user activity
            $table->string('timezone')->default('UTC'); // User timezone preference
            $table->json('preferences')->nullable(); // Store user preferences
            
            // Foreign keys with proper constraints  
            $table->foreignId('department_group_id')
                  ->nullable()
                  ->constrained('department_groups')
                  ->onDelete('set null'); // Don't cascade delete users
                  
            $table->enum('user_type', ['standard', 'corporate'])->default('standard');
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // Soft delete for user data integrity
            
            // Composite indexes for common queries
            $table->index(['is_active', 'department_group_id']);
            $table->index(['is_active', 'user_type']);
            $table->index('last_login_at');
            $table->index('user_type', 'idx_users_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
