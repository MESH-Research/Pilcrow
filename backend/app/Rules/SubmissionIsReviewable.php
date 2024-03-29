<?php
declare(strict_types=1);

namespace App\Rules;

use App\Models\Submission;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class SubmissionIsReviewable implements ValidationRule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Set the data under validation
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $submission = Submission::where('id', $this->data['input']['id'])->firstOrFail();
        if ($submission->status !== Submission::UNDER_REVIEW) {
            $fail('The submission is not in a reviewable state.');
        }
    }
}
