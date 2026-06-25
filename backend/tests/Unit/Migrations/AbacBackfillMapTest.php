<?php
declare(strict_types=1);

namespace Tests\Unit\Migrations;

use App\Auth\Roles\GlobalRole;
use App\Auth\Roles\ScopedRole;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

/**
 * The add_role_slug_to_pivots migration backfills the new `role` slug column
 * from the legacy integer `role_id` using a frozen literal map. That map is the
 * single point where a wrong number silently mis-assigns every historical row's
 * role on upgrade — a data-integrity bug no item-level test would catch.
 *
 * This locks the migration's map to the role vocabulary (ScopedRole /
 * GlobalRole), so the migration and the enums can never drift apart unnoticed.
 * Pure reflection over the migration object — no database, safe on the shared
 * MySQL test connection (the live forward/rollback rehearsal lives in the
 * `migrate:rehearse` command, which needs DDL and so runs against a clone).
 */
class AbacBackfillMapTest extends TestCase
{
    /**
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function migrationInternals(): array
    {
        $migration = require dirname(__DIR__, 3)
            . '/database/migrations/2026_06_16_165000_add_role_slug_to_pivots.php';

        $ref = new ReflectionObject($migration);

        $mapProp = $ref->getProperty('map');
        $mapProp->setAccessible(true);
        $tablesProp = $ref->getProperty('tables');
        $tablesProp->setAccessible(true);

        return [$mapProp->getValue($migration), $tablesProp->getValue($migration)];
    }

    public function testBackfillMapMatchesTheFrozenVocabulary(): void
    {
        [$map] = $this->migrationInternals();

        $this->assertSame([
            1 => 'application_admin',
            2 => 'publication_admin',
            3 => 'editor',
            4 => 'review_coordinator',
            5 => 'reviewer',
            6 => 'submitter',
        ], $map, 'The role_id -> slug backfill map drifted from the documented vocabulary.');
    }

    public function testScopedSlugsRoundTripThroughTheEnum(): void
    {
        [$map] = $this->migrationInternals();

        // Every scoped id (2..6) must map to a real ScopedRole whose own
        // legacyId() agrees with the migration — the resolver reads the slug,
        // the migration writes it, so a mismatch corrupts authorization on
        // upgraded rows.
        foreach ([2, 3, 4, 5, 6] as $id) {
            $role = ScopedRole::tryFrom($map[$id]);
            $this->assertNotNull($role, "Backfill slug '{$map[$id]}' is not a ScopedRole case.");
            $this->assertSame($id, $role->legacyId(), "ScopedRole {$role->name} legacyId disagrees with the backfill map.");
        }
    }

    public function testGlobalSlugRoundTripsThroughTheEnum(): void
    {
        [$map] = $this->migrationInternals();

        $this->assertSame(GlobalRole::ApplicationAdministrator->toSlug(), $map[1]);
        $this->assertSame(1, GlobalRole::ApplicationAdministrator->legacyId());
        $this->assertNull(ScopedRole::tryFrom($map[1]), 'application_admin must NOT be a scoped role.');
    }

    public function testBackfillCoversEveryRoleAssignmentPivot(): void
    {
        [, $tables] = $this->migrationInternals();

        $this->assertEqualsCanonicalizing(
            ['publication_user', 'submission_user', 'submission_invitations'],
            $tables,
            'A role-bearing pivot is missing from the backfill (its rows would keep a null slug).'
        );
    }
}
