<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SimpleAssignAdmin extends Command
{
    protected $signature = 'admin:simple {email : The email of the user to make admin}';
    protected $description = 'Assign admin role using standard Spatie methods';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found!");
            return;
        }
        
        // Check if Admin role exists
        $adminRole = Role::where('name', 'Admin')->first();
        if (!$adminRole) {
            $this->error("Admin role not found!");
            return;
        }
        
        try {
            // Remove any existing roles
            $user->syncRoles([]);
            
            // Assign Admin role
            $user->assignRole('Admin');
            
            $this->success("Admin role assigned to {$user->email} ({$user->name})");
            
            // Verify
            $user->refresh();
            if ($user->hasRole('Admin')) {
                $this->info("✓ Verification successful - user now has Admin role");
            } else {
                $this->warn("⚠ Verification failed - role may not have been assigned properly");
            }
            
        } catch (\Exception $e) {
            $this->error("Error assigning role: " . $e->getMessage());
            return;
        }
    }
}