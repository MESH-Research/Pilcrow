<?php
declare(strict_types=1);

namespace App\Auth;

use App\Auth\Predicates\SubmissionIsDraft;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * The scoped (publication / submission) roles, as a type.
 *
 * These are deliberately NOT Bouncer roles and have no rows in bouncer_roles.
 * A user holds a scoped role per-entity through the publication_user /
 * submission_user pivots (the integer role_id column, which is this enum's
 * backing value); AbilityResolver maps that role_id to a case via
 * ScopedRole::tryFrom() and asks it what it grants. The role -> ability map is
 * code: each case returns its list of {@see Grant}s. Nothing here is stored in,
 * seeded into, or assignable through Bouncer — that is reserved for genuinely
 * global roles (App\Models\Role, e.g. application_admin), which is intentionally
 * NOT a case here.
 */
enum ScopedRole: int
{
    case PublicationAdmin = 2;
    case Editor = 3;
    case ReviewCoordinator = 4;
    case Reviewer = 5;
    case Submitter = 6;

    /**
     * The grants this role confers.
     *
     * Roles compose as supersets: each builds on the one below it by spreading
     * its grants, so "everything role B has, plus X" is explicit. The submitter
     * is not in the coordinator chain — it extends the reviewer with title /
     * submitter edits and a DRAFT-only status change (a conditional grant).
     *
     * @return array<int, \App\Auth\Grant>
     */
    public function grants(): array
    {
        return match ($this) {
            self::Reviewer => [
                new Grant(Ability::SubmissionView),
                new Grant(Ability::SubmissionUpdate),
            ],
            self::ReviewCoordinator => [
                ...self::Reviewer->grants(),
                new Grant(Ability::SubmissionUpdateSubmitters),
                new Grant(Ability::SubmissionUpdateReviewers),
                new Grant(Ability::SubmissionUpdateStatus),
                new Grant(Ability::SubmissionUpdateTitle),
                new Grant(Ability::SubmissionInvite),
            ],
            self::Editor => [
                ...self::ReviewCoordinator->grants(),
                new Grant(Ability::PublicationView),
                new Grant(Ability::SubmissionUpdateReviewCoordinators),
            ],
            self::PublicationAdmin => [
                ...self::Editor->grants(),
                new Grant(Ability::PublicationUpdate),
            ],
            self::Submitter => [
                ...self::Reviewer->grants(),
                new Grant(Ability::SubmissionUpdateSubmitters),
                new Grant(Ability::SubmissionUpdateTitle),
                new Grant(Ability::SubmissionUpdateStatus, new SubmissionIsDraft()),
            ],
        };
    }

    /**
     * Does this role grant the ability for the entity / acting user? Resolves
     * both absolute and conditional grants.
     *
     * @param \App\Auth\Ability $ability
     * @param \Illuminate\Database\Eloquent\Model|null $entity
     * @param \App\Models\User $user
     * @return bool
     */
    public function allows(Ability $ability, ?Model $entity, User $user): bool
    {
        foreach ($this->grants() as $grant) {
            if ($grant->permits($ability, $entity, $user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The value stored in the pivot `role_id` column for this role. This is the
     * enum's backing value, surfaced through an intent-revealing method so pivot
     * reads/writes and queries don't reach for the raw ->value.
     *
     * @return int
     */
    public function pivotValue(): int
    {
        return $this->value;
    }

    /**
     * Human-readable title (the Bouncer-era display name), used where a scoped
     * role is surfaced by title rather than id.
     *
     * @return string
     */
    public function title(): string
    {
        return match ($this) {
            self::PublicationAdmin => 'Publication Administrator',
            self::Editor => 'Editor',
            self::ReviewCoordinator => 'Review Coordinator',
            self::Reviewer => 'Reviewer',
            self::Submitter => 'Submitter',
        };
    }
}
