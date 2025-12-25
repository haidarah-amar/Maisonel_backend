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
        'last_name' => 'required|string|max:255',
        'birth_date' => 'required|date|max:255',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:50000',
        'id_document' => 'nullable|image|mimes:jpeg,png,jpg|max:50000',
        'phone' => 'required|string|max:15|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // Handle photo upload safely
    if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        if ($file->isValid()) {
            // store under 'photos' directory on the 'public' disk
            $photo_path = $file->store('photos', 'public');
            $validatedData['photo'] = $photo_path;
        } else {
            return response()->json(['message' => 'Invalid photo upload'], 422);
        }
    }

    // Handle id_document upload safely
    if ($request->hasFile('id_document')) {
        $file = $request->file('id_document');
        if ($file->isValid()) {
            $id_path = $file->store('id_documents', 'public');
            $validatedData['id_document'] = $id_path;
        } else {
            return response()->json(['message' => 'Invalid id_document upload'], 422);
        }
    }

    // Create user (ensure password is hashed)
    $user = User::create([
        'phone' => $validatedData['phone'],
        'password' => Hash::make($validatedData['password']),
        'first_name' => $validatedData['first_name'],
        'last_name' => $validatedData['last_name'],
        'birth_date' => $validatedData['birth_date'],
        'photo' => $photo_path,
        'id_document' => $id_path
    ]);

    return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required|string|max:15',
            'password' => 'required|string',
        ]);
        if (!Auth::attempt($request->only('phone', 'password'))) {
            return response()->json(['message' => 'Invalid phone or password'], 401);
        }

        $user = User::where('phone', $request->phone)->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Login successful',
         'User' => $user ,
        'Token' => $token ], 200);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'User' => $user,
            'Token' => $token
        ], 201);

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
