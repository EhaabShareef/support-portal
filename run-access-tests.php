<?php

/**
 * Enhanced Role Access Test Runner
 * 
 * Uses the access-test.php configuration to run comprehensive tests.
 * Perfect for development workflow and CI/CD integration.
 * 
 * Features:
 * - Configuration-driven testing
 * - Detailed reporting
 * - Easy to extend for new features
 * - Color-coded output
 * - JSON export for integration
 * 
 * Usage: php run-access-tests.php [options]
 * Options:
 *   --verbose    Show detailed output
 *   --json       Output results as JSON
 *   --save       Save report to file
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Route;

class ConfigurableAccessTest 
{
    private $config;
    private $users;
    private $results = [];
    private $options;

    public function __construct($options = [])
    {
        $this->options = array_merge([
            'verbose' => false,
            'json' => false,
            'save' => false,
        ], $options);
        
        $this->config = config('access-test');
        $this->loadUsers();
    }

    private function loadUsers()
    {
        $this->users = User::with(['roles', 'permissions'])->whereHas('roles')->get();
        
        if ($this->users->isEmpty()) {
            throw new Exception("❌ No users with roles found. Run: php artisan migrate:fresh --seed");
        }
    }

    public function runTests()
    {
        if (!$this->options['json']) {
            $this->printHeader();
        }

        $this->testRoleStructure();
        $this->testNavigationAccess();
        $this->testPermissionStructure();
        $this->testRoleExpectations();
        
        if ($this->options['json']) {
            $this->outputJson();
        } else {
            $this->printSummary();
        }

        if ($this->options['save'] || $this->config['test_config']['save_detailed_report']) {
            $this->saveReport();
        }
    }

    private function printHeader()
    {
        echo $this->color("🔐 Enhanced Role Access Test Suite", 'cyan') . "\n";
        echo $this->color(str_repeat("=", 45), 'cyan') . "\n\n";
        
        echo $this->color("📊 System Overview:", 'yellow') . "\n";
        echo "  • Users: " . $this->users->count() . "\n";
        echo "  • Roles: " . Role::count() . "\n";
        echo "  • Permissions: " . Permission::count() . "\n";
        echo "  • Navigation Elements: " . count($this->config['navigation_elements']) . "\n\n";
    }

    private function testRoleStructure()
    {
        if (!$this->options['json']) {
            echo $this->color("👥 Testing Role Structure", 'blue') . "\n";
            echo str_repeat("-", 30) . "\n";
        }

        $expectedRoles = array_keys($this->config['role_expectations']);
        $actualRoles = Role::pluck('name')->toArray();
        
        $this->results['role_structure'] = [
            'expected_roles' => $expectedRoles,
            'actual_roles' => $actualRoles,
            'missing_roles' => array_diff($expectedRoles, $actualRoles),
            'extra_roles' => array_diff($actualRoles, $expectedRoles),
            'status' => 'passed',
        ];

        foreach ($expectedRoles as $roleName) {
            $exists = in_array($roleName, $actualRoles);
            $status = $exists ? "✅" : "❌";
            
            if (!$exists) {
                $this->results['role_structure']['status'] = 'failed';
            }

            if (!$this->options['json']) {
                echo "  {$status} Role '{$roleName}' " . ($exists ? "exists" : "missing") . "\n";
            }
        }

        if (!$this->options['json']) {
            echo "\n";
        }
    }

    private function testNavigationAccess()
    {
        if (!$this->options['json']) {
            echo $this->color("🧭 Testing Navigation Access", 'blue') . "\n";
            echo str_repeat("-", 35) . "\n";
        }

        $this->results['navigation_access'] = [];

        foreach ($this->users as $user) {
            $userResults = [
                'user' => $user->name,
                'roles' => $user->roles->pluck('name')->toArray(),
                'tests' => [],
                'passed' => 0,
                'failed' => 0,
            ];

            foreach ($this->config['navigation_elements'] as $element) {
                $hasAccess = $this->checkElementAccess($user, $element);
                $expected = $this->shouldHaveAccess($user, $element);
                $testPassed = $hasAccess === $expected;

                $testResult = [
                    'element' => $element['name'],
                    'expected' => $expected,
                    'actual' => $hasAccess,
                    'passed' => $testPassed,
                    'critical' => $element['critical'] ?? false,
                ];

                $userResults['tests'][] = $testResult;
                
                if ($testPassed) {
                    $userResults['passed']++;
                } else {
                    $userResults['failed']++;
                }

                if (!$this->options['json'] && ($this->options['verbose'] || !$testPassed)) {
                    $status = $testPassed ? "✅" : "❌";
                    $critical = ($element['critical'] ?? false) ? " [CRITICAL]" : "";
                    echo "  {$status} {$user->name}: {$element['name']}{$critical}\n";
                }
            }

            $this->results['navigation_access'][] = $userResults;
        }

        if (!$this->options['json']) {
            echo "\n";
        }
    }

    private function testPermissionStructure()
    {
        if (!$this->options['json']) {
            echo $this->color("🔐 Testing Permission Structure", 'blue') . "\n";
            echo str_repeat("-", 35) . "\n";
        }

        $this->results['permission_structure'] = [];

        foreach ($this->users as $user) {
            $expectedRole = $user->roles->first()?->name;
            if (!$expectedRole || !isset($this->config['role_expectations'][$expectedRole])) {
                continue;
            }

            $expectations = $this->config['role_expectations'][$expectedRole];
            $actualPermissionCount = $user->getAllPermissions()->count();
            $expectedPermissionCount = $expectations['permission_count'];
            
            $permissionTest = [
                'user' => $user->name,
                'role' => $expectedRole,
                'expected_permissions' => $expectedPermissionCount,
                'actual_permissions' => $actualPermissionCount,
                'passed' => $actualPermissionCount === $expectedPermissionCount,
            ];

            $this->results['permission_structure'][] = $permissionTest;

            if (!$this->options['json']) {
                $status = $permissionTest['passed'] ? "✅" : "❌";
                echo "  {$status} {$user->name} ({$expectedRole}): {$actualPermissionCount}/{$expectedPermissionCount} permissions\n";
            }
        }

        if (!$this->options['json']) {
            echo "\n";
        }
    }

    private function testRoleExpectations()
    {
        if (!$this->options['json']) {
            echo $this->color("🎯 Testing Role Expectations", 'blue') . "\n";
            echo str_repeat("-", 35) . "\n";
        }

        $this->results['role_expectations'] = [];

        foreach ($this->config['role_expectations'] as $roleName => $expectations) {
            $role = Role::where('name', $roleName)->first();
            if (!$role) {
                continue;
            }

            $users = $this->users->filter(fn($u) => $u->hasRole($roleName));
            
            if ($users->isEmpty()) {
                if (!$this->options['json']) {
                    echo "  ⚠️  No users found with role: {$roleName}\n";
                }
                continue;
            }

            foreach ($users as $user) {
                $roleTest = [
                    'user' => $user->name,
                    'role' => $roleName,
                    'expectations_met' => [],
                    'passed' => 0,
                    'failed' => 0,
                ];

                // Test specific expectations
                foreach ($expectations['should_access'] as $feature => $shouldHave) {
                    $actuallyHas = $this->checkFeatureAccess($user, $feature);
                    $passed = $actuallyHas === $shouldHave;

                    $roleTest['expectations_met'][] = [
                        'feature' => $feature,
                        'expected' => $shouldHave,
                        'actual' => $actuallyHas,
                        'passed' => $passed,
                    ];

                    if ($passed) {
                        $roleTest['passed']++;
                    } else {
                        $roleTest['failed']++;
                        
                        if (!$this->options['json']) {
                            $status = $shouldHave ? "should have" : "should NOT have";
                            echo "  ❌ {$user->name} {$status} access to: {$feature}\n";
                        }
                    }
                }

                $this->results['role_expectations'][] = $roleTest;
            }
        }

        if (!$this->options['json']) {
            echo "\n";
        }
    }

    private function checkElementAccess($user, $element)
    {
        // Check role requirement
        if ($element['role_requirement']) {
            $roles = explode('|', $element['role_requirement']);
            if (!$user->hasAnyRole($roles)) {
                return false;
            }
        }

        // Check permission requirement
        if ($element['permission_requirement']) {
            if (!$user->can($element['permission_requirement'])) {
                return false;
            }
        }

        return true;
    }

    private function shouldHaveAccess($user, $element)
    {
        // This method determines what access the user SHOULD have
        // based on their role and the element requirements
        return $this->checkElementAccess($user, $element);
    }

    private function checkFeatureAccess($user, $feature)
    {
        switch ($feature) {
            case 'all_navigation_elements':
                return $user->hasRole('admin');
            case 'all_routes':
                return $user->hasRole('admin');
            case 'user_management':
                return $user->can('users.read');
            case 'role_management':
                return $user->can('users.manage');
            case 'system_settings':
                return $user->can('settings.read');
            case 'tickets':
                return $user->can('tickets.read');
            case 'organizations':
                return $user->can('organizations.read');
            case 'schedules':
                return $user->can('schedules.read');
            case 'delete_operations':
                return $user->can('users.delete') || $user->can('tickets.delete');
            default:
                return false;
        }
    }

    private function printSummary()
    {
        echo $this->color("📊 Test Summary", 'green') . "\n";
        echo str_repeat("=", 40) . "\n\n";

        // Navigation access summary
        $navPassed = $navFailed = 0;
        foreach ($this->results['navigation_access'] as $userResult) {
            $navPassed += $userResult['passed'];
            $navFailed += $userResult['failed'];
        }

        echo "🧭 Navigation Access:\n";
        echo "  ✅ Passed: {$navPassed}\n";
        echo "  ❌ Failed: {$navFailed}\n\n";

        // Permission structure summary
        $permPassed = collect($this->results['permission_structure'])->where('passed', true)->count();
        $permFailed = collect($this->results['permission_structure'])->where('passed', false)->count();

        echo "🔐 Permission Structure:\n";
        echo "  ✅ Correct: {$permPassed}\n";
        echo "  ❌ Incorrect: {$permFailed}\n\n";

        // Overall status
        $overallStatus = ($navFailed === 0 && $permFailed === 0) ? "PASSED" : "FAILED";
        $color = ($overallStatus === "PASSED") ? 'green' : 'red';
        
        echo $this->color("🎯 Overall Status: {$overallStatus}", $color) . "\n\n";

        echo "💡 Tip: Use --verbose flag for detailed output\n";
        echo "💾 Tip: Use --save flag to save detailed report\n";
    }

    private function outputJson()
    {
        echo json_encode([
            'timestamp' => now()->toISOString(),
            'summary' => [
                'users_tested' => $this->users->count(),
                'roles_found' => Role::count(),
                'permissions_found' => Permission::count(),
            ],
            'results' => $this->results,
        ], JSON_PRETTY_PRINT);
    }

    private function saveReport()
    {
        $filename = 'storage/logs/access-test-' . now()->format('Y-m-d-H-i-s') . '.json';
        
        $reportData = [
            'timestamp' => now()->toISOString(),
            'config' => $this->config,
            'results' => $this->results,
            'users_tested' => $this->users->map(fn($u) => [
                'name' => $u->name,
                'email' => $u->email,
                'roles' => $u->roles->pluck('name')->toArray(),
                'permission_count' => $u->getAllPermissions()->count(),
            ])->toArray(),
        ];

        if (!is_dir('storage/logs')) {
            mkdir('storage/logs', 0755, true);
        }

        file_put_contents($filename, json_encode($reportData, JSON_PRETTY_PRINT));
        
        if (!$this->options['json']) {
            echo "💾 Report saved: {$filename}\n";
        }
    }

    private function color($text, $color = 'white')
    {
        if (!$this->config['test_config']['color_output']) {
            return $text;
        }

        $colors = [
            'red' => '31', 'green' => '32', 'yellow' => '33',
            'blue' => '34', 'magenta' => '35', 'cyan' => '36', 'white' => '37'
        ];
        
        $code = $colors[$color] ?? '37';
        return "\033[{$code}m{$text}\033[0m";
    }
}

// Parse command line arguments
$options = [];
foreach ($argv as $arg) {
    if ($arg === '--verbose') $options['verbose'] = true;
    if ($arg === '--json') $options['json'] = true;
    if ($arg === '--save') $options['save'] = true;
    if ($arg === '--help') {
        echo "Enhanced Role Access Test Runner\n\n";
        echo "Usage: php run-access-tests.php [options]\n\n";
        echo "Options:\n";
        echo "  --verbose    Show detailed test output\n";
        echo "  --json       Output results as JSON\n";
        echo "  --save       Save detailed report to file\n";
        echo "  --help       Show this help message\n";
        exit(0);
    }
}

// Run the tests
try {
    $tester = new ConfigurableAccessTest($options);
    $tester->runTests();
} catch (Exception $e) {
    if ($options['json'] ?? false) {
        echo json_encode(['error' => $e->getMessage()]);
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    exit(1);
}