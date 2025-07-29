<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\MetaPage;
use App\Models\SubmissionMetaResponse;

final readonly class SubmissionMetaPageUpdate
{
    /** @param  array{}  $_ */
    public function __invoke(null $_, array $args)
    {

        $response = SubmissionMetaResponse::where([
            'submission_id' => $args['input']['submission_id'],
            'meta_page_id' => $args['input']['meta_page_id'],
        ])->first();

        if (!$response) {
            $response = new SubmissionMetaResponse();
            $response->meta_page_id = $args['input']['meta_page_id'];
            $response->submission_id = $args['input']['submission_id'];
        }

        $response->responses = $args['input']['responses'];
        $response->prompts = MetaPage::findOrFail($args['input']['meta_page_id'])->metaPrompts->toArray();

        $response->save();

        return $response;
    }
}
