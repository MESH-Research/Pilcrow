<?php
declare(strict_types=1);

namespace App\Auth\Roles;

use App\Auth\Abilities\PublicationAbility;
use App\Auth\Abilities\ScopedAbility;
use App\Auth\Abilities\SubmissionAbility;
use App\Auth\Grants\Grant;
use App\Auth\Grants\Predicates\SubmissionIsDraft;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use UnitEnum;

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
 * global roles (App\Auth\Roles\GlobalRole, e.g. application_admin), which is intentionally
 * NOT a case here.
 */
enum ScopedRole: string
{
    /** Roles assigned through the `publication_user` pivot. */
    public const PIVOT_PUBLICATION = 'publication';

    /** Roles assigned through the `submission_user` pivot. */
    public const PIVOT_SUBMISSION = 'submission';

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
     * @return array<int, \App\Auth\Grants\Grant>
     */
    public function grants(): array
    {
        return array_map([self::class, 'toGrant'], $this->grantDefinitions());
    }

    /**
     * The pivot a role is assigned through — the inverse of the resolver's
     * per-entity lookup, used by list-filtering to know which membership table
     * to join. {@see self::PIVOT_PUBLICATION} / {@see self::PIVOT_SUBMISSION}.
     *
     * @return string
     */
    public function pivotName(): string
    {
        return match ($this) {
            self::PublicationAdmin, self::Editor => self::PIVOT_PUBLICATION,
            self::ReviewCoordinator, self::Reviewer, self::Submitter => self::PIVOT_SUBMISSION,
        };
    }

    /**
     * The roles that grant the ability **absolutely** (no predicate) — the
     * matrix inverted by ability. This is the single source of truth shared
     * between item authorization ({@see ScopedAbilityResolver}) and SQL
     * list-filtering, so the two cannot drift.
     *
     * Tier 1 list-filtering only supports unconditional abilities: a predicate
     * lives in PHP and has no SQL form, so a conditional grant for the requested
     * ability throws rather than silently filtering incorrectly.
     *
     * @param \App\Auth\Abilities\ScopedAbility $ability
     * @return array<int, \App\Auth\Roles\ScopedRole>
     * @throws \LogicException if any role grants the ability conditionally
     */
    public static function rolesGranting(ScopedAbility $ability): array
    {
        $roles = [];
        foreach (self::cases() as $role) {
            foreach ($role->grantDefinitions() as $definition) {
                if ($definition instanceof ScopedAbility) {
                    if ($definition === $ability) {
                        $roles[] = $role;
                    }
                    continue;
                }
                if ($definition[0] === $ability) {
                    $label = $ability instanceof UnitEnum ? $ability->name : 'ability';
                    throw new LogicException(
                        "Scoped ability {$label} has a conditional grant on {$role->name}; "
                        . 'it is not list-filterable (Tier 1 supports unconditional abilities only).'
                    );
                }
            }
        }

        return $roles;
    }

    /**
     * The pivot `role` slugs that grant the ability, restricted to one pivot.
     *
     * @param \App\Auth\Abilities\ScopedAbility $ability
     * @param string $pivot one of the PIVOT_* constants
     * @return array<int, string>
     */
    public static function grantingSlugsFor(ScopedAbility $ability, string $pivot): array
    {
        $slugs = [];
        foreach (self::rolesGranting($ability) as $role) {
            if ($role->pivotName() === $pivot) {
                $slugs[] = $role->toSlug();
            }
        }

        return $slugs;
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
     * @return array<int, \App\Auth\Abilities\ScopedAbility|array{0: \App\Auth\Abilities\ScopedAbility, 1: class-string<\App\Auth\Grants\Predicate>}>
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
     * @param \App\Auth\Abilities\ScopedAbility|array{0: \App\Auth\Abilities\ScopedAbility, 1: class-string<\App\Auth\Grants\Predicate>} $definition
     * @return \App\Auth\Grants\Grant
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
     * @param \App\Auth\Abilities\ScopedAbility $ability
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
     * The role slug — the value stored in the pivot `role` column and the
     * backing value of this enum. An intent-revealing alias for ->value, named
     * to match {@see \App\Auth\Roles\GlobalRole::toSlug()}.
     *
     * @return string
     */
    public function toSlug(): string
    {
        return $this->value;
    }

    /**
     * The legacy integer `role_id` for this role. The slug is now the source of
     * truth, but the integer is still written alongside it into the retained
     * `role_id` pivot column so a rollback to the pre-slug code finds valid data.
     *
     * @return int
     */
    public function legacyId(): int
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
     * Privilege rank for the `highest_privileged_role` UI hint (lower ranks
     * higher), continuing the scale below the global administrator. By
     * construction this is the legacy role id — a UI hint, not authorization.
     *
     * @see \App\Auth\Roles\GlobalRole::rank()
     * @return int
     */
    public function rank(): int
    {
        return $this->legacyId();
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
