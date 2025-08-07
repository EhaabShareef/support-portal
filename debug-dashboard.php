<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "🔍 DASHBOARD DEBUG\n";
echo str_repeat("=", 40) . "\n\n";

// Test superadmin user
$superAdmin = User::where('email', 'superadmin@hospitalitytechnology.com.mv')->first();

if (!$superAdmin) {
    echo "❌ Superadmin user not found!\n";
    exit(1);
}

echo "👤 USER DETAILS:\n";
echo "  Name: {$superAdmin->name}\n";
echo "  Email: {$superAdmin->email}\n";
echo "  Roles: " . $superAdmin->roles->pluck('name')->implode(', ') . "\n";
echo "  First role name: " . ($superAdmin->roles->first()?->name ?? 'No role') . "\n\n";

echo "🔍 ROLE CHECK RESULTS:\n";
echo "  userRole computed value: " . ($superAdmin->roles->first()?->name ?? 'client') . "\n";
echo "  hasRole('admin'): " . ($superAdmin->hasRole('admin') ? 'Yes' : 'No') . "\n";
echo "  hasRole('support'): " . ($superAdmin->hasRole('support') ? 'Yes' : 'No') . "\n";
echo "  hasRole('client'): " . ($superAdmin->hasRole('client') ? 'Yes' : 'No') . "\n\n";

echo "🎯 DASHBOARD TYPE DETERMINATION:\n";
$userRole = $superAdmin->roles->first()?->name ?? 'client';

if ($userRole === 'admin') {
    echo "  ✅ Should use ADMIN dashboard\n";
    echo "  Expected metrics: total_tickets, open_tickets, organizations, active_users\n";
} elseif ($userRole === 'support') {
    echo "  ✅ Should use AGENT dashboard\n";
    echo "  Expected metrics: my_tickets, my_open_tickets, department_tickets\n";
} else {
    echo "  ✅ Should use CLIENT dashboard\n";
    echo "  Expected metrics: my_tickets, open_tickets, resolved_tickets\n";
}

echo "\n💡 If you're getting 'my_tickets' error with admin user, the issue is:\n";
echo "  - User has 'admin' role but dashboard is using wrong template\n";
echo "  - Check the blade template role comparison logic\n";

echo "\n🔧 QUICK FIX:\n";
echo "  - Clear view cache: php artisan view:clear\n";
echo "  - Check browser cache and reload\n";
echo "  - Verify role names match exactly in blade conditions\n";