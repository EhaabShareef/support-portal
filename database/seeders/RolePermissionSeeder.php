<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding roles and permissions...');

        // Clear existing data first
        $this->clearExistingData();

        // Clear cache to avoid conflicts
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::beginTransaction();
        
        try {
            // Create permissions first
            $this->createPermissions();

            // Create roles and assign permissions
            $this->createRoles();

            // Clear cache again after seeding
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            DB::commit();
            $this->command->info('✅ Roles and permissions seeded successfully!');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('❌ Error seeding roles and permissions: ' . $e->getMessage());
            throw $e;
        }
    }

    private function clearExistingData(): void
    {
        $this->command->info('Clearing existing roles, permissions, and user assignments...');
        
        // Clear user data first
        \App\Models\User::query()->delete();
        
        // Clear role-permission assignments
        DB::table('role_has_permissions')->delete();
        
        // Clear user-role assignments
        DB::table('model_has_roles')->delete();
        
        // Clear user-permission assignments
        DB::table('model_has_permissions')->delete();
        
        // Delete all roles
        Role::query()->delete();
        
        // Delete all permissions
        Permission::query()->delete();
        
        $this->command->info('✅ Existing data cleared.');
    }

    private function createPermissions(): void
    {
        $this->command->info('Creating permissions from modules config...');
        
        $modules = config('modules.modules');
        $permissions = [];
        
        // Generate permissions from modules configuration
        foreach ($modules as $module => $config) {
            foreach ($config['actions'] as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        // Create permissions
        $createdCount = 0;
        foreach ($permissions as $permission) {
            $created = Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
            
            if ($created->wasRecentlyCreated) {
                $createdCount++;
            }
        }
        
        $this->command->info("Created {$createdCount} new permissions (total: " . count($permissions) . ").");
    }

    private function createRoles(): array
    {
        $this->command->info('Creating admin and support roles...');

        $createdRoles = [];

        // Create Admin role with all permissions
        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);
        
        // Assign all permissions to admin
        $allPermissions = Permission::all();
        $adminRole->syncPermissions($allPermissions);
        $createdRoles['admin'] = $adminRole;
        $this->command->info("✓ Admin role created with " . $allPermissions->count() . " permissions");

        // Create Support role with limited permissions (will be configured later via UI)
        $supportRole = Role::create([
            'name' => 'support',
            'guard_name' => 'web'
        ]);
        
        // Support role gets basic read permissions only
        $basicPermissions = Permission::where('name', 'like', '%.read')
            ->orWhere('name', 'like', '%.create')
            ->orWhere('name', 'like', '%.update')
            ->get();
        $supportRole->syncPermissions($basicPermissions);
        $createdRoles['support'] = $supportRole;
        $this->command->info("✓ Support role created with " . $basicPermissions->count() . " permissions");

        // Create Client role for external users
        $clientRole = Role::create([
            'name' => 'client',
            'guard_name' => 'web'
        ]);
        
        // Client role gets very limited permissions - only ticket and dashboard access
        $clientPermissions = Permission::whereIn('name', [
            'tickets.create',
            'tickets.read',
            'tickets.update', // own tickets only
            'articles.read',
            'dashboard.access'
        ])->get();
        $clientRole->syncPermissions($clientPermissions);
        $createdRoles['client'] = $clientRole;
        $this->command->info("✓ Client role created with " . $clientPermissions->count() . " permissions");

        return $createdRoles;
    }

    /**
     * Resolve permission patterns into actual permission names
     *
     * @param mixed $permissions
     * @return array
     */
    private function resolvePermissions($permissions): array
    {
        if ($permissions === '*') {
            // Return all permissions
            return Permission::pluck('name')->toArray();
        }

        if (!is_array($permissions)) {
            return [];
        }

        $resolvedPermissions = [];

        foreach ($permissions as $permission) {
            if (Str::endsWith($permission, '.*')) {
                // Wildcard pattern - get all permissions for module
                $module = Str::beforeLast($permission, '.*');
                $modulePermissions = Permission::where('name', 'like', $module . '.%')->pluck('name')->toArray();
                $resolvedPermissions = array_merge($resolvedPermissions, $modulePermissions);
            } else {
                // Exact permission name
                $resolvedPermissions[] = $permission;
            }
        }

        // Remove duplicates and filter out non-existent permissions
        $resolvedPermissions = array_unique($resolvedPermissions);
        $existingPermissions = Permission::whereIn('name', $resolvedPermissions)->pluck('name')->toArray();

        return $existingPermissions;
    }
}