<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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
            'company_name' => $request->company_name,
        ]);

        $token = $user->createToken('user_token')->plainTextToken;
        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 200);
    }

    public function registerUserWeb(Requests\RegisterRequest $request)
    {
        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_name' => $request->company_name,
        ]);

        // Attempt to log in the user
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->intended('/home'); // Redirect to homepage
        }

        // If login fails, redirect to login page
        return redirect()->intended('/login')->with('error', 'Registration successful. Please log in.');
    }

    //Reset Password
    public function requestPasswordReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Check if the user exists
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'We cannot find a user with that email address.',
            ], 404);
        }

        // Generate a custom token
        $token = bin2hex(random_bytes(32));

        // Store the token in the database (e.g., in a password_resets table)
        \DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
            ]
        );

        // Send the reset link via email
        $resetLink = 'https://preview--hr-manager.lovable.app'.'/reset-password/' . $token . '?email=' . urlencode($user->email);
        $emailHandlerController = new EmailHandlerController();
        $emailHandlerController->sendForgetPasswordEmail(new Request([
            'email' => $user->email,
            'reset_link' => $resetLink,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent successfully.',
        ]);
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // Validate the token and email
        $resetEntry = \DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetEntry) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token.',
            ], 400);
        }

        // Check if the token is expired (e.g., valid for 60 minutes)
        if (now()->diffInMinutes($resetEntry->created_at) > 60) {
            return response()->json([
                'success' => false,
                'message' => 'The reset token has expired.',
            ], 400);
        }

        // Reset the user's password
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'We cannot find a user with that email address.',
            ], 404);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        // Invalidate all tokens for the user
        $user->tokens()->delete();

        // Delete the reset token from the database
        \DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.',
        ]);
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }
}
