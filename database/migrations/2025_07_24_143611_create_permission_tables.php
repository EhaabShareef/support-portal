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
        $teams         = config('permission.teams');
        $tableNames    = config('permission.table_names');
        $columnNames   = config('permission.column_names');
        $pivotRole     = $columnNames['role_pivot_key']       ?? 'role_id';
        $pivotPerm     = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $teamForeign   = $columnNames['team_foreign_key']     ?? 'team_id';

        /*throw_if(
            empty($tableNames),
            new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear].')
        );
        throw_if(
            $teams && empty($teamForeign),
            new \Exception('Error: team_foreign_key not set in config/permission.php.')
        );
        */

        if (! Schema::hasTable($tableNames['permissions'])) {
            Schema::create($tableNames['permissions'], function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name','guard_name']);
            });
        }

        if (! Schema::hasTable($tableNames['roles'])) {
            Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $teamForeign) {
                $table->bigIncrements('id');
                if ($teams) {
                    $table->unsignedBigInteger($teamForeign)->nullable();
                    $table->index($teamForeign, 'roles_' . $teamForeign . '_index');
                }
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                if ($teams) {
                    $table->unique([$teamForeign,'name','guard_name'], 'roles_' . $teamForeign . '_name_guard_name_unique');
                } else {
                    $table->unique(['name','guard_name']);
                }
            });
        } else {
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teams, $teamForeign, $tableNames) {
                // add guard_name if missing
                if (! Schema::hasColumn($tableNames['roles'], 'guard_name')) {
                    $table->string('guard_name')
                          ->default(config('auth.defaults.guard'))
                          ->after('name');
                }
                // add department_id (team) if missing
                if ($teams && ! Schema::hasColumn($tableNames['roles'], $teamForeign)) {
                    $table->unsignedBigInteger($teamForeign)
                          ->nullable()
                          ->after('guard_name');
                    $table->index($teamForeign, 'roles_' . $teamForeign . '_index');
                    $table->foreign($teamForeign)
                          ->references('id')
                          ->on('departments')
                          ->onDelete('cascade');
                }
                // adjust unique constraint
                if ($teams) {
                    // drop old [name,guard_name] unique
                    if (Schema::hasColumn($tableNames['roles'], 'name') &&
                        Schema::hasColumn($tableNames['roles'], 'guard_name'))
                    {
                        $table->dropUnique('roles_name_guard_name_unique');
                    }
                    $table->unique(
                        [$teamForeign,'name','guard_name'],
                        'roles_' . $teamForeign . '_name_guard_name_unique'
                    );
                }
            });
        }

        //
        // 3) model_has_permissions pivot
        //
        if (! Schema::hasTable($tableNames['model_has_permissions'])) {
            Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($teams, $teamForeign, $pivotPerm, $columnNames, $tableNames) {
                if ($teams) {
                    $table->unsignedBigInteger($teamForeign)->nullable();
                }
                $table->unsignedBigInteger($pivotPerm);
                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index(
                    [$columnNames['model_morph_key'],'model_type'],
                    $tableNames['model_has_permissions'] . '_model_id_model_type_index'
                );
                $table->foreign($pivotPerm)
                      ->references('id')
                      ->on($tableNames['permissions'])
                      ->onDelete('cascade');
                if ($teams) {
                    $table->index($teamForeign, $tableNames['model_has_permissions'] . '_' . $teamForeign . '_index');
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
        }

        //
        // 4) model_has_roles pivot
        //
        if (! Schema::hasTable($tableNames['model_has_roles'])) {
            Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($teams, $teamForeign, $pivotRole, $columnNames, $tableNames) {
                if ($teams) {
                    $table->unsignedBigInteger($teamForeign)->nullable();
                }
                $table->unsignedBigInteger($pivotRole);
                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index(
                    [$columnNames['model_morph_key'],'model_type'],
                    $tableNames['model_has_roles'] . '_model_id_model_type_index'
                );
                $table->foreign($pivotRole)
                      ->references('id')
                      ->on($tableNames['roles'])
                      ->onDelete('cascade');
                if ($teams) {
                    $table->index($teamForeign, $tableNames['model_has_roles'] . '_' . $teamForeign . '_index');
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
        }

        //
        // 5) role_has_permissions pivot
        //
        if (! Schema::hasTable($tableNames['role_has_permissions'])) {
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

        // Clear the permission cache
        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames  = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamForeign = $columnNames['team_foreign_key'] ?? 'team_id';
        $teams       = config('permission.teams');

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
        Schema::dropIfExists($tableNames['permissions']);

        if (Schema::hasTable($tableNames['roles'])) {
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teams, $teamForeign) {
                if ($teams && Schema::hasColumn($table->getTable(), $teamForeign)) {
                    $table->dropForeign([$teamForeign]);
                    $table->dropIndex('roles_' . $teamForeign . '_index');
                    $table->dropColumn($teamForeign);
                }
                if (Schema::hasColumn($table->getTable(), 'guard_name')) {
                    $table->dropColumn('guard_name');
                }
                if ($teams) {
                    $table->dropUnique('roles_' . $teamForeign . '_name_guard_name_unique');
                    $table->unique(['name','guard_name'], 'roles_name_guard_name_unique');
                }
            });
        }
    }
};
