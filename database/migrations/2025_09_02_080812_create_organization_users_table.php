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
        Schema::create('organization_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            // Unique constraints
            $table->unique(['user_id', 'organization_id'], 'unique_user_org');
            $table->unique(['organization_id', 'is_primary'], 'unique_primary_per_org');
            
            // Indexes for performance
            $table->index('user_id', 'idx_organization_users_user');
            $table->index('organization_id', 'idx_organization_users_org');
            $table->index(['organization_id', 'is_primary'], 'idx_organization_users_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_users');
    }
};
