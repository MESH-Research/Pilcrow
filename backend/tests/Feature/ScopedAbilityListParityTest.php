<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Auth\Abilities\PublicationAbility;
use App\Auth\Abilities\ScopedAbility;
use App\Auth\Abilities\SubmissionAbility;
use App\Auth\Roles\GlobalRole;
use App\Auth\Roles\ScopedRole;
use App\Auth\ScopedAbilityResolver;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

/**
 * The Tier 1 unification guarantee: SQL list-filtering (Builder::whereCan)
 * derives from the same ScopedRole matrix as item authorization
 * (ScopedAbilityResolver::allows), so the two cannot diverge. These tests assert
 * that equivalence directly — for every actor, the listed set equals the set the
 * resolver would individually permit.
 */
class ScopedAbilityListParityTest extends TestCase
{
    use RefreshDatabase;

    private ScopedAbilityResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new ScopedAbilityResolver();
    }

    public function testRolesGrantingInvertsTheMatrix(): void
    {
        $this->assertEqualsCanonicalizing(
            [
                ScopedRole::PublicationAdmin,
                ScopedRole::Editor,
                ScopedRole::ReviewCoordinator,
                ScopedRole::Reviewer,
                ScopedRole::Submitter,
            ],
            ScopedRole::rolesGranting(SubmissionAbility::View)
        );

        $this->assertEqualsCanonicalizing(
            [ScopedRole::PublicationAdmin, ScopedRole::Editor],
            ScopedRole::rolesGranting(PublicationAbility::View)
        );

        $this->assertEqualsCanonicalizing(
            [ScopedRole::PublicationAdmin],
            ScopedRole::rolesGranting(PublicationAbility::Update)
        );
    }

    public function testConditionalAbilityIsNotListFilterable(): void
    {
        // submission.update-status is a DRAFT-only (conditional) grant for the
        // submitter; a predicate has no SQL form, so Tier 1 refuses it loudly.
        $this->expectException(LogicException::class);
        ScopedRole::rolesGranting(SubmissionAbility::UpdateStatus);
    }

    public function testSubmissionVisibleListMatchesResolver(): void
    {
        $pubA = Publication::factory()->create(['is_publicly_visible' => false]);
        $pubB = Publication::factory()->create(['is_publicly_visible' => false]);
        $subA1 = Submission::factory()->for($pubA)->create();
        $subA2 = Submission::factory()->for($pubA)->create();
        $subB1 = Submission::factory()->for($pubB)->create();
        $all = [$subA1, $subA2, $subB1];

        $editor = User::factory()->create();
        $pubA->editors()->save($editor);

        $reviewer = User::factory()->create();
        $subA1->reviewers()->save($reviewer);

        $submitter = User::factory()->create();
        $subB1->submitters()->save($submitter);

        $outsider = User::factory()->create();

        $admin = User::factory()->create();
        $admin->assignRole(GlobalRole::ApplicationAdministrator);

        foreach ([$editor, $reviewer, $submitter, $outsider, $admin] as $user) {
            $this->assertSubmissionListMatchesResolver($user, SubmissionAbility::View, $all);
        }
    }

    public function testPublicationWhereCanMatchesResolver(): void
    {
        $pubA = Publication::factory()->create(['is_publicly_visible' => false]);
        $pubB = Publication::factory()->create(['is_publicly_visible' => false]);
        $all = [$pubA, $pubB];

        $editor = User::factory()->create();
        $pubA->editors()->save($editor);

        $admin = User::factory()->create();
        $pubB->publicationAdmins()->save($admin);

        $outsider = User::factory()->create();

        $appAdmin = User::factory()->create();
        $appAdmin->assignRole(GlobalRole::ApplicationAdministrator);

        foreach ([$editor, $admin, $outsider, $appAdmin] as $user) {
            $this->actingAs($user);
            $listed = Publication::query()->whereCan(PublicationAbility::View)
                ->pluck('id')->sort()->values()->all();
            $expected = collect($all)
                ->filter(fn(Publication $p) => $this->resolver->allows($user->fresh(), PublicationAbility::View, $p))
                ->pluck('id')->sort()->values()->all();

            $this->assertEquals($expected, $listed, "publication whereCan must match resolver for user {$user->id}");
        }
    }

    /**
     * whereCan must deny-all for an unauthenticated viewer (the `1 = 0` branch),
     * for both builders — a guest must never leak a private list.
     */
    public function testWhereCanListsNothingForGuest(): void
    {
        $pub = Publication::factory()->create(['is_publicly_visible' => false]);
        Submission::factory()->for($pub)->create();

        // No actingAs: guest.
        $this->assertSame([], Publication::query()->whereCan(PublicationAbility::View)->pluck('id')->all());
        $this->assertSame([], Submission::query()->whereCan(SubmissionAbility::View)->pluck('id')->all());
    }

    /**
     * The resolver resolves no effective roles when there is no entity in play —
     * the null-entity guard, so a scoped check without an entity denies.
     */
    public function testEffectiveRolesIsEmptyWithoutEntity(): void
    {
        $user = User::factory()->create();

        $this->assertSame([], $this->resolver->effectiveRoles($user, null));
    }

    /**
     * @param \App\Models\User $user
     * @param \App\Auth\Abilities\ScopedAbility $ability
     * @param array<int, \App\Models\Submission> $all
     * @return void
     */
    private function assertSubmissionListMatchesResolver(User $user, ScopedAbility $ability, array $all): void
    {
        $this->actingAs($user);

        $listed = Submission::query()->whereCan($ability)
            ->pluck('id')->sort()->values()->all();

        $expected = collect($all)
            ->filter(fn(Submission $s) => $this->resolver->allows($user->fresh(), $ability, $s))
            ->pluck('id')->sort()->values()->all();

        $this->assertEquals($expected, $listed, "submission whereCan must match resolver for user {$user->id}");
    }
}
