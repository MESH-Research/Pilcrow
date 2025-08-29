<?php

declare(strict_types=1);

namespace App\GraphQL\Validators;

use Nuwave\Lighthouse\Validation\Validator;

final class UpdateUsersInputValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'connect' => ['array', 'distinct'],
            'disconnect' => ['array', 'distinct'],
            'invite' => ['array', 'distinct'],
            'invite.*' => ['email', 'unique:users,email'],
            'message' => ['string']
        ];
    }
}
