<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use Nuwave\Lighthouse\Validation\Validator;

class ProfileMetadataValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'salutation' => [
                'max:64',
            ],
            'phone' => [
                'max:32',
            ],
            'orchid_id' => [
                'max:128',
            ],
            'humanities_commons' => [
                'max:128',
            ],
            'professional_title' => [
                'max:256',
            ],
            'specialization' => [
                'max:256',
            ],
            'affiliation' => [
                'max:256',
            ],
            'interest_keywords.*' => [
                'max:128',
            ],
            'disinterest_keywords.*' => [
                'max:128',
            ],
            'biography' => [
                'max:4096',
            ],
            'websites.*' => [
                'max:512',
            ],
        ];
    }
}
