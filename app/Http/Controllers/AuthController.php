<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register User
    public function register(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Create User
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Return Response
        return response()->json(['message' => 'User registered successfully!'], 201);
    }

    public function login(Request $request)
    {
        $request->validate([ 'email' => 'required|email', 'password' => 'required|string', 'remember' => 'boolean', ]);
        $credentials = $request->only('email', 'password');
        $remember = $request->input('remember', false);
        if (Auth::attempt($credentials,$remember    )) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
    
            return response()->json(['token' => $token]);
        }
    
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    
    public function logout(Request $request)
    {
        $request->user()->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

     public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
