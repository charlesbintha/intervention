<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\LoginRateLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login user and create token
     */
    public function login(LoginRequest $request, LoginRateLimiter $loginRateLimiter): JsonResponse
    {
        if ($loginRateLimiter->tooManyAttempts($request)) {
            return response()->json([
                'message' => 'Trop de tentatives de connexion.',
                'retry_after' => $loginRateLimiter->availableIn($request),
            ], 429);
        }

        $user = User::query()->where('email', $request->string('email')->toString())->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            $loginRateLimiter->hit($request);

            throw ValidationException::withMessages([
                'email' => ['Les informations d’identification sont incorrectes.'],
            ]);
        }

        if (! $user->is_active) {
            $loginRateLimiter->hit($request);

            return response()->json([
                'message' => 'Votre compte a été désactivé. Veuillez contacter l’administrateur.',
            ], 403);
        }

        $loginRateLimiter->clear($request);
        $token = $user->createToken(
            'mobile-app',
            ['*'],
            now()->addMinutes((int) config('sanctum.expiration')),
        )->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }

    /**
     * Get current user
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
