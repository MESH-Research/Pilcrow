<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use Illuminate\Validation\Rule;
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
        // print_r($this->arg('role_id'));
        // print_r($this->args);
        return [
            'user_id' => [
                'filled',
                'numeric',
                'integer',
            ],
            'submission_id' => [
                'filled',
                'numeric',
                'integer',
            ],
            'role_id' => [
                'filled',
                'numeric',
                'integer',
                // 'unique:submission_user',
                Rule::unique('submission_user')->where(function ($query) {
                    return $query
                        ->where('role_id', $this->arg('role_id'))
                        ->where('user_id', $this->arg('user_id'))
                        ->where('submission_id', $this->arg('submission_id'));
                }),
            ],
            // 'submission_user_unique' => [
            //     'unique',
            // ],
        ];
    }

    /**
     * Return messages for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'role_id.unique' => 'duplicate_entry'
        ];
    }
}
