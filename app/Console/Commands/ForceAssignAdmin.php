<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ForceAssignAdmin extends Command
{
    protected $signature = 'admin:force {email : The email of the user to make admin}';
    protected $description = 'Force assign admin role using raw SQL';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found!");
            return;
        }
        
        try {
            // Get the Admin role ID
            $adminRole = DB::table('roles')->where('name', 'Admin')->first();
            if (!$adminRole) {
                $this->error("Admin role not found!");
                return;
            }
            
            // Remove any existing role assignments
            DB::table('model_has_roles')
                ->where('model_id', $user->id)
                ->where('model_type', 'App\\Models\\User')
                ->delete();
            
            // Check if department_id column exists
            $columns = collect(DB::select("SHOW COLUMNS FROM model_has_roles"));
            $hasDepartmentId = $columns->contains('Field', 'department_id');
            
            if ($hasDepartmentId) {
                // Insert with department_id set to a valid department (we'll get the first department)
                $firstDepartment = DB::table('departments')->first();
                if (!$firstDepartment) {
                    $this->error("No departments found! Please create a department first.");
                    return;
                }
                
                DB::table('model_has_roles')->insert([
                    'role_id' => $adminRole->id,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $user->id,
                    'department_id' => $firstDepartment->id
                ]);
                
                $this->info("Note: Admin role assigned with department_id = {$firstDepartment->id} (this is temporary)");
            } else {
                // Insert without department_id
                DB::table('model_has_roles')->insert([
                    'role_id' => $adminRole->id,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $user->id
                ]);
            }
            
            // Clear permission cache
            app('cache')->forget('spatie.permission.cache');
            
            $this->info("Successfully assigned Admin role to {$user->email} ({$user->name})");
            
            // Verify
            $user->refresh();
            $user->load('roles');
            
            if ($user->hasRole('Admin')) {
                $this->info("✓ Verification successful - user now has Admin role");
            } else {
                $this->warn("⚠ Verification failed - role may not have been assigned properly");
            }
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}