<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FixUserRoles extends Command
{
    protected $signature = 'fix:user-roles';
    protected $description = 'Fix user role assignments by recreating them properly';

    public function handle()
    {
        try {
            // Clear all existing role assignments
            DB::table('model_has_roles')->delete();
            
            // Clear the permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
            // Get user ID 5
            $user = User::find(5);
            if (!$user) {
                $this->error("User ID 5 not found!");
                return;
            }
            
            // Get the Admin role
            $adminRole = DB::table('roles')->where('name', 'Admin')->first();
            if (!$adminRole) {
                $this->error("Admin role not found!");
                return;
            }
            
            // Insert without department_id (ignore teams for admin role)
            DB::table('model_has_roles')->insert([
                'role_id' => $adminRole->id,
                'model_type' => 'App\\Models\\User',
                'model_id' => 5
            ]);
            
            // Clear cache again
            app('cache')->forget('spatie.permission.cache');
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
            $this->info("Role assignment recreated for user ID 5");
            
            // Test immediately
            $user->refresh();
            $user->load('roles');
            
            $this->info("Testing role assignment:");
            $this->line("Roles loaded: " . $user->roles->pluck('name')->join(', '));
            $this->line("hasRole('Admin'): " . ($user->hasRole('Admin') ? 'YES' : 'NO'));
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}