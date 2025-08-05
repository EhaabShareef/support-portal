<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class TestAdminAccess extends Command
{
    protected $signature = 'test:admin-access';
    protected $description = 'Test admin access for user ID 5';

    public function handle()
    {
        $user = User::find(5);
        
        if (!$user) {
            $this->error("User ID 5 not found!");
            return;
        }
        
        $this->info("Testing admin access for: {$user->email} ({$user->name})");
        
        $isAdmin = $user->isAdmin();
        $this->line("isAdmin() method: " . ($isAdmin ? 'YES' : 'NO'));
        
        if ($isAdmin) {
            $this->info("✅ SUCCESS! User ID 5 has admin access.");
            $this->info("Login with: it@admin.com");
            $this->info("You should now see the 'Users' menu in the navigation.");
        } else {
            $this->error("❌ Failed - User does not have admin access.");
        }
    }
}