<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register (Request $request){
    $validatedData = $request->validate([
        'first_name' => 'required|string|max:255',
        'phone' => 'required|string|max:15|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        'name' => $validatedData['name'],
        'phone' => $validatedData['phone'],
        'password' => Hash::make($validatedData['password']),
    ]);

    return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);

  }

  public function login (Request $request){
    $validatedData = $request->validate([
        'phone' => 'required|string|max:15',
        'password' => 'required|string',
    ]);
    if (!Auth::attempt($request->only('phone', 'password'))) {
        return response()->json(['message' => 'Invalid phone or password'], 401);
    }

    $user = User::where('phone', $request-> phone)->first();
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json(['message' => 'Login successful',
     'User' => $user ,
    'Token' => $token ], 201);

  }

    public function logout(Request $request)
{
    $user = $request->user();

    
    $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

    return response()->json([
        'message' => 'Logged out successfully'
    ], 200);
}


}
