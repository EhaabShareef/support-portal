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
        $tableNames = config('permission.table_names');
        $rolesTable = $tableNames['roles'] ?? 'roles';

        if (Schema::hasTable($rolesTable) && !Schema::hasColumn($rolesTable, 'description')) {
            Schema::table($rolesTable, function (Blueprint $table) {
                $table->text('description')->nullable()->after('guard_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $rolesTable = $tableNames['roles'] ?? 'roles';

        if (Schema::hasTable($rolesTable) && Schema::hasColumn($rolesTable, 'description')) {
            Schema::table($rolesTable, function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};