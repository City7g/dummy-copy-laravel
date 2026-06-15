<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $userFields = ["id", "name", "email"];

    public function register(Request $request)
    {
        $credentials = $request->validate([
            "name" => "required|string|min:3|max:255",
            "email" => "required|email|unique:users,email",
            "password" => "required|string|min:4|confirmed",
        ]);

        $user = User::create($credentials);

        $token = $user->createToken("auth-token");

        return response()->json(
            [
                "user" => $user->only($this->userFields),
                "token" => $token->plainTextToken,
            ],
            201,
        );
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            "email" => "required|email",
            "password" => "required|string",
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(["message" => "Invalid credentials."], 401);
        }

        $user = Auth::user();
        $token = $user->createToken("auth-token");

        return response()->json([
            "data" => [
                "user" => $user->only($this->userFields),
                "token" => $token->plainTextToken,
            ],
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            "user" => $request->user()->only($this->userFields),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "message" => "Logged out successfully.",
        ]);
    }
}
