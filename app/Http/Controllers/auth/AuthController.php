<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Create a new user
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')), // Hash password
        ]);

        // Return the created user
        return response()->json($user, 201);
    }
    
    public function login(Request $request)
    {
        // Validate request data
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    
        // Attempt to authenticate the user
        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    
        // Retrieve the authenticated user
        $user = Auth::user();
    
        // Check if the user is already logged in
        if ($user->login) {
            return response()->json(['error' => 'You are already logged in, please logout first'], 403);
        }
    
        // Update the login status
        $user->login = true;
        $user->save();
    
        // Create a token for the authenticated user
        $token = $user->createToken('token')->plainTextToken;
    
        // Return the user and token
        return response()->json(['user' => $user, 'token' => $token]);
    }
    
    public function logout(Request $request)
    {
        $userEmail = $request->input('email'); // Get the email from the request
    
        // Find the user by email
        $user = User::where('email', $userEmail)->first();
    
        if ($user) {
            // Update the login status
            $user->login = false;
            $user->save();
    
            // Revoke all tokens for the authenticated user
            $user->tokens()->delete();
    
            return response()->json(['message' => 'Successfully logged out']);
        }
    
        return response()->json(['error' => 'User not found'], 404);
    }
}
