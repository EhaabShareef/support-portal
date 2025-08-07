<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Database\Seeders\RolePermissionSeeder;

class PermissionsSync extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'permissions:sync 
                            {--fresh : Delete existing permissions and roles before syncing}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Sync permissions and roles from modules configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Syncing permissions and roles...');

        if ($this->option('fresh')) {
            $this->handleFreshSync();
        } else {
            $this->handleRegularSync();
        }

        return Command::SUCCESS;
    }

    /**
     * Handle fresh sync (delete and recreate everything)
     */
    private function handleFreshSync()
    {
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: Would delete all permissions and roles, then recreate from config');
            return;
        }

        if (!$this->confirm('This will delete all existing permissions and roles. Are you sure?')) {
            $this->info('Cancelled.');
            return;
        }

        $this->info('🗑️  Clearing existing permissions and roles...');
        
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Delete all permissions (this will also remove role-permission relationships)
        Permission::query()->delete();
        
        // Delete all roles except those with users assigned
        $rolesWithUsers = Role::has('users')->get();
        if ($rolesWithUsers->count() > 0) {
            $this->warn('Keeping roles with assigned users:');
            foreach ($rolesWithUsers as $role) {
                $this->line("  - {$role->name} ({$role->users()->count()} users)");
            }
        }
        
        Role::doesntHave('users')->delete();

        // Run the seeder
        $this->info('🌱 Running RolePermissionSeeder...');
        $seeder = new RolePermissionSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        $this->info('✅ Fresh sync completed!');
    }

    /**
     * Handle regular sync (update existing, add new)
     */
    private function handleRegularSync()
    {
        if ($this->option('dry-run')) {
            $this->showDryRunResults();
            return;
        }

        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Run the seeder (it handles updates intelligently)
        $this->info('🌱 Running RolePermissionSeeder...');
        $seeder = new RolePermissionSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        $this->info('✅ Sync completed!');
        $this->showCurrentStats();
    }

    /**
     * Show what would happen in a dry run
     */
    private function showDryRunResults()
    {
        $modules = config('modules.modules');
        $roleTemplates = config('modules.role_templates');
        
        // Calculate permissions that would be created
        $configPermissions = [];
        foreach ($modules as $module => $config) {
            foreach ($config['actions'] as $action) {
                $configPermissions[] = "{$module}.{$action}";
            }
        }
        
        $existingPermissions = Permission::pluck('name')->toArray();
        $newPermissions = array_diff($configPermissions, $existingPermissions);
        $removedPermissions = array_diff($existingPermissions, $configPermissions);
        
        $this->info('DRY RUN RESULTS:');
        $this->line('');
        
        if (count($newPermissions) > 0) {
            $this->info('Permissions that would be ADDED:');
            foreach ($newPermissions as $permission) {
                $this->line("  + {$permission}");
            }
        }
        
        if (count($removedPermissions) > 0) {
            $this->warn('Permissions that would be ORPHANED (exist in DB but not in config):');
            foreach ($removedPermissions as $permission) {
                $this->line("  - {$permission}");
            }
        }
        
        $this->line('');
        $this->info('Roles that would be updated:');
        foreach ($roleTemplates as $roleName => $template) {
            $this->line("  • {$roleName}");
        }
    }

    /**
     * Show current statistics
     */
    private function showCurrentStats()
    {
        $permissionCount = Permission::count();
        $roleCount = Role::count();
        $userCount = \App\Models\User::count();
        
        $this->line('');
        $this->info('📊 Current Statistics:');
        $this->line("Permissions: {$permissionCount}");
        $this->line("Roles: {$roleCount}");
        $this->line("Users: {$userCount}");
        
        $this->line('');
        $this->info('👥 Role Distribution:');
        $roles = Role::withCount('users')->get();
        foreach ($roles as $role) {
            $this->line("  {$role->name}: {$role->users_count} users");
        }
    }
}