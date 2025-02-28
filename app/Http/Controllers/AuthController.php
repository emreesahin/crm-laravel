<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validated = $request->validate([
            'company_id' => 'required|integer',
            'role' => 'required|string',
            'name' => 'required|string',
            'surname' => 'required|string',
            'username' => 'required|string',
            'national_id' => 'nullable|string',
            'profile_photo' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);


        $user = User::create([
            'company_id' => $validated['company_id'],
            'role' => $validated['role'],
            'name' => $validated['name'],
            'surname' => $validated['surname'],
            'username' => $validated['username'],
            'national_id' => $validated['national_id'],
            'profile_photo' => $validated['profile_photo'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);


        return response()->json([
            'token' => $user->createToken('API Token')->plainTextToken,
        ]);
    }

    public function login(Request $request)
    {

        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Invalid login details',
            ], 401);
        }


        $user = Auth::user();


        return response()->json([
            'token' => $user->createToken('API Token')->plainTextToken,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {

        Auth::user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }
}
