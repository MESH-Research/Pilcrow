<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Auth\ScopedRole;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Direct coverage for PublicationBuilder scopes. Some of these (visible,
 * myRole) are consumed by the publications GraphQL query but are exercised
 * here against the query builder so the behaviour is locked in independent of
 * the schema wiring.
 */
class PublicationBuilderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * search matches publications whose name contains the term and is
     * case-insensitive at the database level.
     *
     * @return void
     */
    public function test_search_matches_name_substring(): void
    {
        $match = Publication::factory()->create(['name' => 'Quarterly Review']);
        Publication::factory()->create(['name' => 'Annual Digest']);

        $results = Publication::query()->search('quarter')->get();

        $this->assertEquals([$match->id], $results->pluck('id')->all());
    }

    /**
     * search is a no-op when given a null or empty term, returning the full
     * set rather than filtering everything out.
     *
     * @return void
     */
    public function test_search_returns_all_when_term_is_empty(): void
    {
        Publication::factory()->count(3)->create();

        $this->assertCount(3, Publication::query()->search(null)->get());
        $this->assertCount(3, Publication::query()->search('')->get());
    }

    /**
     * public(true) returns only publicly visible publications; public(false)
     * returns only hidden ones.
     *
     * @return void
     */
    public function test_public_filters_by_visibility(): void
    {
        $visible = Publication::factory()->create();
        $hidden = Publication::factory()->hidden()->create();

        $this->assertEquals(
            [$visible->id],
            Publication::query()->public()->get()->pluck('id')->all()
        );
        $this->assertEquals(
            [$hidden->id],
            Publication::query()->public(false)->get()->pluck('id')->all()
        );
    }

    /**
     * acceptingSubmissions(true) returns only open publications;
     * acceptingSubmissions(false) returns only closed ones.
     *
     * @return void
     */
    public function test_accepting_submissions_filters_by_state(): void
    {
        $open = Publication::factory()->create(['is_accepting_submissions' => true]);
        $closed = Publication::factory()->create(['is_accepting_submissions' => false]);

        $this->assertEquals(
            [$open->id],
            Publication::query()->acceptingSubmissions()->get()->pluck('id')->all()
        );
        $this->assertEquals(
            [$closed->id],
            Publication::query()->acceptingSubmissions(false)->get()->pluck('id')->all()
        );
    }

    /**
     * visible shows guests only publicly visible publications.
     *
     * @return void
     */
    public function test_visible_restricts_guests_to_public_publications(): void
    {
        $public = Publication::factory()->create();
        Publication::factory()->hidden()->create();

        $results = Publication::query()->visible()->get();

        $this->assertEquals([$public->id], $results->pluck('id')->all());
    }

    /**
     * visible shows application administrators every publication, public or
     * hidden, with no membership required.
     *
     * @return void
     */
    public function test_visible_shows_application_administrators_everything(): void
    {
        Publication::factory()->create();
        Publication::factory()->hidden()->create();

        $this->beAppAdmin();

        $this->assertCount(2, Publication::query()->visible()->get());
    }

    /**
     * visible shows a non-admin user public publications plus any hidden
     * publication they hold a role on, and excludes hidden publications they
     * have no relationship to.
     *
     * @return void
     */
    public function test_visible_includes_public_plus_users_private_publications(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $public = Publication::factory()->create();
        $ownHidden = Publication::factory()->hidden()->create();
        $ownHidden->editors()->save($user);
        Publication::factory()->hidden()->create();

        $results = Publication::query()->visible()->get();

        $this->assertEqualsCanonicalizing(
            [$public->id, $ownHidden->id],
            $results->pluck('id')->all()
        );
    }

    /**
     * myRole restricts publications to those the authenticated user holds one
     * of the given roles on.
     *
     * @return void
     */
    public function test_my_role_filters_to_users_role_on_publication(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $editing = Publication::factory()->create();
        $editing->editors()->save($user);

        $adminning = Publication::factory()->create();
        $adminning->publicationAdmins()->save($user);

        Publication::factory()->create();

        $results = Publication::query()
            ->myRole([ScopedRole::Editor->value])
            ->get();

        $this->assertEquals([$editing->id], $results->pluck('id')->all());
    }

    /**
     * myRole returns no publications for a guest, who holds no roles, rather
     * than dereferencing a null user.
     *
     * @return void
     */
    public function test_my_role_returns_nothing_for_guests(): void
    {
        Publication::factory()->count(3)->create();

        $results = Publication::query()
            ->myRole([ScopedRole::Editor->value])
            ->get();

        $this->assertCount(0, $results);
    }

    /**
     * myRole accepts multiple roles and returns the union of matching
     * publications.
     *
     * @return void
     */
    public function test_my_role_accepts_multiple_roles(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $editing = Publication::factory()->create();
        $editing->editors()->save($user);

        $adminning = Publication::factory()->create();
        $adminning->publicationAdmins()->save($user);

        Publication::factory()->create();

        $results = Publication::query()
            ->myRole([
                ScopedRole::Editor->value,
                ScopedRole::PublicationAdmin->value,
            ])
            ->get();

        $this->assertEqualsCanonicalizing(
            [$editing->id, $adminning->id],
            $results->pluck('id')->all()
        );
    }
}
