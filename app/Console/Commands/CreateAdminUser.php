<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'user:create-admin {--name=Admin} {--email=admin@example.com} {--password=password}';
    protected $description = 'Create an admin user with full permissions';

    public function handle()
    {
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');

        // Check if admin role exists
        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            $this->error('Admin role does not exist. Please run the RolePermissionSeeder first.');
            return 1;
        }

        // Check if user already exists
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $this->warn("User with email {$email} already exists.");
            if ($this->confirm('Do you want to assign admin role to this user?')) {
                $existingUser->assignRole($adminRole);
                $this->info("Admin role assigned to existing user: {$existingUser->name}");
                return 0;
            }
            return 1;
        }

        // Create new admin user
        $user = User::create([
            'name' => $name,
            'username' => strtolower(str_replace(' ', '', $name)),
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        // Assign admin role
        $user->assignRole($adminRole);

        $this->info("Admin user created successfully!");
        $this->info("Name: {$user->name}");
        $this->info("Email: {$user->email}");
        $this->info("Username: {$user->username}");
        $this->info("Password: {$password}");
        $this->info("Role: admin");

        return 0;
    }
}
