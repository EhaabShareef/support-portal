<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FixAdminRole extends Command
{
    protected $signature = 'admin:fix';
    protected $description = 'Fix admin role assignment for user ID 5';

    public function handle()
    {
        try {
            // Clear all existing role assignments
            DB::table('model_has_roles')->delete();
            
            // Get the admin role
            $adminRole = DB::table('roles')->where('name', 'admin')->first();
            if (!$adminRole) {
                $this->error("Admin role not found!");
                return;
            }
            
            // Get user ID 5
            $user = User::find(5);
            if (!$user) {
                $this->error("User ID 5 not found!");
                return;
            }
            
            // Get first department
            $firstDepartment = DB::table('departments')->first();
            if (!$firstDepartment) {
                $this->error("No departments found!");
                return;
            }
            
            // Insert admin role for user ID 5
            DB::table('model_has_roles')->insert([
                'role_id' => $adminRole->id,
                'model_type' => 'App\\Models\\User',
                'model_id' => 5,
                'department_id' => $firstDepartment->id
            ]);
            
            // Clear permission cache
            app('cache')->forget('spatie.permission.cache');
            
            $this->info("Admin role assigned to user ID 5: {$user->email} ({$user->name})");
            
            // Show current status
            $this->info("\n=== Updated User Roles ===");
            $users = User::with('roles')->get();
            foreach ($users as $u) {
                $roles = $u->roles->pluck('name')->join(', ') ?: 'No roles assigned';
                $this->line("ID: {$u->id} | Email: {$u->email} | Name: {$u->name} | Roles: {$roles}");
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}