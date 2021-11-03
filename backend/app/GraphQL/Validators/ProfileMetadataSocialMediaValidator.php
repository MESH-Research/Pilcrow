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
            'twitter' => [
                'max:128',
                'regex:/^[A-z0-9_]+$/',
            ],
            'facebook' => [
                'max:128',
                'regex:/^(?:https?:)?\/\/(?:www\.)?(?:facebook|fb)\.com\/(?<profile>(?![A-z]+\.php)(?!marketplace|gaming|watch|me|messages|help|search|groups)[A-z0-9_\-.]+)\/?$/',
            ],
            'instagram' => [
                'max:128',
                'regex:/(?:https?:)?\/\/(?:www\.)?(?:instagram\.com|instagr\.am)\/(?<username>[A-Za-z0-9_](?:(?:[A-Za-z0-9_]|(?:\.(?!\.))){0,28}(?:[A-Za-z0-9_]))?$)$/',
            ],
            'linkedin' => [
                'max:128',
                'regex:/^(?:https?:)?\/\/(?:[\w]+\.)?linkedin\.com\/in\/(?<permalink>[\w\-_À-ÿ%]+)\/?$/',
            ],
        ];
    }
}
