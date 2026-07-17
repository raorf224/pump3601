<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth-signin');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // Determine if login input is email or username
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginType => $request->login,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            // Check user status
            if (Auth::user()->status != 1) {
                Auth::logout();
                return back()->withErrors(['login' => 'Your account is inactive.']);
            }

            return redirect()->intended('/station-sites');
        }

        return back()->withErrors([
            'login' => 'Invalid username/email or password.',
        ])->onlyInput('login');
    }

    public function applogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = DB::select("
            SELECT
                id,
                username,
                email,
                password,
                description,
                full_name,
                phone,
                role,
                status,
                created_at,
                updated_at,
                stationrow_id,
                station_id
                
            FROM users
            LEFT JOIN stations s ON u.id = s.user_id 
            WHERE (username = ? OR email = ?)
            LIMIT 1
        ", [
            $request->username,
            $request->username
        ]);

        if (empty($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password.'
            ], 401);
        }

        $user = (array) $user[0];

        if ($user['status'] != '1') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive.'
            ], 403);
        }

        if (!Hash::check($request->password, $user['password'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or password.'
            ], 401);
        }

        // Generate API Token
        $token = Str::random(80);



        unset($user['password']);


        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => $user
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
