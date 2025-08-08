<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        Log::info('DB hash', ['hash' => optional($user)->password]);
        Log::info('plain',  ['plain'=> $credentials['password']]);
        Log::info('check',  ['match'=> $user ? Hash::check($credentials['password'],$user->password) : null]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Set flag for first-run loading overlay
            $request->session()->flash('show_loading_overlay', true);

            return redirect()->intended('/dashboard');
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials.'])
            ->onlyInput('email');
    }
}
