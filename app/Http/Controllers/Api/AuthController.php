<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $userFields = ["id", "name", "email"];

    public function register(RegisterRequest $request)
    {
        $credentials = $request->validated();

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

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return response()->json(["message" => "Invalid credentials."], 401);
        }

        $user = Auth::user();
        $token = $user->createToken("auth-token");

        return response()->json([
            "user" => $user->only($this->userFields),
            "token" => $token->plainTextToken,
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
