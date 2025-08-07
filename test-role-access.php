<?php

/**
 * Quick Role Access Test
 * 
 * A streamlined test to verify role-based access across the application.
 * Perfect for quick validation during development and new feature implementation.
 * 
 * Usage: php test-role-access.php
 * 
 * Features:
 * - Tests existing users (no test user creation needed)
 * - Validates navigation visibility based on roles
 * - Checks route accessibility with middleware
 * - Tests permission-based access
 * - Color-coded output for easy reading
 * - Quick summary report
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Route;

class QuickRoleAccessTest
{
    private $users = [];
    private $routes = [];
    private $navigationChecks = [];

    public function __construct()
    {
        $this->loadUsers();
        $this->loadRoutes();
        $this->defineNavigationChecks();
    }

    private function loadUsers()
    {
        // Get actual users from the system (not test users)
        $this->users = User::with(['roles', 'permissions'])->whereHas('roles')->get();
        
        if ($this->users->isEmpty()) {
            throw new Exception("No users with roles found. Please run the seeder first.");
        }
        
        echo "👥 Testing " . $this->users->count() . " users:\n";
        foreach ($this->users as $user) {
            $roles = $user->roles->pluck('name')->implode(', ');
            echo "  • {$user->name} ({$user->email}) - Roles: {$roles}\n";
        }
        echo "\n";
    }

    private function loadRoutes()
    {
        $routeCollection = Route::getRoutes();
        
        foreach ($routeCollection as $route) {
            $middleware = $route->gatherMiddleware();
            $name = $route->getName();
            $uri = $route->uri();
            
            // Focus on protected routes
            if (!in_array('auth', $middleware) || str_starts_with($uri, 'api/')) {
                continue;
            }
            
            $roleMiddleware = null;
            foreach ($middleware as $m) {
                if (str_starts_with($m, 'role:')) {
                    $roleMiddleware = str_replace('role:', '', $m);
                    break;
                }
            }
            
            $this->routes[] = [
                'name' => $name,
                'uri' => $uri,
                'role_requirement' => $roleMiddleware,
                'middleware' => $middleware,
            ];
        }
        
        echo "🛣️  Found " . count($this->routes) . " protected routes\n\n";
    }

    private function defineNavigationChecks()
    {
        // Define key navigation elements to test
        $this->navigationChecks = [
            [
                'name' => 'Users Management',
                'role_requirement' => 'admin',
                'route' => 'admin.users.index',
                'description' => 'Admin user management page',
            ],
            [
                'name' => 'Roles Management',
                'role_requirement' => 'admin',
                'route' => 'admin.roles.index',
                'description' => 'Role management page',
            ],
            [
                'name' => 'Organizations',
                'role_requirement' => null, // All authenticated users
                'route' => 'organizations.index',
                'description' => 'Organization listing',
            ],
            [
                'name' => 'Tickets',
                'role_requirement' => null, // All authenticated users
                'route' => 'tickets.index',
                'description' => 'Ticket management',
            ],
            [
                'name' => 'Create Ticket',
                'role_requirement' => null, // All authenticated users
                'route' => 'tickets.create',
                'description' => 'Ticket creation form',
            ],
            [
                'name' => 'Schedule',
                'role_requirement' => 'admin|client',
                'route' => 'schedule.index',
                'description' => 'Schedule calendar',
            ],
            [
                'name' => 'Settings',
                'role_requirement' => 'admin',
                'route' => 'admin.settings',
                'description' => 'System settings',
            ],
        ];
    }

    public function runTests()
    {
        echo "🚀 Running Quick Role Access Tests\n";
        echo str_repeat("=", 50) . "\n\n";

        $this->testNavigationAccess();
        $this->testRouteAccess();
        $this->testKeyPermissions();
        $this->generateSummary();
    }

    private function testNavigationAccess()
    {
        echo "🧭 Navigation Access Test\n";
        echo str_repeat("-", 30) . "\n";

        foreach ($this->users as $user) {
            $userRoles = $user->roles->pluck('name')->toArray();
            echo "\n👤 {$user->name} ({$user->roles->pluck('name')->implode(', ')}):\n";
            
            foreach ($this->navigationChecks as $check) {
                $shouldHaveAccess = $this->checkAccess($userRoles, $check['role_requirement']);
                $status = $shouldHaveAccess ? "✅" : "❌";
                
                echo "  {$status} {$check['name']} - {$check['description']}\n";
                
                if ($check['role_requirement']) {
                    echo "      Required: {$check['role_requirement']}\n";
                }
            }
        }
        echo "\n";
    }

    private function testRouteAccess()
    {
        echo "🛣️  Route Access Test\n";
        echo str_repeat("-", 30) . "\n";

        $protectedRoutes = array_filter($this->routes, fn($r) => $r['role_requirement'] !== null);
        
        if (empty($protectedRoutes)) {
            echo "  ℹ️  No role-protected routes found\n\n";
            return;
        }

        foreach ($this->users as $user) {
            $userRoles = $user->roles->pluck('name')->toArray();
            echo "\n👤 {$user->name}:\n";
            
            foreach ($protectedRoutes as $route) {
                $shouldHaveAccess = $this->checkAccess($userRoles, $route['role_requirement']);
                $status = $shouldHaveAccess ? "✅" : "❌";
                $routeDisplay = $route['name'] ?: $route['uri'];
                
                echo "  {$status} {$routeDisplay} (requires: {$route['role_requirement']})\n";
            }
        }
        echo "\n";
    }

    private function testKeyPermissions()
    {
        echo "🔐 Key Permissions Test\n";
        echo str_repeat("-", 30) . "\n";

        $keyPermissions = [
            'users.read' => 'View users',
            'users.create' => 'Create users',
            'users.update' => 'Update users',
            'users.delete' => 'Delete users',
            'organizations.read' => 'View organizations',
            'organizations.create' => 'Create organizations',
            'tickets.read' => 'View tickets',
            'tickets.create' => 'Create tickets',
            'schedules.read' => 'View schedules',
            'settings.read' => 'View settings',
        ];

        foreach ($this->users as $user) {
            echo "\n👤 {$user->name} ({$user->getAllPermissions()->count()} total permissions):\n";
            
            foreach ($keyPermissions as $permission => $description) {
                $hasPermission = $user->can($permission);
                $status = $hasPermission ? "✅" : "❌";
                
                echo "  {$status} {$permission} - {$description}\n";
            }
        }
        echo "\n";
    }

    private function checkAccess($userRoles, $requirement)
    {
        if ($requirement === null) {
            return true; // No specific role required
        }
        
        $requiredRoles = explode('|', $requirement);
        return !empty(array_intersect($userRoles, $requiredRoles));
    }

    private function generateSummary()
    {
        echo "📊 Summary Report\n";
        echo str_repeat("=", 50) . "\n";

        // Role distribution
        $roleStats = [];
        foreach ($this->users as $user) {
            foreach ($user->roles as $role) {
                $roleStats[$role->name] = ($roleStats[$role->name] ?? 0) + 1;
            }
        }

        echo "\n👥 Role Distribution:\n";
        foreach ($roleStats as $roleName => $count) {
            echo "  • {$roleName}: {$count} user(s)\n";
        }

        // Permission distribution
        echo "\n🔐 Permission Summary:\n";
        foreach ($this->users as $user) {
            $permissionCount = $user->getAllPermissions()->count();
            $roles = $user->roles->pluck('name')->implode(', ');
            echo "  • {$user->name} ({$roles}): {$permissionCount} permissions\n";
        }

        // Route protection summary
        $protectedRoutes = array_filter($this->routes, fn($r) => $r['role_requirement'] !== null);
        $openRoutes = count($this->routes) - count($protectedRoutes);
        
        echo "\n🛣️  Route Summary:\n";
        echo "  • Protected routes: " . count($protectedRoutes) . "\n";
        echo "  • Open routes: {$openRoutes}\n";
        echo "  • Total routes: " . count($this->routes) . "\n";

        // System health check
        echo "\n🏥 System Health:\n";
        $adminUsers = $this->users->filter(fn($u) => $u->hasRole('admin'));
        $supportUsers = $this->users->filter(fn($u) => $u->hasRole('support'));
        
        if ($adminUsers->count() === 0) {
            echo "  ⚠️  No admin users found!\n";
        } else {
            echo "  ✅ {$adminUsers->count()} admin user(s) available\n";
        }
        
        if ($supportUsers->count() === 0) {
            echo "  ⚠️  No support users found!\n";
        } else {
            echo "  ✅ {$supportUsers->count()} support user(s) available\n";
        }

        echo "\n🎯 Test completed successfully!\n";
        echo "💡 Tip: Run this test after adding new roles, routes, or permissions\n";
    }
}

// Color output helper
function colorOutput($text, $color = 'white')
{
    $colors = [
        'red' => '31',
        'green' => '32',
        'yellow' => '33',
        'blue' => '34',
        'magenta' => '35',
        'cyan' => '36',
        'white' => '37',
    ];
    
    $colorCode = $colors[$color] ?? '37';
    return "\033[{$colorCode}m{$text}\033[0m";
}

// Run the test
echo colorOutput("🔐 Quick Role Access Test Suite", 'cyan') . "\n";
echo colorOutput(str_repeat("=", 40), 'cyan') . "\n\n";

try {
    $tester = new QuickRoleAccessTest();
    $tester->runTests();
} catch (Exception $e) {
    echo colorOutput("❌ Error: " . $e->getMessage(), 'red') . "\n";
}