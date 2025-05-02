<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login()
    {
        if(Auth::user()){
            return redirect()->intended('/home');
        }
        return view("auth.login");
    }
    public function loginUser(Requests\LoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->intended('/home');
        }

        // Authentication failed
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
    public function loginUserApi(Requests\LoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $token = Auth::user()->createToken('user_token')->plainTextToken;
            // Authentication failed
            return response()->json([
                'success' => true,
                'message' => 'login success.',
                'data' => [
                    'user' => Auth::user(),
                    'token'=> $token
                ],
            ]);
        }

        // Authentication failed
        return response()->json([
            'success' => false,
            'message' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function register()
    {
        return view("auth.register");
    }
    public function registerUser(Requests\RegisterRequest $request)
    {
        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('user_token')->plainTextToken;
        if ($request->header('Accept') == 'application/json') {
            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ], 200);
        } else {
            // Attempt to log in the user
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $request->session()->regenerate();
                return redirect()->intended('/home'); // Redirect to homepage
            }

            // If login fails, redirect to login page
            return redirect()->intended('/login')->with('error', 'Registration successful. Please log in.');
        }
    }
}
