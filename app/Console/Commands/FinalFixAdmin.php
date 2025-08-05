<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FinalFixAdmin extends Command
{
    protected $signature = 'admin:final-fix';
    protected $description = 'Final fix for admin role assignment';

    public function handle()
    {
        try {
            // Get first department (any department will do since admin doesn't really belong to one)
            $department = DB::table('departments')->first();
            if (!$department) {
                $this->error("No departments found! Please create at least one department first.");
                return;
            }
            
            // Clear existing assignments
            DB::table('model_has_roles')->delete();
            
            // Get the Admin role
            $adminRole = DB::table('roles')->where('name', 'Admin')->first();
            if (!$adminRole) {
                $this->error("Admin role not found!");
                return;
            }
            
            // Insert with department_id (using first department as placeholder)
            DB::table('model_has_roles')->insert([
                'role_id' => $adminRole->id,
                'model_type' => 'App\\Models\\User',
                'model_id' => 5,
                'department_id' => $department->id
            ]);
            
            // Clear all caches
            app('cache')->flush();
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            
            $this->info("Admin role assigned to user ID 5 with department_id = {$department->id}");
            
            // Verify the assignment in database
            $check = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->join('users', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.model_id', 5)
                ->select('users.email', 'roles.name as role_name')
                ->first();
                
            if ($check) {
                $this->info("✓ Database verification: {$check->email} has role '{$check->role_name}'");
                
                // Final test with fresh user instance
                $user = User::find(5);
                $user->load('roles');
                
                $roles = $user->roles->pluck('name')->toArray();
                $this->line("User roles loaded: " . implode(', ', $roles));
                
                if (in_array('Admin', $roles)) {
                    $this->info("✓ SUCCESS: User ID 5 now has Admin role!");
                    $this->info("You can now login with: it@admin.com");
                } else {
                    $this->warn("⚠ Role loaded but not showing in Laravel model");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}