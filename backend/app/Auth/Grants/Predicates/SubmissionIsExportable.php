<?php
declare(strict_types=1);

namespace App\Auth\Grants\Predicates;

use App\Auth\Grants\Predicate;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Holds when the entity is a submission in a status from which it may be
 * exported — a terminal or returned-to-author state where the review record is
 * settled. Backs the conditional grant on submission.export, so the export
 * ability tracks the submission's status instead of the client hard-coding the
 * set.
 */
final class SubmissionIsExportable implements Predicate
{
    /**
     * The statuses an exporter may pull a submission from.
     *
     * @var array<int, int>
     */
    public const EXPORTABLE_STATUSES = [
        Submission::RESUBMISSION_REQUESTED,
        Submission::REJECTED,
        Submission::ACCEPTED_AS_FINAL,
        Submission::EXPIRED,
        Submission::ARCHIVED,
    ];

    /**
     * @param \Illuminate\Database\Eloquent\Model $entity
     * @param \App\Models\User $_user
     * @return bool
     */
    public function holds(Model $entity, User $_user): bool
    {
        return $entity instanceof Submission
            && in_array($entity->status, self::EXPORTABLE_STATUSES, true);
    }
}
