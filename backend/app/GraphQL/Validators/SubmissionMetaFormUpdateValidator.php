<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use App\Enums\MetaPromptType;
use App\Models\MetaForm;
use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

final class SubmissionMetaFormUpdateValidator extends Validator
{
    protected MetaForm $metaForm;

    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'submission_id' => ['required', Rule::exists('submissions', 'id')],
            'meta_form_id' => ['required', Rule::exists('meta_forms', 'id')],
            'responses' => ['required', 'array', 'min:1', fn($_, $value, $fail) => $this->checkPage($value, $fail)],
            'responses.*' => [fn($_, $value, $fail) => $this->checkResponse($value, $fail)],
        ];
    }

    /**
     * Validate the responses against the meta form prompts.
     *
     * @param mixed $value The value of the responses being validated
     * @param \Closure $fail The closure to call if validation fails
     * @return void
     */
    public function checkPage(mixed $value, \Closure $fail): void
    {
        $responses = collect($value);

        // Fetch the meta form and hang on to it for later.
        $this->metaForm = MetaForm::find($this->arg('meta_form_id'));

        // Validate the the meta form exists.
        if (!$this->metaForm) {
            $fail('The selected meta form does not exist.');

            return;
        }

        // Extract the IDs of the prompts from the meta form.
        $promptIds = $this->metaForm->metaPrompts->pluck('id')->toArray();

        // Check if the responses contain duplicate meta prompt IDs.
        if (count(array_unique($promptIds)) !== count($promptIds)) {
            $fail('Duplicate meta prompt IDs in responses are not allowed.');

            return;
        }

        // Ensure all responses correspond to valid meta prompts of the selected meta form.
        if (!$responses->every(fn($response) => in_array($response['meta_prompt_id'], $promptIds))) {
            $fail('All responses must correspond to valid meta prompts of the selected meta form.');

            return;
        }
        // Ensure that each prompt in the page has a response.
        $complete = $this->metaForm
            ->metaPrompts->every(
                fn($prompt) => $responses->firstWhere('meta_prompt_id', $prompt->id)
            );

        if (!$complete) {
            $fail('Must provide a response for each prompt in the selected meta form.');

            return;
        }
    }

    /**
     * Validate a single response against its corresponding meta prompt.
     *
     * @param mixed $value The value of the response being validated
     * @param \Closure $fail The closure to call if validation fails
     * @return void
     */
    public function checkResponse(mixed $value, \Closure $fail): void
    {
        // Fetch the meta prompt from our stored meta form.
        $prompt = $this->metaForm->metaPrompts->firstWhere('id', $value['meta_prompt_id']);

        // If the prompt is a select type, ensure the response is valid.
        if ($prompt->type == MetaPromptType::SELECT->value) {
            $options = $prompt->options['options'] ?? [];
            if (!in_array($value['response'], $options)) {
                $fail("The response '{$value['response']}' is not a valid option for the selected meta prompt.");

                return;
            }
        }

        // If the prompt is required, ensure a response is provided.
        if ($prompt->required && empty($value['response'])) {
            $fail("The response for meta prompt ID {$value['meta_prompt_id']} is required but was not provided.");

            return;
        }
    }
}
