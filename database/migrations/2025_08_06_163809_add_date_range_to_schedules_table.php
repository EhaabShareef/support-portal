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
        Schema::table('schedules', function (Blueprint $table) {
            // Add start_date and end_date columns
            $table->date('start_date')->nullable()->after('date');
            $table->date('end_date')->nullable()->after('start_date');
            
            // Add index for date range queries
            $table->index(['start_date', 'end_date'], 'idx_schedules_date_range');
            $table->index(['user_id', 'start_date', 'end_date'], 'idx_schedules_user_date_range');
        });
        
        // Migrate existing data: copy 'date' to both 'start_date' and 'end_date'
        DB::statement("UPDATE schedules SET start_date = date, end_date = date WHERE start_date IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_schedules_date_range');
            $table->dropIndex('idx_schedules_user_date_range');
            
            // Drop columns
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
