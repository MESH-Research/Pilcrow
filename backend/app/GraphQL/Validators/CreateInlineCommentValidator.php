<?php
declare(strict_types=1);

namespace App\GraphQL\Validators;

use App\Models\InlineComment;
use App\Rules\CommentReplyCoherence;
use Nuwave\Lighthouse\Validation\Validator;

final class CreateInlineCommentValidator extends Validator
{
    /**
     * Return the validation rules.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        // The coherence rule reads parent_id / reply_to_id / submission_id from
        // the args itself, so a single attachment validates the whole thread.
        return [
            'parent_id' => [
                new CommentReplyCoherence(InlineComment::class),
            ],
        ];
    }
}
