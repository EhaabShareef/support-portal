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
        // First, let's add a temporary string column
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('status_temp', 50)->nullable()->after('status');
        });

        // Copy existing ENUM values to the temporary string column
        DB::statement("UPDATE tickets SET status_temp = status");

        // Drop the old ENUM column
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Rename the temporary column to 'status'
        Schema::table('tickets', function (Blueprint $table) {
            $table->renameColumn('status_temp', 'status');
        });

        // Make the status column not nullable
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('status', 50)->nullable(false)->change();
        });

        // Add index for better performance
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the index first
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        // Add a temporary ENUM column
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('status_temp', [
                'open',
                'in_progress', 
                'waiting_for_customer',
                'resolved',
                'closed'
            ])->default('open')->after('status');
        });

        // Copy string values back to ENUM
        DB::statement("UPDATE tickets SET status_temp = 
            CASE 
                WHEN status = 'open' THEN 'open'
                WHEN status = 'in_progress' THEN 'in_progress'
                WHEN status = 'waiting_for_customer' THEN 'waiting_for_customer'
                WHEN status = 'resolved' THEN 'resolved'
                WHEN status = 'closed' THEN 'closed'
                ELSE 'open'
            END");

        // Drop the string column
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Rename temp column back to status
        Schema::table('tickets', function (Blueprint $table) {
            $table->renameColumn('status_temp', 'status');
        });
    }
};
