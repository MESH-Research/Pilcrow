<?php
/**
 * @phpcs:disable Generic.Files.LineLength.TooLong
 */
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
            'twitter' => [
                'max:128',
                'regex:/^[A-z0-9_]+$/',
            ],
            'facebook' => [
                'max:128',
                'regex:/^[A-z0-9_\-.]+$/',
            ],
            'instagram' => [
                'max:128',
                'regex:/^[A-Za-z0-9_](?:(?:[A-Za-z0-9_]|(?:\.(?!\.))){0,28}(?:[A-Za-z0-9_]))?$/',
            ],
            'linkedin' => [
                'max:128',
                'regex:/^[\w\-_À-ÿ%]+$/',
            ],
        ];
    }
}
