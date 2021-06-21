<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use Nuwave\Lighthouse\Validation\Validator;

class SubmissionUserInputValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'filled',
            ],
            'submission_id' => [
                'filled',
            ],
            'role_id' => [
                'filled',
            ],
        ];
    }
}
