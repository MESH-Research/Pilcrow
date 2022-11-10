<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class SubmissionInvitationValidator extends Validator
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
                Rule::unique('users', 'email')->ignore($this->arg('id'), 'id'),
                'email:rfc,dns',
                'filled',
            ],
        ];
    }
}
