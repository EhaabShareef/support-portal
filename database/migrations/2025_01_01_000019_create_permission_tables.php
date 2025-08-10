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
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPerm = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $teamForeign = $columnNames['team_foreign_key'] ?? 'team_id';

        // Create permissions table
        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        // Create roles table
        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $teamForeign) {
            $table->bigIncrements('id');
            if ($teams) {
                $table->unsignedBigInteger($teamForeign)->nullable();
                $table->index($teamForeign, 'roles_' . $teamForeign . '_index');
            }
            $table->string('name');
            $table->string('guard_name');
            $table->text('description')->nullable();
            $table->timestamps();
            
            if ($teams) {
                $table->unique([$teamForeign, 'name', 'guard_name'], 'roles_' . $teamForeign . '_name_guard_name_unique');
                $table->foreign($teamForeign)->references('id')->on('departments')->onDelete('cascade');
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        // Create model_has_permissions pivot table
        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($teams, $teamForeign, $pivotPerm, $columnNames, $tableNames) {
            if ($teams) {
                $table->unsignedBigInteger($teamForeign)->nullable();
                $table->index($teamForeign, $tableNames['model_has_permissions'] . '_' . $teamForeign . '_index');
            }
            $table->unsignedBigInteger($pivotPerm);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            
            $table->index(
                [$columnNames['model_morph_key'], 'model_type'],
                $tableNames['model_has_permissions'] . '_model_id_model_type_index'
            );
            
            $table->foreign($pivotPerm)
                  ->references('id')
                  ->on($tableNames['permissions'])
                  ->onDelete('cascade');
            
            if ($teams) {
                $table->primary(
                    [$teamForeign, $pivotPerm, $columnNames['model_morph_key'], 'model_type'],
                    $tableNames['model_has_permissions'] . '_permission_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotPerm, $columnNames['model_morph_key'], 'model_type'],
                    $tableNames['model_has_permissions'] . '_permission_model_type_primary'
                );
            }
        });

        // Create model_has_roles pivot table
        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($teams, $teamForeign, $pivotRole, $columnNames, $tableNames) {
            if ($teams) {
                $table->unsignedBigInteger($teamForeign)->nullable();
                $table->index($teamForeign, $tableNames['model_has_roles'] . '_' . $teamForeign . '_index');
            }
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            
            $table->index(
                [$columnNames['model_morph_key'], 'model_type'],
                $tableNames['model_has_roles'] . '_model_id_model_type_index'
            );
            
            $table->foreign($pivotRole)
                  ->references('id')
                  ->on($tableNames['roles'])
                  ->onDelete('cascade');
            
            if ($teams) {
                $table->primary(
                    [$teamForeign, $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    $tableNames['model_has_roles'] . '_role_model_type_primary'
                );
            } else {
                $table->primary(
                    [$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    $tableNames['model_has_roles'] . '_role_model_type_primary'
                );
            }
        });

        // Create role_has_permissions pivot table
        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($pivotPerm, $pivotRole, $tableNames) {
            $table->unsignedBigInteger($pivotPerm);
            $table->unsignedBigInteger($pivotRole);
            
            $table->foreign($pivotPerm)
                  ->references('id')
                  ->on($tableNames['permissions'])
                  ->onDelete('cascade');
                  
            $table->foreign($pivotRole)
                  ->references('id')
                  ->on($tableNames['roles'])
                  ->onDelete('cascade');
            
            $table->primary(
                [$pivotPerm, $pivotRole],
                $tableNames['role_has_permissions'] . '_permission_id_role_id_primary'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        
        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['roles']);
        Schema::dropIfExists($tableNames['permissions']);
    }
};