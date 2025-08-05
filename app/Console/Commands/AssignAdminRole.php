<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssignAdminRole extends Command
{
    protected $signature = 'admin:assign {email : The email of the user to make admin}';
    protected $description = 'Assign admin role to a user by directly inserting into model_has_roles';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found!");
            return;
        }
        
        // Find the Admin role
        $adminRole = DB::table('roles')->where('name', 'Admin')->first();
        
        if (!$adminRole) {
            $this->error("Admin role not found! Please run the seeder first.");
            return;
        }
        
        // Remove any existing role assignments for this user
        DB::table('model_has_roles')->where('model_id', $user->id)->where('model_type', 'App\Models\User')->delete();
        
        // Insert the admin role assignment directly
        DB::table('model_has_roles')->insert([
            'role_id' => $adminRole->id,
            'model_type' => 'App\Models\User',
            'model_id' => $user->id,
            'department_id' => null // Admins don't belong to specific departments
        ]);
        
        // Clear the permission cache
        app('cache')->forget('spatie.permission.cache');
        
        $this->success("Successfully assigned Admin role to {$user->email} ({$user->name})");
        
        // Verify the assignment
        $user->refresh();
        $user->load('roles');
        
        if ($user->hasRole('Admin')) {
            $this->info("✓ Verification: User now has Admin role");
        } else {
            $this->warn("⚠ Warning: Role assignment may not have worked properly");
        }
    }
}