<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\MetaForm;
use App\Models\SubmissionMetaResponse;

final readonly class SubmissionMetaFormUpdate
{
    /**
     * Handle the mutation to update or create a submission meta response.
     *
     * @param null $_ Unused root parameter for GraphQL resolver signature
     * @param array $args The arguments passed to the mutation
     * @return \App\Models\SubmissionMetaResponse The updated or created submission meta response
     */
    public function __invoke(null $_, array $args)
    {
        $response = SubmissionMetaResponse::where([
            'submission_id' => $args['input']['submission_id'],
            'meta_form_id' => $args['input']['meta_form_id'],
        ])->first();

        if (!$response) {
            $response = new SubmissionMetaResponse();
            $response->meta_form_id = $args['input']['meta_form_id'];
            $response->submission_id = $args['input']['submission_id'];
        }

        $response->responses = $args['input']['responses'];
        $response->prompts = MetaForm::findOrFail($args['input']['meta_form_id'])->metaPrompts->toArray();

        $response->save();

        return $response;
    }
}
