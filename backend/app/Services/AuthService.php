<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Validate credentials and issue a Sanctum token.
     *
     * @throws AuthenticationException
     */
    public function login(string $email, string $password, string $deviceName): array
    {
        $user = User::withoutGlobalScopes()
            ->where('email', $email)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new AuthenticationException('The provided credentials are incorrect.');
        }

        if (! $user->tenant->is_active) {
            throw new AuthenticationException('Your account is suspended. Please contact support.');
        }

        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }

    /**
     * Revoke only the current token (this device).
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Revoke all tokens for the user (logout everywhere).
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }
}