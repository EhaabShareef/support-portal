<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class CheckUserRoles extends Command
{
    protected $signature = 'user:check-roles {--assign-admin= : Email of user to assign admin role}';
    protected $description = 'Check user roles and optionally assign admin role';

    public function handle()
    {
        // Show all users and their roles
        $this->info('=== Current Users and Roles ===');
        $users = User::with('roles')->get();
        
        if ($users->isEmpty()) {
            $this->warn('No users found in the system.');
            return;
        }
        
        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->join(', ') ?: 'No roles assigned';
            $this->line("ID: {$user->id} | Email: {$user->email} | Name: {$user->name} | Roles: {$roles}");
        }
        
        // Show available roles
        $this->info("\n=== Available Roles ===");
        $roles = Role::all();
        foreach ($roles as $role) {
            $this->line("- {$role->name}");
        }
        
        // Assign admin role if requested
        if ($email = $this->option('assign-admin')) {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->error("User with email '{$email}' not found!");
                return;
            }
            
            // For teams-enabled permission system, we need to specify the team (department)
            // Admin users typically don't belong to specific departments, so we'll use null
            $adminRole = Role::where('name', 'Admin')->first();
            
            if (!$adminRole) {
                $this->error("Admin role not found! Please run the PermissionAndRoleSeeder first.");
                return;
            }
            
            // Remove existing roles first
            $user->roles()->detach();
            
            // Assign Admin role with null department (since Admins don't belong to specific departments)
            $user->assignRole($adminRole->name);
            
            $this->success("Admin role assigned to {$user->email} ({$user->name})");
            
            // Show updated user info
            $user->refresh();
            $roles = $user->roles->pluck('name')->join(', ');
            $this->info("Updated: {$user->email} now has roles: {$roles}");
        }
    }
}