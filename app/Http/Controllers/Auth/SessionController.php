<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    // GET /session
    public function index(Request $request)
    {
        return response()->json([
            'user'       => $request->user(),
            'session_id' => $request->session()->getId(),
            'lifetime'   => config('session.lifetime'),
        ]);
    }
}
