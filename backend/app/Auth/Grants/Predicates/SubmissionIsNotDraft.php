<?php
declare(strict_types=1);

namespace App\Auth\Grants\Predicates;

use App\Auth\Grants\Predicate;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Holds when the entity is a submission that has left DRAFT — the inverse of
 * {@see SubmissionIsDraft}. Backs the editorial side of the submitter -> editorial
 * co-submitter handoff (RC+ may manage co-submitters once the draft is submitted).
 */
final class SubmissionIsNotDraft implements Predicate
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Models\User $_user
     * @return bool
     */
    public function holds(Model $entity, User $_user): bool
    {
        return $entity instanceof Submission && ! $entity->isDraft();
    }
}
