<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('role')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        ActivityLog::log('login', "User logged in: {$user->name}", $user, null, null, $user->id);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        ActivityLog::log('logout', "User logged out: {$user->name}", $user);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['role', 'department']),
        ]);
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            ActivityLog::log('password_reset_requested', "Password reset requested for {$request->email}");

            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent to your email.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unable to send reset link. Please try again later.',
        ], 422);
    }

    /**
     * Reset password with token
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                ActivityLog::log('password_reset', "Password reset for: {$user->name}", $user, null, null, $user->id);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired reset token.',
        ], 422);
    }

    /**
     * Register a new user (public registration)
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Default role is Student (id=4 typically, but let's look it up)
        $studentRole = \App\Models\Role::where('name', 'Student')->first();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $studentRole ? $studentRole->id : 1,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        ActivityLog::log('registered', "New user registered: {$user->name} ({$user->email})", $user, null, null, $user->id);

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user->load('role'),
            'token' => $token,
        ], 201);
    }
}
