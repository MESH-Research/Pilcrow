<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use Nuwave\Lighthouse\Validation\Validator;

class ProfileMetadataSocialMediaValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'google' => [
                'max:128',
            ],
            'twitter' => [
                'max:128',
            ],
            'facebook' => [
                'max:128',
            ],
            'instagram' => [
                'max:128',
            ],
            'linkedin' => [
                'max:128',
            ],
            'academia_edu_id' => [
                'max:128',
            ],
        ];
    }
}
