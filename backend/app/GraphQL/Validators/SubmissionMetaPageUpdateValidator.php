<?php

declare(strict_types=1);

namespace App\GraphQL\Validators;

use App\Enums\MetaPromptType;
use App\Models\MetaPage;
use Illuminate\Validation\Rule;
use Nuwave\Lighthouse\Validation\Validator;

final class SubmissionMetaPageUpdateValidator extends Validator
{

    protected MetaPage $metaPage;

    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'submission_id' => ['required', Rule::exists('submissions', 'id')],
            'meta_page_id' => ['required', Rule::exists('meta_pages', 'id')],
            'responses' => ['required', 'array', 'min:1', fn($attribute, $value, $fail) => $this->validateResponses($attribute, $value, $fail)],
            'responses.*' => [fn($attribute, $value, $fail) => $this->validateResponse($attribute, $value, $fail)]
        ];
    }

    public function validateResponses(string $attribute, mixed $value, \Closure $fail): void
    {
        $responses = collect($value);

        // Fetch the meta page and hang on to it for later.
        $this->metaPage = MetaPage::find($this->arg('meta_page_id'));

        // Validate the the meta page exists.
        if (!$this->metaPage) {
            $fail("The selected meta page does not exist.");
            return;
        }

        // Extract the IDs of the prompts from the meta page.
        $promptIds = $this->metaPage->metaPrompts->pluck('id')->toArray();

        // Check if the responses contain duplicate meta prompt IDs.
        if (count(array_unique($promptIds)) !== count($promptIds)) {
            $fail("Duplicate meta prompt IDs in responses are not allowed.");
            return;
        }

        // Ensure all responses correspond to valid meta prompts of the selected meta page.
        if (!$responses->every(fn($response) => in_array($response['meta_prompt_id'], $promptIds))) {
            $fail("All responses must correspond to valid meta prompts of the selected meta page.");
            return;
        }
        // Ensure that each prompt in the page has a response.
        if (!$this->metaPage->metaPrompts->every(fn($prompt) => $responses->firstWhere('meta_prompt_id', $prompt->id))) {
            $fail("Must provide a response for each prompt in the selected meta page.");
            return;
        }
    }

    public function validateResponse(string $attribute, mixed $value, \Closure $fail): void
    {
        // Fetch the meta prompt from our stored meta page.
        $prompt = $this->metaPage->metaPrompts->firstWhere('id', $value['meta_prompt_id']);

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
