<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DepartmentGroup;
use App\Models\Organization;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding users...');

        // Clear existing users
        User::query()->delete();

        // Get default organization (create if not exists)
        $organization = Organization::firstOrCreate([
            'name' => 'Hospitality Technology',
        ], [
            'company' => 'Hospitality Technology Ltd',
            'company_contact' => 'System Admin',
            'tin_no' => '123456789',
            'is_active' => true,
            'subscription_status' => 'active',
            'notes' => 'Default Organization',
        ]);

        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $supportRole = Role::where('name', 'support')->first();

        // Get department groups
        $departmentGroups = DepartmentGroup::all();

        // Create Super Admin user (not tied to department group structure)
        $superAdmin = User::withoutEvents(function () use ($organization) {
            return User::create([
                'uuid' => Str::uuid(),
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@hospitalitytechnology.com.mv',
                'password' => Hash::make('password'),
                'user_type' => 'standard',
                'department_group_id' => null, // Super admin not tied to specific department group
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
        });
        $superAdmin->assignRole($adminRole);
        $this->command->info('✓ Super Admin user created');

        // Create one user per department group
        foreach ($departmentGroups as $group) {
            $email = strtolower($group->name) . '@hospitalitytechnology.com.mv';
            $username = strtolower($group->name);
            $name = $group->name . ' Manager';
            
            // Determine role based on group
            $role = in_array($group->name, ['Admin', 'Email']) ? $adminRole : $supportRole;
            
            $user = User::withoutEvents(function () use ($name, $username, $email, $organization, $group) {
                return User::create([
                    'uuid' => Str::uuid(),
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'user_type' => 'standard',
                    'department_group_id' => $group->id,
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);
            });
            
            $user->assignRole($role);
            
            $this->command->info("✓ {$name} user created with {$role->name} role");
        }

        // Create organization-user relationships for all users (including support users)
        $this->createOrganizationUserRelationships($organization);

        $this->command->info('✅ Users seeded successfully!');
    }

    /**
     * Create organization-user relationships for all users
     */
    private function createOrganizationUserRelationships(Organization $organization): void
    {
        $this->command->info('🔗 Creating organization-user relationships...');

        // Get all users that don't already have organization relationships
        $users = User::whereDoesntHave('organizations')->get();

        foreach ($users as $user) {
            // Create the relationship in the pivot table
            $user->organizations()->attach($organization->id, [
                'is_primary' => false // Support users are not primary
            ]);

            $this->command->info("✓ Linked user {$user->name} to {$organization->name}");
        }

        // Set the Super Admin as the primary user for the default organization
        $superAdmin = User::where('username', 'superadmin')->first();
        if ($superAdmin) {
            // Update the pivot table
            $superAdmin->organizations()->updateExistingPivot($organization->id, ['is_primary' => true]);
            
            // Update the organization table
            $organization->update(['primary_user_id' => $superAdmin->id]);
            
            $this->command->info("✓ Set {$superAdmin->name} as primary user for {$organization->name}");
        }
    }
}