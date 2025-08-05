<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestUserRole extends Command
{
    protected $signature = 'test:user-role {user_id}';
    protected $description = 'Test if a user has admin role';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User ID {$userId} not found!");
            return;
        }
        
        $this->info("Testing user: {$user->email} ({$user->name})");
        
        // Clear any cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Refresh user model
        $user->refresh();
        $user->load('roles');
        
        $this->info("Roles loaded: " . $user->roles->pluck('name')->join(', '));
        
        // Test different ways to check admin role
        $hasAdminRole = $user->hasRole('admin');
        $hasAdminRoleCapital = $user->hasRole('Admin');
        
        $this->line("hasRole('admin'): " . ($hasAdminRole ? 'YES' : 'NO'));
        $this->line("hasRole('Admin'): " . ($hasAdminRoleCapital ? 'YES' : 'NO'));
        
        // Check permissions
        $hasAdminAccess = $user->can('admin.access');
        $this->line("can('admin.access'): " . ($hasAdminAccess ? 'YES' : 'NO'));
        
        // Test the @role blade directive logic
        $roleNames = $user->roles->pluck('name')->toArray();
        $hasRoleAdmin = in_array('Admin', $roleNames);
        $hasRoleAdminLower = in_array('admin', $roleNames);
        
        $this->line("Role 'Admin' in roles array: " . ($hasRoleAdmin ? 'YES' : 'NO'));
        $this->line("Role 'admin' in roles array: " . ($hasRoleAdminLower ? 'YES' : 'NO'));
        
        if ($hasRoleAdminLower) {
            $this->info("✓ User should have admin access (role name is lowercase 'admin')");
        } else {
            $this->warn("⚠ User might not have admin access");
        }
    }
}