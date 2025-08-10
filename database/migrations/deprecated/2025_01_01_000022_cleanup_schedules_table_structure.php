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
        // Check if the columns and indexes exist before trying to drop them
        if (Schema::hasColumn('schedules', 'date')) {
            Schema::table('schedules', function (Blueprint $table) {
                // Drop indexes and constraints if they exist
                try {
                    $table->dropUnique('schedules_user_id_date_event_type_id_unique');
                } catch (\Exception $e) {
                    // Index doesn't exist, continue
                }
                
                try {
                    $table->dropIndex('schedules_user_id_date_index');
                } catch (\Exception $e) {
                    // Index doesn't exist, continue
                }
                
                try {
                    $table->dropIndex('schedules_date_event_type_id_index');
                } catch (\Exception $e) {
                    // Index doesn't exist, continue
                }
                
                $table->dropColumn('date');
            });
        }

        // Update foreign key constraints to include cascade rules
        Schema::table('schedules', function (Blueprint $table) {
            // Drop existing foreign key constraints
            $table->dropForeign(['created_by']);
            
            // Re-add with proper cascade rules
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });
        
        // Update schedule event types foreign key as well
        Schema::table('schedule_event_types', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['created_by']);
            
            // Re-add with proper cascade rules
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });

        // Make start_date and end_date required now that date is removed
        Schema::table('schedules', function (Blueprint $table) {
            $table->date('start_date')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the date column
        Schema::table('schedules', function (Blueprint $table) {
            $table->date('date')->after('event_type_id');
        });

        // Populate the date column from start_date for existing records
        DB::statement("UPDATE schedules SET date = start_date WHERE date IS NULL");

        // Make start_date and end_date nullable again
        Schema::table('schedules', function (Blueprint $table) {
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
        });

        // Restore original indexes
        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['user_id', 'date']);
            $table->index(['date', 'event_type_id']);
            
            // Restore the old unique constraint
            $table->unique(['user_id', 'date', 'event_type_id']);
        });

        // Revert foreign key constraints
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')->references('id')->on('users');
        });
        
        Schema::table('schedule_event_types', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')->references('id')->on('users');
        });
    }
};