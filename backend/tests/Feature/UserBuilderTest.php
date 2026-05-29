<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Direct coverage for UserBuilder::search, which backs the admin users table
 * search field. The match spans name, username, and email and is grouped so it
 * composes with other constraints without leaking its ORs.
 */
class UserBuilderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * search matches against the name column.
     *
     * @return void
     */
    public function test_search_matches_name(): void
    {
        $match = User::factory()->create(['name' => 'Ada Lovelace']);
        User::factory()->create(['name' => 'Grace Hopper']);

        $results = User::query()->search('lovelace')->get();

        $this->assertEquals([$match->id], $results->pluck('id')->all());
    }

    /**
     * search matches against the username column.
     *
     * @return void
     */
    public function test_search_matches_username(): void
    {
        $match = User::factory()->create(['username' => 'catamaran']);
        User::factory()->create(['username' => 'dinghy']);

        $results = User::query()->search('catamaran')->get();

        $this->assertEquals([$match->id], $results->pluck('id')->all());
    }

    /**
     * search matches against the email column.
     *
     * @return void
     */
    public function test_search_matches_email(): void
    {
        $match = User::factory()->create(['email' => 'pilot@airfield.test']);
        User::factory()->create(['email' => 'sailor@harbour.test']);

        $results = User::query()->search('airfield')->get();

        $this->assertEquals([$match->id], $results->pluck('id')->all());
    }

    /**
     * search groups its column ORs so an outer constraint is not widened by
     * the search clause. A non-matching outer where combined with search
     * returns nothing rather than leaking matches via the OR chain.
     *
     * @return void
     */
    public function test_search_clause_is_grouped(): void
    {
        $target = User::factory()->create(['name' => 'Searchable Name']);
        User::factory()->create(['name' => 'Searchable Name']);

        $results = User::query()
            ->where('id', $target->id)
            ->search('Searchable')
            ->get();

        $this->assertEquals([$target->id], $results->pluck('id')->all());
    }

    /**
     * search is a no-op when given a null or empty term, returning the full
     * set rather than filtering everything out.
     *
     * @return void
     */
    public function test_search_returns_all_when_term_is_empty(): void
    {
        User::factory()->count(3)->create();

        $this->assertCount(3, User::query()->search(null)->get());
        $this->assertCount(3, User::query()->search('')->get());
    }
}
