<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use App\Models\OverallComment;
use App\Rules\CommentReplyCoherence;
use Nuwave\Lighthouse\Validation\Validator;

final class CreateOverallCommentValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        // Attached to submission_id (always present) so it runs for every create.
        // The parent / reply-target ids are read here from the input-scoped args
        // and injected — see CommentReplyCoherence on why a DataAwareRule is unfit
        // under @spread.
        return [
            'submission_id' => [
                new CommentReplyCoherence(
                    OverallComment::class,
                    $this->arg('parent_id'),
                    $this->arg('reply_to_id'),
                ),
            ],
        ];
    }
}
