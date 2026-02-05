<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PersonalAccessTokens
{
    /**
     * List all personal access tokens for the current user.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     * @return Collection
     */
    public function __invoke($_, array $args): Collection
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user->tokens;
    }
}
