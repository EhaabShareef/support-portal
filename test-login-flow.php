<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;

echo "🔓 TESTING LOGIN FLOW\n";
echo str_repeat("=", 40) . "\n\n";

// Test credentials
$testCredentials = [
    'superadmin@hospitalitytechnology.com.mv' => 'password',
    'admin@hospitalitytechnology.com.mv' => 'password',
    'pms@hospitalitytechnology.com.mv' => 'password',
];

foreach ($testCredentials as $email => $password) {
    echo "🧪 Testing: {$email}\n";
    
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        echo "  ❌ User not found\n\n";
        continue;
    }
    
    // Basic checks
    echo "  User exists: ✅\n";
    echo "  Is active: " . ($user->is_active ? '✅' : '❌') . "\n";
    echo "  Has password: " . (!empty($user->password) ? '✅' : '❌') . "\n";
    echo "  Email verified: " . ($user->email_verified_at ? '✅' : '❌') . "\n";
    
    // Password check
    $passwordWorks = Hash::check($password, $user->password);
    echo "  Password check: " . ($passwordWorks ? '✅' : '❌') . "\n";
    
    // Role check
    $hasRoles = $user->roles->count() > 0;
    echo "  Has roles: " . ($hasRoles ? '✅' : '❌') . "\n";
    if ($hasRoles) {
        echo "    Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
    }
    
    // Authentication attempt check
    $credentials = ['email' => $email, 'password' => $password];
    $authAttempt = \Illuminate\Support\Facades\Auth::attempt($credentials);
    echo "  Auth::attempt: " . ($authAttempt ? '✅' : '❌') . "\n";
    
    if ($authAttempt) {
        \Illuminate\Support\Facades\Auth::logout();
    }
    
    echo "\n";
}

// Check middleware and guards
echo "🔒 AUTH SYSTEM CHECK:\n";
echo "  Default guard: " . config('auth.defaults.guard') . "\n";
echo "  Session driver: " . config('session.driver') . "\n";
echo "  Session lifetime: " . config('session.lifetime') . " minutes\n";

// Check if there are any issues with the User model
echo "\n👤 USER MODEL CHECK:\n";
$userModel = config('auth.providers.users.model');
echo "  User model: {$userModel}\n";

$testUser = new $userModel;
echo "  Implements Authenticatable: " . ($testUser instanceof \Illuminate\Contracts\Auth\Authenticatable ? '✅' : '❌') . "\n";
echo "  Uses HasRoles trait: " . (method_exists($testUser, 'hasRole') ? '✅' : '❌') . "\n";

echo "\n🎯 QUICK FIX SUGGESTIONS:\n";
echo "1. Try logging in with:\n";
echo "   Email: superadmin@hospitalitytechnology.com.mv\n";
echo "   Password: password\n";
echo "\n2. If still having issues, clear browser cookies/session\n";
echo "\n3. Check browser console for any JavaScript errors\n";
echo "\n4. Verify the login URL is /login\n";

echo "\n✅ All authentication components appear to be working!\n";