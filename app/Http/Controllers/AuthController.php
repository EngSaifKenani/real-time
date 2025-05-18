<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DeviceToken;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{


    public function __construct( )
    {
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(3)
            ],
            'phone_number' => 'nullable|string|max:20',
            //'gender' => 'nullable|in:male,female',
           // 'address' => 'nullable|string|max:255',
           // 'bio' => 'nullable|string|max:500',
            'fcm_token' => 'sometimes|string',
            'platform' => 'sometimes|in:android,ios,web'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number'] ?? null,
           // 'gender' => $validated['gender'] ?? null,
           // 'profile_image' => $validated['profile_image'] ?? null,
           // 'bio' => $validated['bio'] ?? null,
        ]);

        return response([
            'user' => $user->only(['id', 'name', 'email', 'phone_number']),
            'token' => $user->createToken($user->email)->plainTextToken
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'invalid credentials.'
            ], 403);
        }

        $token = $user->createToken($user->email)->plainTextToken;

        return response([
            'user' => $user->only(['id', 'name', 'email', 'phone_number']),
            'token' => $token
        ], 200);
    }
}
