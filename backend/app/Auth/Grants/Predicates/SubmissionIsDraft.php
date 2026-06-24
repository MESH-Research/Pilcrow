<?php
declare(strict_types=1);

namespace App\Auth\Grants\Predicates;

use App\Auth\Grants\Predicate;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Holds when the entity is a submission still in DRAFT. Backs the submitter's
 * conditional grant on submission.update-status.
 */
final class SubmissionIsDraft implements Predicate
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Models\User $_user
     * @return bool
     */
    public function holds(Model $entity, User $_user): bool
    {
        return $entity instanceof Submission && $entity->status === Submission::DRAFT;
    }
}
