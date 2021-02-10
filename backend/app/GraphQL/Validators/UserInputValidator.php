<?php

namespace App\GraphQL\Validators;

use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class UserInputValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'max:256'
            ],
            'username' => [
                Rule::unique('users', 'username'),
                'filled'
            ],
            'email' => [
                Rule::unique('users', 'email'),
                'email:rfc,dns',
                'filled'
            ],
            'password' => [
                'zxcvbn_min:3',
                'filled'
            ]
        ];
    }

    public function messages(): array {
        return [
            'name' => [
                'max' => 'NAME_LENGTH_EXCEEDED',
            ],
            'username' => [
                'unique' => 'USERNAME_IN_USE',
                'filled' => 'USERNAME_EMPTY',
            ],
            'email' => [
                'unique' => 'EMAIL_IN_USE',
                'email' => 'EMAIL_NOT_VALID',
                'filled' => 'EMAIL_EMPTY',
            ],
            'password' => [
                'zxcvbn_min' => 'PASSWORD_NOT_COMPLEX',
                'filled' => 'PASSWORD_EMPTY'
            ]
        ];
    }
}
