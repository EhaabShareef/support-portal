<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugRoles extends Command
{
    protected $signature = 'debug:roles';
    protected $description = 'Debug role assignments in database';

    public function handle()
    {
        $this->info("=== Roles Table ===");
        $roles = DB::table('roles')->get();
        foreach ($roles as $role) {
            $this->line("ID: {$role->id} | Name: {$role->name} | Guard: {$role->guard_name}");
        }
        
        $this->info("\n=== Model Has Roles Table ===");
        $modelRoles = DB::table('model_has_roles')->get();
        foreach ($modelRoles as $mr) {
            $this->line("Role ID: {$mr->role_id} | Model ID: {$mr->model_id} | Model Type: {$mr->model_type} | Dept ID: " . ($mr->department_id ?? 'null'));
        }
        
        $this->info("\n=== Users Table ===");
        $users = DB::table('users')->get(['id', 'name', 'email']);
        foreach ($users as $user) {
            $this->line("ID: {$user->id} | Email: {$user->email} | Name: {$user->name}");
        }
        
        $this->info("\n=== Direct Role Check for User ID 5 ===");
        $userRoles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', 5)
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->select('roles.name', 'roles.id')
            ->get();
            
        if ($userRoles->count() > 0) {
            foreach ($userRoles as $role) {
                $this->line("User ID 5 has role: {$role->name} (ID: {$role->id})");
            }
        } else {
            $this->line("User ID 5 has no roles assigned");
        }
    }
}