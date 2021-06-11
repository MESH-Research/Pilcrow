<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use Nuwave\Lighthouse\Validation\Validator;

class SubmissionInputValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'max:512',
                'filled',
            ],
            'publication_id' => [
                'filled',
            ],
            'user_id' => [
                'filled',
            ],
        ];
    }
}
