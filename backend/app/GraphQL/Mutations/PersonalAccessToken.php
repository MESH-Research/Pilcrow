<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\Auth;

class PersonalAccessToken
{
    /**
     * Create a new personal access token for the current user.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     * @return array{token: string, personalAccessToken: \Laravel\Sanctum\PersonalAccessToken}
     */
    public function create($_, array $args): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $token = $user->createToken($args['name']);

        return [
            'token' => $token->plainTextToken,
            'personalAccessToken' => $token->accessToken,
        ];
    }

    /**
     * Revoke a personal access token.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     * @return bool
     */
    public function revoke($_, array $args): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $token = $user->tokens()->where('id', $args['id'])->first();

        if (! $token) {
            return false;
        }

        return (bool)$token->delete();
    }
}
