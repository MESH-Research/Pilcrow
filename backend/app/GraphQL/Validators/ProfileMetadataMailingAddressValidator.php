<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use Nuwave\Lighthouse\Validation\Validator;

class ProfileMetadataMailingAddressValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'city' => [
                'max:128',
            ],
            'state' => [
                'max:64',
            ],
            'postal_code' => [
                'max:16',
            ],
            'street_address' => [
                'max:128',
            ],
        ];
    }
}
