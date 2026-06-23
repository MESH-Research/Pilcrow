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
 * submission_user pivots (the `role` slug column, which is this enum's backing
 * value); ScopedAbilityResolver maps that slug to a case via
 * ScopedRole::tryFrom() and asks it what it grants. The role -> ability map is
 * code: each case returns its list of {@see Grant}s. Nothing here is stored in,
 * seeded into, or assignable through Bouncer — that is reserved for genuinely
 * global roles (App\Auth\GlobalRole, e.g. application_admin), which is intentionally
 * NOT a case here.
 */
enum ScopedRole: string
{
    case PublicationAdmin = 'publication_admin';
    case Editor = 'editor';
    case ReviewCoordinator = 'review_coordinator';
    case Reviewer = 'reviewer';
    case Submitter = 'submitter';

    /**
     * The grants this role confers.
     *
     * Authored in shorthand by {@see grantDefinitions} — a bare ScopedAbility is an
     * absolute grant, an [ScopedAbility, PredicateClass] pair a conditional one — and
     * normalized to {@see Grant} objects here.
     *
     * @return array<int, \App\Auth\Grant>
     */
    public function grants(): array
    {
        return array_map([self::class, 'toGrant'], $this->grantDefinitions());
    }

    /**
     * The role's grants in shorthand. Each entry is either a ScopedAbility (absolute
     * grant) or an [ScopedAbility, Predicate class-string] pair (conditional grant).
     *
     * Roles compose as supersets: each spreads the one below it, so "everything
     * role B has, plus X" is explicit. The submitter is not in the coordinator
     * chain — it extends the reviewer with title / submitter edits and a
     * DRAFT-only status change (a conditional grant).
     *
     * @return array<int, \App\Auth\ScopedAbility|array{0: \App\Auth\ScopedAbility, 1: class-string<\App\Auth\Predicate>}>
     */
    private function grantDefinitions(): array
    {
        return match ($this) {
            self::Reviewer => [
                SubmissionAbility::View,
                SubmissionAbility::Update,
            ],
            self::ReviewCoordinator => [
                ...self::Reviewer->grantDefinitions(),
                SubmissionAbility::UpdateSubmitters,
                SubmissionAbility::UpdateReviewers,
                SubmissionAbility::UpdateStatus,
                SubmissionAbility::UpdateTitle,
                SubmissionAbility::Invite,
            ],
            self::Editor => [
                ...self::ReviewCoordinator->grantDefinitions(),
                PublicationAbility::View,
                SubmissionAbility::UpdateReviewCoordinators,
            ],
            self::PublicationAdmin => [
                ...self::Editor->grantDefinitions(),
                PublicationAbility::Update,
            ],
            self::Submitter => [
                ...self::Reviewer->grantDefinitions(),
                SubmissionAbility::UpdateSubmitters,
                SubmissionAbility::UpdateTitle,
                [SubmissionAbility::UpdateStatus, SubmissionIsDraft::class],
            ],
        };
    }

    /**
     * Normalize a shorthand grant definition into a {@see Grant}, instantiating
     * the predicate class for conditional grants.
     *
     * @param \App\Auth\ScopedAbility|array{0: \App\Auth\ScopedAbility, 1: class-string<\App\Auth\Predicate>} $definition
     * @return \App\Auth\Grant
     */
    private static function toGrant(ScopedAbility|array $definition): Grant
    {
        if ($definition instanceof ScopedAbility) {
            return new Grant($definition);
        }

        [$ability, $predicateClass] = $definition;

        return new Grant($ability, new $predicateClass());
    }

    /**
     * Does this role grant the ability for the entity / acting user? Resolves
     * both absolute and conditional grants.
     *
     * The shorthand ability is compared first, so a Grant (and its predicate)
     * is only instantiated for a definition whose ability actually matches —
     * predicates for unrelated abilities are never constructed.
     *
     * @param \App\Auth\ScopedAbility $ability
     * @param \Illuminate\Database\Eloquent\Model|null $entity
     * @param \App\Models\User $user
     * @return bool
     */
    public function allows(ScopedAbility $ability, ?Model $entity, User $user): bool
    {
        foreach ($this->grantDefinitions() as $definition) {
            $grantAbility = $definition instanceof ScopedAbility ? $definition : $definition[0];
            if ($grantAbility !== $ability) {
                continue;
            }

            if (self::toGrant($definition)->permits($ability, $entity, $user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The value stored in the pivot `role` column for this role — the role slug.
     * This is the enum's backing value, surfaced through an intent-revealing
     * method so pivot reads/writes and queries don't reach for the raw ->value.
     *
     * @return string
     */
    public function pivotValue(): string
    {
        return $this->value;
    }

    /**
     * Privilege rank for the `highest_privileged_role` UI hint (lower ranks
     * higher), continuing the scale below the global administrator. These are the
     * legacy role ids, retained only as the GraphQL `UserRoles` display values —
     * a UI hint, not authorization and not a stored identifier.
     *
     * @see \App\Auth\GlobalRole::rank()
     * @return int
     */
    public function rank(): int
    {
        return match ($this) {
            self::PublicationAdmin => 2,
            self::Editor => 3,
            self::ReviewCoordinator => 4,
            self::Reviewer => 5,
            self::Submitter => 6,
        };
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
