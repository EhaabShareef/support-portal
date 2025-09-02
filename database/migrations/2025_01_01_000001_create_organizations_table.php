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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index(); // Indexed for search
            $table->string('company')->nullable();
            $table->string('company_contact')->nullable(); // TODO: Bassed on primary user if not set
            $table->string('tin_no')->nullable()->unique(); // Tax identification should be unique
            $table->boolean('is_active')->default(true); // Standardized boolean naming
            $table->enum('subscription_status', ['trial', 'active', 'suspended', 'cancelled'])
                  ->default('trial'); // For future billing management
            $table->text('notes')->nullable(); // For internal notes
            $table->unsignedBigInteger('primary_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Soft delete for data integrity
            
            // Indexes for performance
            $table->index(['is_active', 'subscription_status']);
            $table->index('created_at');
            $table->index('primary_user_id', 'idx_organizations_primary_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};