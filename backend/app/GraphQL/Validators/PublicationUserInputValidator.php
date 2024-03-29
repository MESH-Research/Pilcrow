<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use Nuwave\Lighthouse\Validation\Validator;

class PublicationUserInputValidator extends Validator
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
                'numeric',
                'integer',
            ],
            'publication_id' => [
                'filled',
                'numeric',
                'integer',
            ],
            'role_id' => [
                'filled',
                'numeric',
                'integer',
            ],
        ];
    }
}
