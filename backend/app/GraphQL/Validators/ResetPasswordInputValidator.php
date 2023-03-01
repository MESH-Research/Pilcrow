<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use Nuwave\Lighthouse\Validation\Validator;

final class ResetPasswordInputValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'email:rfc,dns',
                'filled',
            ],
            'password' => [
                'zxcvbn:3',
                'filled',
            ],
            'token' => [
                'filled',
            ],
        ];
    }
}
