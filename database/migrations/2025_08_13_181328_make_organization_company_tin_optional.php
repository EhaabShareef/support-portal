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
        Schema::table('organizations', function (Blueprint $table) {
            // Make company field nullable and remove unique constraint from tin_no
            $table->string('company')->nullable()->change();
            $table->dropUnique(['tin_no']);
            $table->string('tin_no')->nullable()->change();
            
            // Add back unique constraint but allow nulls
            $table->unique('tin_no', 'organizations_tin_no_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Revert changes - make fields required again
            $table->string('company')->nullable(false)->change();
            $table->dropUnique('organizations_tin_no_unique');
            $table->string('tin_no')->nullable(false)->unique()->change();
        });
    }
};
