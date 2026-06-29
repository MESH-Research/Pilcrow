<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use Nuwave\Lighthouse\Validation\Validator;

final class UpdateSubmissionContentValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required',
            ],
            // Body and title are both the author's content; supply either or
            // both, but at least one (a no-op update is rejected).
            'content' => [
                'required_without:title',
                'filled',
            ],
            'title' => [
                'required_without:content',
                'filled',
                'max:512',
            ],
        ];
    }
}
