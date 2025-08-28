<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CheckUserPermissions extends Command
{
    protected $signature = 'user:check-permissions {email?}';
    protected $description = 'Check user permissions and roles';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Total users in database: " . User::count());
        $this->info("Total users including soft-deleted: " . User::withTrashed()->count());
        $this->info("Soft-deleted users: " . User::onlyTrashed()->count());
        
        // Try a direct database query
        $this->info("Direct DB query users:");
        $users = \DB::table('users')->get();
        $this->info("Found " . $users->count() . " users via direct query");
        
        if ($email) {
            $user = User::withTrashed()->where('email', $email)->first();
            if (!$user) {
                $this->error("User with email {$email} not found.");
                return 1;
            }
            $this->checkUser($user);
        } else {
            $this->info("All active users:");
            $users = User::all();
            if ($users->isEmpty()) {
                $this->warn("No active users found in database.");
                
                $this->info("\nSoft-deleted users:");
                $softDeletedUsers = User::onlyTrashed()->get();
                if ($softDeletedUsers->isNotEmpty()) {
                    $softDeletedUsers->each(function($user) {
                        $this->info("- {$user->name} ({$user->email}) - Deleted at: {$user->deleted_at}");
                    });
                    
                    if ($this->confirm('Do you want to restore all soft-deleted users?')) {
                        User::onlyTrashed()->restore();
                        $this->info("All users restored successfully!");
                    }
                }
            } else {
                $users->each(function($user) {
                    $this->checkUser($user);
                });
            }
        }
        
        return 0;
    }
    
    private function checkUser($user)
    {
        $this->info("\nUser: {$user->name} ({$user->email})");
        $this->info("Roles: " . $user->getRoleNames()->implode(', '));
        $this->info("Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', '));
    }
}
