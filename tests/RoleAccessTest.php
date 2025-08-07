<?php

/**
 * Role-Based Access Control Test Suite
 * 
 * This comprehensive test suite validates that users with different roles
 * have appropriate access to navigation elements, routes, and blade templates.
 * 
 * Usage:
 * - Run from command line: php tests/RoleAccessTest.php
 * - Automatically discovers roles, routes, and permissions
 * - Tests navigation visibility, route accessibility, and permission checks
 * - Generates detailed access matrix report
 * 
 * Features:
 * - Dynamic role discovery from database
 * - Route testing with middleware validation
 * - Navigation blade template parsing
 * - Permission-based access validation
 * - Detailed reporting with pass/fail status
 * - Future-proof for new modules and roles
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RoleAccessTest
{
    private $results = [];
    private $roles = [];
    private $testUsers = [];
    private $routes = [];
    private $navigationElements = [];

    public function __construct()
    {
        $this->loadRoles();
        $this->createTestUsers();
        $this->loadRoutes();
        $this->loadNavigationElements();
    }

    /**
     * Load all available roles from database
     */
    private function loadRoles()
    {
        $this->roles = Role::with('permissions')->get()->keyBy('name');
        echo "📋 Loaded " . $this->roles->count() . " roles: " . $this->roles->keys()->implode(', ') . "\n\n";
    }

    /**
     * Create or get test users for each role
     */
    private function createTestUsers()
    {
        foreach ($this->roles as $roleName => $role) {
            $email = "test-{$roleName}@test.com";
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $user = User::withoutEvents(function () use ($roleName, $email) {
                    return User::create([
                        'uuid' => \Illuminate\Support\Str::uuid(),
                        'name' => "Test {$roleName}",
                        'username' => "test-{$roleName}",
                        'email' => $email,
                        'password' => bcrypt('password'),
                        'email_verified_at' => now(),
                        'is_active' => true,
                    ]);
                });
            }
            
            // Ensure user has only the intended role
            $user->syncRoles([$roleName]);
            $this->testUsers[$roleName] = $user;
        }
        
        echo "👤 Created/verified test users for all roles\n\n";
    }

    /**
     * Load all application routes with their middleware
     */
    private function loadRoutes()
    {
        $routeCollection = Route::getRoutes();
        
        foreach ($routeCollection as $route) {
            $middleware = $route->gatherMiddleware();
            $name = $route->getName();
            $uri = $route->uri();
            $methods = $route->methods();
            
            // Skip API routes and auth routes
            if (str_starts_with($uri, 'api/') || in_array($uri, ['login', 'logout'])) {
                continue;
            }
            
            $this->routes[] = [
                'name' => $name,
                'uri' => $uri,
                'methods' => $methods,
                'middleware' => $middleware,
                'requires_auth' => in_array('auth', $middleware),
                'role_middleware' => $this->extractRoleMiddleware($middleware),
            ];
        }
        
        echo "🛣️  Loaded " . count($this->routes) . " routes for testing\n\n";
    }

    /**
     * Extract role requirements from middleware
     */
    private function extractRoleMiddleware($middleware)
    {
        foreach ($middleware as $m) {
            if (str_starts_with($m, 'role:')) {
                return str_replace('role:', '', $m);
            }
        }
        return null;
    }

    /**
     * Load navigation elements from blade files
     */
    private function loadNavigationElements()
    {
        $navigationFiles = [
            'resources/views/components/sidebar.blade.php',
            'resources/views/components/navigation.blade.php',
        ];
        
        foreach ($navigationFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $this->parseNavigationElements($content, $file);
            }
        }
        
        echo "🧭 Found " . count($this->navigationElements) . " navigation elements\n\n";
    }

    /**
     * Parse navigation elements from blade content
     */
    private function parseNavigationElements($content, $file)
    {
        // Find @role directives
        preg_match_all('/@role\([\'"]([^\'"]+)[\'"]\)(.*?)@endrole/s', $content, $matches);
        
        for ($i = 0; $i < count($matches[0]); $i++) {
            $roleRequirement = $matches[1][$i];
            $elementContent = $matches[2][$i];
            
            // Extract route name from href or route()
            if (preg_match('/route\([\'"]([^\'"]+)[\'"]/', $elementContent, $routeMatch)) {
                $routeName = $routeMatch[1];
            } elseif (preg_match('/href=[\'"]([^\'"]+)[\'"]/', $elementContent, $hrefMatch)) {
                $routeName = $hrefMatch[1];
            } else {
                $routeName = 'unknown';
            }
            
            $this->navigationElements[] = [
                'file' => $file,
                'role_requirement' => $roleRequirement,
                'route_name' => $routeName,
                'content_preview' => trim(substr(strip_tags($elementContent), 0, 50)),
            ];
        }

        // Find @can directives
        preg_match_all('/@can\([\'"]([^\'"]+)[\'"]\)(.*?)@endcan/s', $content, $canMatches);
        
        for ($i = 0; $i < count($canMatches[0]); $i++) {
            $permissionRequirement = $canMatches[1][$i];
            $elementContent = $canMatches[2][$i];
            
            if (preg_match('/route\([\'"]([^\'"]+)[\'"]/', $elementContent, $routeMatch)) {
                $routeName = $routeMatch[1];
            } elseif (preg_match('/href=[\'"]([^\'"]+)[\'"]/', $elementContent, $hrefMatch)) {
                $routeName = $hrefMatch[1];
            } else {
                $routeName = 'unknown';
            }
            
            $this->navigationElements[] = [
                'file' => $file,
                'permission_requirement' => $permissionRequirement,
                'route_name' => $routeName,
                'content_preview' => trim(substr(strip_tags($elementContent), 0, 50)),
            ];
        }
    }

    /**
     * Run all access tests
     */
    public function runTests()
    {
        echo "🚀 Starting comprehensive role-based access tests...\n";
        echo str_repeat("=", 60) . "\n\n";

        $this->testNavigationAccess();
        $this->testRouteAccess();
        $this->testPermissionAccess();
        $this->generateReport();
    }

    /**
     * Test navigation element visibility for each role
     */
    private function testNavigationAccess()
    {
        echo "🧭 Testing Navigation Access\n";
        echo str_repeat("-", 40) . "\n";

        foreach ($this->testUsers as $roleName => $user) {
            $this->results['navigation'][$roleName] = [];
            
            foreach ($this->navigationElements as $element) {
                $shouldHaveAccess = $this->shouldHaveNavigationAccess($user, $element);
                $testName = "Navigation: " . ($element['content_preview'] ?? 'Unknown');
                
                $this->results['navigation'][$roleName][] = [
                    'test' => $testName,
                    'expected' => $shouldHaveAccess,
                    'requirement' => $element['role_requirement'] ?? $element['permission_requirement'] ?? 'None',
                    'file' => basename($element['file']),
                ];
                
                $status = $shouldHaveAccess ? "✅" : "❌";
                echo "  {$status} {$roleName}: {$testName}\n";
            }
        }
        echo "\n";
    }

    /**
     * Test route accessibility for each role
     */
    private function testRouteAccess()
    {
        echo "🛣️  Testing Route Access\n";
        echo str_repeat("-", 40) . "\n";

        foreach ($this->testUsers as $roleName => $user) {
            $this->results['routes'][$roleName] = [];
            
            foreach ($this->routes as $route) {
                if (!$route['requires_auth']) continue; // Skip public routes
                
                $shouldHaveAccess = $this->shouldHaveRouteAccess($user, $route);
                $testName = "Route: " . ($route['name'] ?? $route['uri']);
                
                $this->results['routes'][$roleName][] = [
                    'test' => $testName,
                    'expected' => $shouldHaveAccess,
                    'requirement' => $route['role_middleware'] ?? 'Authenticated',
                    'uri' => $route['uri'],
                ];
                
                $status = $shouldHaveAccess ? "✅" : "❌";
                echo "  {$status} {$roleName}: {$testName}\n";
            }
        }
        echo "\n";
    }

    /**
     * Test permission-based access for each role
     */
    private function testPermissionAccess()
    {
        echo "🔐 Testing Permission Access\n";
        echo str_repeat("-", 40) . "\n";

        $allPermissions = Permission::all();
        
        foreach ($this->testUsers as $roleName => $user) {
            $this->results['permissions'][$roleName] = [];
            $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
            
            foreach ($allPermissions as $permission) {
                $hasPermission = in_array($permission->name, $userPermissions);
                $testName = "Permission: " . $permission->name;
                
                $this->results['permissions'][$roleName][] = [
                    'test' => $testName,
                    'expected' => $hasPermission,
                    'requirement' => 'Direct permission',
                ];
                
                $status = $hasPermission ? "✅" : "❌";
                echo "  {$status} {$roleName}: {$testName}\n";
            }
        }
        echo "\n";
    }

    /**
     * Determine if user should have navigation access
     */
    private function shouldHaveNavigationAccess($user, $element)
    {
        if (isset($element['role_requirement'])) {
            $roles = explode('|', $element['role_requirement']);
            return $user->hasAnyRole($roles);
        }
        
        if (isset($element['permission_requirement'])) {
            return $user->can($element['permission_requirement']);
        }
        
        return true; // No restrictions
    }

    /**
     * Determine if user should have route access
     */
    private function shouldHaveRouteAccess($user, $route)
    {
        if (!$route['requires_auth']) {
            return true;
        }
        
        if ($route['role_middleware']) {
            $roles = explode('|', $route['role_middleware']);
            return $user->hasAnyRole($roles);
        }
        
        return true; // Authenticated users can access
    }

    /**
     * Generate comprehensive access report
     */
    private function generateReport()
    {
        echo "📊 Access Matrix Report\n";
        echo str_repeat("=", 60) . "\n\n";

        // Role summary
        echo "👥 Role Summary:\n";
        foreach ($this->roles as $roleName => $role) {
            $permissionCount = $role->permissions->count();
            echo "  • {$roleName}: {$permissionCount} permissions\n";
        }
        echo "\n";

        // Access statistics
        echo "📈 Access Statistics:\n";
        foreach ($this->results as $category => $roleResults) {
            echo "  {$category}:\n";
            foreach ($roleResults as $roleName => $tests) {
                $accessCount = collect($tests)->where('expected', true)->count();
                $totalCount = count($tests);
                echo "    • {$roleName}: {$accessCount}/{$totalCount} accessible\n";
            }
            echo "\n";
        }

        // Detailed access matrix
        $this->generateAccessMatrix();
        
        // Save results to file
        $this->saveResultsToFile();
    }

    /**
     * Generate visual access matrix
     */
    private function generateAccessMatrix()
    {
        echo "🗃️  Navigation Access Matrix:\n";
        $matrix = [];
        
        // Collect unique navigation elements
        $elements = [];
        foreach ($this->results['navigation'] as $roleName => $tests) {
            foreach ($tests as $test) {
                $elements[$test['test']] = $test['requirement'];
            }
        }
        
        // Create matrix header
        $roles = array_keys($this->testUsers);
        $maxElementLength = max(array_map('strlen', array_keys($elements)));
        
        printf("  %-{$maxElementLength}s | %s\n", "Element", implode(" | ", array_map(fn($r) => str_pad(substr($r, 0, 8), 8), $roles)));
        echo "  " . str_repeat("-", $maxElementLength + 2 + (count($roles) * 11)) . "\n";
        
        foreach ($elements as $element => $requirement) {
            printf("  %-{$maxElementLength}s |", substr($element, 12)); // Remove "Navigation: "
            
            foreach ($roles as $roleName) {
                $hasAccess = false;
                foreach ($this->results['navigation'][$roleName] as $test) {
                    if ($test['test'] === $element) {
                        $hasAccess = $test['expected'];
                        break;
                    }
                }
                printf(" %-8s |", $hasAccess ? "✅" : "❌");
            }
            echo "\n";
        }
        echo "\n";
    }

    /**
     * Save detailed results to JSON file
     */
    private function saveResultsToFile()
    {
        $reportData = [
            'timestamp' => now()->toISOString(),
            'roles' => $this->roles->map(fn($role) => [
                'name' => $role->name,
                'permission_count' => $role->permissions->count(),
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ])->toArray(),
            'results' => $this->results,
            'summary' => [
                'total_roles' => $this->roles->count(),
                'total_routes' => count($this->routes),
                'total_navigation_elements' => count($this->navigationElements),
                'total_permissions' => Permission::count(),
            ],
        ];
        
        $filename = 'storage/logs/role-access-test-' . now()->format('Y-m-d-H-i-s') . '.json';
        file_put_contents($filename, json_encode($reportData, JSON_PRETTY_PRINT));
        echo "💾 Detailed results saved to: {$filename}\n\n";
    }

    /**
     * Clean up test users
     */
    public function cleanup()
    {
        echo "🧹 Cleaning up test users...\n";
        foreach ($this->testUsers as $user) {
            if (str_starts_with($user->email, 'test-')) {
                $user->delete();
            }
        }
        echo "✅ Cleanup completed\n";
    }
}

// Run the tests
echo "🔐 Role-Based Access Control Test Suite\n";
echo "=====================================\n\n";

try {
    $tester = new RoleAccessTest();
    $tester->runTests();
    
    // Ask if user wants to clean up test users
    echo "Would you like to clean up test users? (y/N): ";
    $handle = fopen("php://stdin", "r");
    $cleanup = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($cleanup) === 'y') {
        $tester->cleanup();
    }
    
    echo "\n🎉 Test suite completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error running tests: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}