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
        Schema::table('organization_users', function (Blueprint $table) {
            // Drop the problematic unique constraint that was causing issues
            $table->dropUnique('unique_primary_per_org');
        });
        
        // We'll handle primary user uniqueness in the application logic
        // This allows multiple users per organization but only one can be primary
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_users', function (Blueprint $table) {
            // Restore the original constraint
            $table->unique(['organization_id', 'is_primary'], 'unique_primary_per_org');
        });
    }
};
