<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

class PublicationInputValidator extends Validator
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
                'max:256',
                Rule::unique('users', 'username')->ignore($this->arg('id'), 'id'),
                'filled',
            ],
        ];
    }
}
