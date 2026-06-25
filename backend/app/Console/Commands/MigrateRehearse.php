<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Auth\Roles\GlobalRole;
use App\Auth\Roles\ScopedRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Validate the RBAC -> ABAC cutover migrations against a real database — meant
 * to be run on a restored PRODUCTION CLONE before the real deploy, since CI only
 * ever migrates the (data-less) schema dump and so never exercises the porting
 * or backfill against live rows.
 *
 * Default mode asserts the FORWARD outcome (run after `php artisan migrate`):
 * the role slug is backfilled for every legacy row, role_id is retained and
 * nullable, and the Bouncer app-admin role exists with its assignees ported.
 *
 * `--rollback` additionally rehearses `migrate:rollback` then `migrate` to prove
 * the down() chain completes without aborting. It is destructive, so it refuses
 * to run on the production environment unless `--force` is also given.
 */
class MigrateRehearse extends Command
{
    /**
     * @var string
     */
    protected $signature = 'migrate:rehearse '
        . '{--rollback : also rehearse rollback then re-migrate (destructive)} '
        . '{--step=5 : migrations to roll back in --rollback mode} '
        . '{--force : allow --rollback on the production environment}';

    /**
     * @var string
     */
    protected $description = 'Validate the ABAC cutover migrations '
        . '(backfill, role_id retention, admin porting) on the current database.';

    /**
     * The role-assignment pivots the cutover touches.
     *
     * @var array<int, string>
     */
    private const PIVOTS = ['publication_user', 'submission_user', 'submission_invitations'];

    /**
     * Run the rehearsal and return a process exit code.
     *
     * @return int
     */
    public function handle(): int
    {
        $failures = $this->assertForwardInvariants();

        if ($this->option('rollback')) {
            $failures += $this->rehearseRollback();
        }

        if ($failures > 0) {
            $this->error("Rehearsal found {$failures} problem(s). Do NOT deploy until resolved.");

            return self::FAILURE;
        }

        $this->info('Rehearsal passed: migrations are consistent on this database.');

        return self::SUCCESS;
    }

    /**
     * The frozen legacy role_id -> slug map, derived from the role enums so the
     * command can never disagree with the live vocabulary.
     *
     * @return array<int, string>
     */
    private function legacyMap(): array
    {
        $map = [GlobalRole::ApplicationAdministrator->legacyId() => GlobalRole::ApplicationAdministrator->toSlug()];
        foreach (ScopedRole::cases() as $role) {
            $map[$role->legacyId()] = $role->toSlug();
        }
        ksort($map);

        return $map;
    }

    /**
     * Assert the post-migration outcome. Returns the number of violations.
     */
    private function assertForwardInvariants(): int
    {
        $failures = 0;
        $map = $this->legacyMap();

        foreach (self::PIVOTS as $table) {
            if (!Schema::hasTable($table)) {
                $this->error("[{$table}] table is missing.");
                $failures++;
                continue;
            }
            if (!Schema::hasColumn($table, 'role')) {
                $this->error("[{$table}] `role` slug column was not added.");
                $failures++;
                continue;
            }

            if (!Schema::hasColumn($table, 'role_id')) {
                $this->error("[{$table}] `role_id` is gone — this cutover RETAINS it as the "
                    . 'dual-write recovery net; dropping it is a later PR.');
                $failures++;
                continue;
            }

            if (!$this->columnIsNullable($table, 'role_id')) {
                $this->error("[{$table}] `role_id` is not nullable — the dual-write "
                    . 'recovery net needs it nullable but retained.');
                $failures++;
            }

            // Backfill completeness: every legacy row must carry the slug its
            // role_id maps to.
            foreach ($map as $id => $slug) {
                $bad = DB::table($table)
                    ->where('role_id', $id)
                    ->where(fn($q) => $q->whereNull('role')->orWhere('role', '!=', $slug))
                    ->count();
                if ($bad > 0) {
                    $this->error("[{$table}] {$bad} row(s) with role_id={$id} are not backfilled to '{$slug}'.");
                    $failures++;
                }
            }

            // Rows whose role_id is outside the known map: the migration cannot
            // have backfilled them, so the slug is whatever the app wrote (or null).
            $orphans = DB::table($table)
                ->whereNotNull('role_id')
                ->whereNotIn('role_id', array_keys($map))
                ->count();
            if ($orphans > 0) {
                $this->error("[{$table}] {$orphans} row(s) have an unmapped role_id (outside 1..6).");
                $failures++;
            }

            $unroled = DB::table($table)->whereNull('role')->count();
            if ($unroled > 0) {
                $this->warn("[{$table}] {$unroled} row(s) have a null `role` "
                    . '(no role_id to backfill from) — verify these are expected.');
            }

            $this->info("[{$table}] OK ({$this->rowCount($table)} rows).");
        }

        $failures += $this->assertAdminPorting();

        return $failures;
    }

    /**
     * Assert the Bouncer global app-admin role exists and report its assignees.
     */
    private function assertAdminPorting(): int
    {
        if (!Schema::hasTable('bouncer_roles') || !Schema::hasTable('bouncer_assigned_roles')) {
            $this->error('Bouncer tables are missing — create_bouncer_tables did not run.');

            return 1;
        }

        $slug = GlobalRole::ApplicationAdministrator->toSlug();
        $role = DB::table('bouncer_roles')->where('name', $slug)->first();
        if ($role === null) {
            $this->error("Bouncer role '{$slug}' is missing — seed_bouncer_application_admin_role did not run.");

            return 1;
        }

        $assignees = DB::table('bouncer_assigned_roles')
            ->where('role_id', $role->id)
            ->where('entity_type', User::class)
            ->count();

        // Zero is valid on a clone that never had an admin; surface it so an
        // operator who DID expect ported admins notices a silent porting miss.
        $this->info("Bouncer app-admin role present; {$assignees} administrator(s) assigned.");
        if ($assignees === 0) {
            $this->warn('No app administrators are assigned — confirm this clone '
                . 'genuinely had none before the cutover.');
        }

        return 0;
    }

    /**
     * Rehearse rollback then re-migrate, re-checking the forward invariants.
     * Destructive: guarded against the production environment.
     */
    private function rehearseRollback(): int
    {
        if ($this->getLaravel()->environment('production') && !$this->option('force')) {
            $this->error('--rollback is destructive and was refused on the production '
                . 'environment (pass --force to override on a clone).');

            return 1;
        }

        $step = (int)$this->option('step');
        $this->warn("Rehearsing rollback of {$step} migration(s), then re-migrating…");

        $rollback = $this->call('migrate:rollback', ['--step' => $step, '--force' => true]);
        if ($rollback !== 0) {
            $this->error('migrate:rollback aborted — the down() chain is not safe.');

            return 1;
        }

        $migrate = $this->call('migrate', ['--force' => true]);
        if ($migrate !== 0) {
            $this->error('re-migrate failed after rollback.');

            return 1;
        }

        $this->info('Rollback + re-migrate completed; re-checking forward invariants…');

        return $this->assertForwardInvariants();
    }

    /**
     * Whether a column is declared NULLABLE on the current MySQL connection.
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    private function columnIsNullable(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT IS_NULLABLE FROM information_schema.columns '
            . 'WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );

        return $row !== null && strtoupper($row->IS_NULLABLE) === 'YES';
    }

    /**
     * Total row count for a table.
     *
     * @param string $table
     * @return int
     */
    private function rowCount(string $table): int
    {
        return DB::table($table)->count();
    }
}
