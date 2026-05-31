<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\ApiTestCase;

class BetaFeatureTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Pin a known beta catalog so the suite doesn't track edits to
        // the real config/features.php as features graduate to GA.
        Config::set('features.beta', ['labs_test']);
    }

    public function testBetaAndOptInsAreExposedOnUser(): void
    {
        $this->beAppAdmin();
        $target = User::factory()->beta()->create([
            'feature_opt_ins' => ['labs_test'],
        ]);

        $response = $this->graphQL(
            'query getUser ($id: ID!) {
                user (id: $id) { id beta feature_opt_ins }
            }',
            ['id' => $target->id]
        );

        $this->assertTrue($response->json('data.user.beta'));
        $this->assertSame(
            ['labs_test'],
            $response->json('data.user.feature_opt_ins')
        );
    }

    public function testBetaUserCanOptIntoGatedFeature(): void
    {
        $user = User::factory()->beta()->create();
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation ($feature: String!, $enabled: Boolean!) {
                setFeatureOptIn (feature: $feature, enabled: $enabled) { id feature_opt_ins }
            }',
            ['feature' => 'labs_test', 'enabled' => true]
        );

        $this->assertEmpty($response->json('errors'));
        $this->assertSame(
            ['labs_test'],
            $response->json('data.setFeatureOptIn.feature_opt_ins')
        );
        $this->assertTrue($user->fresh()->hasFeatureEnabled('labs_test'));
    }

    public function testEnablementIsDecoupledFromBetaFlag(): void
    {
        // Enablement is decided solely by the opt-in record, not the
        // `beta` flag. This is what lets a future grant path (e.g. a
        // user entering a beta key) enable a feature for a non-beta
        // user without advertising it. Here the opt-in is stored but
        // the user has no `beta` flag — the feature is still enabled.
        $user = User::factory()->create([
            'beta' => false,
            'feature_opt_ins' => ['labs_test'],
        ]);

        $this->assertTrue($user->hasFeatureEnabled('labs_test'));
    }

    public function testOptingInPreservesExistingOptIns(): void
    {
        // Opting into a second feature must not clobber an existing
        // opt-in — the write is additive.
        Config::set('features.beta', ['labs_test', 'second_feature']);
        $user = User::factory()->beta()->create([
            'feature_opt_ins' => ['labs_test'],
        ]);
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation ($feature: String!, $enabled: Boolean!) {
                setFeatureOptIn (feature: $feature, enabled: $enabled) { id feature_opt_ins }
            }',
            ['feature' => 'second_feature', 'enabled' => true]
        );

        $this->assertEmpty($response->json('errors'));
        $this->assertEqualsCanonicalizing(
            ['labs_test', 'second_feature'],
            $response->json('data.setFeatureOptIn.feature_opt_ins')
        );
    }

    public function testOptingInIsIdempotent(): void
    {
        // Opting into a feature already enabled must not duplicate the key.
        $user = User::factory()->beta()->create([
            'feature_opt_ins' => ['labs_test'],
        ]);
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation ($feature: String!, $enabled: Boolean!) {
                setFeatureOptIn (feature: $feature, enabled: $enabled) { id feature_opt_ins }
            }',
            ['feature' => 'labs_test', 'enabled' => true]
        );

        $this->assertEmpty($response->json('errors'));
        $this->assertSame(
            ['labs_test'],
            $response->json('data.setFeatureOptIn.feature_opt_ins')
        );
    }

    public function testOptingOutOfFeatureNotEnabledIsNoOp(): void
    {
        // Opting out a key that was never enabled leaves the rest intact.
        Config::set('features.beta', ['labs_test', 'second_feature']);
        $user = User::factory()->beta()->create([
            'feature_opt_ins' => ['labs_test'],
        ]);
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation ($feature: String!, $enabled: Boolean!) {
                setFeatureOptIn (feature: $feature, enabled: $enabled) { id feature_opt_ins }
            }',
            ['feature' => 'second_feature', 'enabled' => false]
        );

        $this->assertEmpty($response->json('errors'));
        $this->assertSame(
            ['labs_test'],
            $response->json('data.setFeatureOptIn.feature_opt_ins')
        );
    }

    public function testGuestCannotSetFeatureOptIn(): void
    {
        // The mutation is @guard-ed; an unauthenticated caller is rejected.
        $response = $this->graphQL(
            'mutation ($feature: String!, $enabled: Boolean!) {
                setFeatureOptIn (feature: $feature, enabled: $enabled) { id feature_opt_ins }
            }',
            ['feature' => 'labs_test', 'enabled' => true]
        );

        $this->assertNotEmpty($response->json('errors'));
    }

    public function testNonBetaUserCannotOptIntoGatedFeature(): void
    {
        $user = User::factory()->create(['beta' => false]);
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation ($feature: String!, $enabled: Boolean!) {
                setFeatureOptIn (feature: $feature, enabled: $enabled) { id feature_opt_ins }
            }',
            ['feature' => 'labs_test', 'enabled' => true]
        );

        $this->assertNotEmpty($response->json('errors'));
        $this->assertNull($user->fresh()->feature_opt_ins);
    }

    public function testOptingIntoUnknownFeatureIsRejected(): void
    {
        $user = User::factory()->beta()->create();
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation ($feature: String!, $enabled: Boolean!) {
                setFeatureOptIn (feature: $feature, enabled: $enabled) { id feature_opt_ins }
            }',
            ['feature' => 'not_a_real_feature', 'enabled' => true]
        );

        $this->assertNotEmpty($response->json('errors'));
    }

    public function testOptingOutIsAllowedRegardlessOfBetaAccess(): void
    {
        // A user who once had a stored opt-in but no longer has beta
        // access can still clear the opt-in.
        $user = User::factory()->create([
            'beta' => false,
            'feature_opt_ins' => ['labs_test'],
        ]);
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation ($feature: String!, $enabled: Boolean!) {
                setFeatureOptIn (feature: $feature, enabled: $enabled) { id feature_opt_ins }
            }',
            ['feature' => 'labs_test', 'enabled' => false]
        );

        $this->assertEmpty($response->json('errors'));
        $this->assertSame([], $response->json('data.setFeatureOptIn.feature_opt_ins'));
    }

    public function testAppAdminCanGrantBetaAccess(): void
    {
        $this->beAppAdmin();
        $target = User::factory()->create(['beta' => false]);

        $response = $this->graphQL(
            'mutation ($id: ID!, $enabled: Boolean!) {
                setUserBetaAccess (id: $id, enabled: $enabled) { id beta }
            }',
            ['id' => $target->id, 'enabled' => true]
        );

        $this->assertEmpty($response->json('errors'));
        $this->assertTrue($response->json('data.setUserBetaAccess.beta'));
        $this->assertTrue($target->fresh()->beta);
    }

    public function testAppAdminCanRevokeBetaAccess(): void
    {
        $this->beAppAdmin();
        $target = User::factory()->beta()->create();

        $response = $this->graphQL(
            'mutation ($id: ID!, $enabled: Boolean!) {
                setUserBetaAccess (id: $id, enabled: $enabled) { id beta }
            }',
            ['id' => $target->id, 'enabled' => false]
        );

        $this->assertEmpty($response->json('errors'));
        $this->assertFalse($response->json('data.setUserBetaAccess.beta'));
        $this->assertFalse($target->fresh()->beta);
    }

    public function testRevokingBetaAccessLeavesOptInsIntact(): void
    {
        // Revoking beta access only flips the advertise/visibility flag;
        // it must not touch stored opt-ins. Enablement stays decoupled,
        // so the feature remains enabled until the opt-in is cleared.
        $this->beAppAdmin();
        $target = User::factory()->beta()->create([
            'feature_opt_ins' => ['labs_test'],
        ]);

        $this->graphQL(
            'mutation ($id: ID!, $enabled: Boolean!) {
                setUserBetaAccess (id: $id, enabled: $enabled) { id beta }
            }',
            ['id' => $target->id, 'enabled' => false]
        );

        $fresh = $target->fresh();
        $this->assertFalse($fresh->beta);
        $this->assertSame(['labs_test'], $fresh->getActiveFeatureOptIns());
        $this->assertTrue($fresh->hasFeatureEnabled('labs_test'));
    }

    public function testNonAdminCannotGrantBetaAccess(): void
    {
        $actor = User::factory()->create();
        $this->actingAs($actor);

        $target = User::factory()->create(['beta' => false]);

        $response = $this->graphQL(
            'mutation ($id: ID!, $enabled: Boolean!) {
                setUserBetaAccess (id: $id, enabled: $enabled) { id beta }
            }',
            ['id' => $target->id, 'enabled' => true]
        );

        $this->assertNotEmpty($response->json('errors'));
        $this->assertFalse($target->fresh()->beta);
    }

    public function testUserCannotSelfGrantBetaViaUpdateUser(): void
    {
        // `beta` is intentionally absent from $fillable and UpdateUserInput,
        // so even a well-formed updateUser cannot flip it.
        $user = User::factory()->create(['beta' => false]);
        $this->actingAs($user);

        $this->graphQL(
            'mutation ($id: ID!) {
                updateUser (user: { id: $id, name: "Renamed" }) { id name }
            }',
            ['id' => $user->id]
        );

        $this->assertFalse($user->fresh()->beta);
    }

    public function testBetaFilterListsOnlyBetaUsers(): void
    {
        $this->beAppAdmin();
        User::factory()->beta()->count(2)->create();
        User::factory()->count(3)->create(['beta' => false]);

        $response = $this->graphQL(
            'query {
                users (beta: true, first: 50) { data { id beta } }
            }'
        );

        $users = $response->json('data.users.data');
        $this->assertNotEmpty($users);
        foreach ($users as $u) {
            $this->assertTrue($u['beta']);
        }
    }

    public function testBetaFilterFalseListsOnlyNonBetaUsers(): void
    {
        $this->beAppAdmin();
        User::factory()->beta()->count(2)->create();
        User::factory()->count(3)->create(['beta' => false]);

        $response = $this->graphQL(
            'query {
                users (beta: false, first: 50) { data { id beta } }
            }'
        );

        $users = $response->json('data.users.data');
        $this->assertNotEmpty($users);
        foreach ($users as $u) {
            $this->assertFalse($u['beta']);
        }
    }

    public function testCanAccessFeatureGatesOnBetaForCatalogKeys(): void
    {
        // Unit-level guard on the WRITE-path helper: a gated (catalog)
        // key requires beta access; an ungated key is open to anyone.
        Config::set('features.beta', ['labs_test']);

        $this->assertTrue(User::featureIsBetaGated('labs_test'));
        $this->assertFalse(User::featureIsBetaGated('not_in_catalog'));

        $betaUser = User::factory()->beta()->create();
        $plainUser = User::factory()->create(['beta' => false]);

        $this->assertTrue($betaUser->canAccessFeature('labs_test'));
        $this->assertFalse($plainUser->canAccessFeature('labs_test'));

        // Ungated keys are accessible regardless of the beta flag.
        $this->assertTrue($betaUser->canAccessFeature('not_in_catalog'));
        $this->assertTrue($plainUser->canAccessFeature('not_in_catalog'));
    }
}
