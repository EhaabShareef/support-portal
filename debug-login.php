<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

echo "🔍 LOGIN DEBUGGING\n";
echo str_repeat("=", 40) . "\n\n";

// Test all users
$users = User::with('roles')->get();

echo "👥 CHECKING ALL USERS:\n";
foreach ($users as $user) {
    echo "\n📧 {$user->email}:\n";
    echo "  Name: {$user->name}\n";
    echo "  Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
    echo "  Password exists: " . (!empty($user->password) ? 'Yes' : 'No') . "\n";
    echo "  Email verified: " . ($user->email_verified_at ? 'Yes' : 'No') . "\n";
    echo "  Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    
    // Test password
    $testPassword = 'password';
    $passwordWorks = Hash::check($testPassword, $user->password);
    echo "  Password 'password' works: " . ($passwordWorks ? 'Yes' : 'No') . "\n";
    
    if (!$passwordWorks) {
        echo "  🔧 FIXING PASSWORD...\n";
        $user->update(['password' => Hash::make('password')]);
        echo "  ✅ Password updated\n";
    }
    
    if (!$user->is_active) {
        echo "  🔧 ACTIVATING USER...\n";
        $user->update(['is_active' => true]);
        echo "  ✅ User activated\n";
    }
    
    if (!$user->email_verified_at) {
        echo "  🔧 VERIFYING EMAIL...\n";
        $user->update(['email_verified_at' => now()]);
        echo "  ✅ Email verified\n";
    }
}

echo "\n🔐 CHECKING AUTH CONFIGURATION:\n";

// Check if Auth guard is properly configured
try {
    $guard = config('auth.defaults.guard');
    $driver = config("auth.guards.{$guard}.driver");
    $provider = config("auth.guards.{$guard}.provider");
    
    echo "  Default guard: {$guard}\n";
    echo "  Driver: {$driver}\n";
    echo "  Provider: {$provider}\n";
    
    $userProvider = config("auth.providers.{$provider}");
    echo "  User model: " . $userProvider['model'] . "\n";
    
} catch (Exception $e) {
    echo "  ❌ Auth config error: " . $e->getMessage() . "\n";
}

echo "\n🧪 TESTING LOGIN ATTEMPT:\n";

// Try to authenticate the superadmin user
$superAdmin = User::where('email', 'superadmin@hospitalitytechnology.com.mv')->first();
if ($superAdmin) {
    echo "  Testing superadmin login...\n";
    
    // Test manual authentication
    if (Hash::check('password', $superAdmin->password)) {
        echo "  ✅ Password verification works\n";
        
        // Test if user can be authenticated
        Auth::login($superAdmin);
        if (Auth::check()) {
            echo "  ✅ User can be authenticated\n";
            echo "  Authenticated as: " . Auth::user()->name . "\n";
            Auth::logout();
        } else {
            echo "  ❌ Authentication failed\n";
        }
    } else {
        echo "  ❌ Password verification failed\n";
    }
} else {
    echo "  ❌ Superadmin user not found\n";
}

echo "\n💡 LOGIN INSTRUCTIONS:\n";
echo "  URL: /login\n";
echo "  Email: superadmin@hospitalitytechnology.com.mv\n";
echo "  Password: password\n";
echo "\n✅ All users should now be able to login with 'password'\n";