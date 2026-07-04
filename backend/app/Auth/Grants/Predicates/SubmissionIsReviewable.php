<?php
declare(strict_types=1);

namespace App\Auth\Grants\Predicates;

use App\Auth\Grants\Predicate;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Holds when the entity is a submission that is reviewable — i.e. UNDER_REVIEW.
 * Backs the reviewer's conditional grant on submission.review, which is the
 * single gate for accessing the manuscript and posting comments. The former
 * `App\Rules\SubmissionIsReviewable` validation rule for comment creation is
 * folded into this predicate so "when may one comment" lives in one place.
 */
final class SubmissionIsReviewable implements Predicate
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Models\User $_user
     * @return bool
     */
    public function holds(Model $entity, User $_user): bool
    {
        return $entity instanceof Submission && $entity->isReviewable();
    }
}
