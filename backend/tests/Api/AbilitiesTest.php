<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\ApiTestCase;

/**
 * The client-facing `abilities` fields surface the same decisions the policies
 * enforce, so the UI can gate navigation/controls without re-implementing the
 * role matrix. They are resolved through Bouncer (global) and
 * {@see \App\Auth\ScopedAbilityResolver} (scoped), the same engines the policies
 * use, so they cannot drift from real authorization.
 */
class AbilitiesTest extends ApiTestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * An application administrator holds every global ability via Bouncer's
     * everything() wildcard, exposed on currentUser.abilities.
     *
     * @return void
     */
    public function testGlobalAbilitiesGrantedToApplicationAdministrator(): void
    {
        $this->beAppAdmin();

        $response = $this->graphQL(
            'query {
                currentUser {
                    abilities {
                        publication_create
                        user_view_any
                        user_update
                        user_manage_beta
                    }
                }
            }'
        );

        $response->assertJsonPath('data.currentUser.abilities', [
            'publication_create' => true,
            'user_view_any' => true,
            'user_update' => true,
            'user_manage_beta' => true,
        ]);
    }

    /**
     * A plain user holds no global abilities (the matrix grants them only to the
     * application administrator role at present).
     *
     * @return void
     */
    public function testGlobalAbilitiesDeniedToPlainUser(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL(
            'query {
                currentUser {
                    abilities {
                        publication_create
                        user_view_any
                    }
                }
            }'
        );

        $response->assertJsonPath('data.currentUser.abilities', [
            'publication_create' => false,
            'user_view_any' => false,
        ]);
    }

    /**
     * A publication administrator can view and update their publication; the
     * flags reflect the scoped resolver verdict for that entity.
     *
     * @return void
     */
    public function testPublicationAbilitiesForPublicationAdmin(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $publication = Publication::factory()
            ->hasAttached($admin, [], 'publicationAdmins')
            ->create();

        $response = $this->graphQL(
            'query getPublication($id: ID) {
                publication(id: $id) {
                    abilities {
                        view
                        update
                    }
                }
            }',
            ['id' => $publication->id]
        );

        $response->assertJsonPath('data.publication.abilities', [
            'view' => true,
            'update' => true,
        ]);
    }

    /**
     * A submitter can update their own draft submission and change its status,
     * but cannot manage reviewers — the scoped flags mirror the matrix.
     *
     * @return void
     */
    public function testSubmissionAbilitiesForSubmitterOnDraft(): void
    {
        $submitter = User::factory()->create();
        $this->actingAs($submitter);
        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($submitter, [], 'submitters')
            ->create(['status' => Submission::DRAFT]);

        $response = $this->graphQL(
            'query getSubmission($id: ID!) {
                submission(id: $id) {
                    abilities {
                        view
                        update
                        update_status
                        update_reviewers
                    }
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.submission.abilities.view', true);
        $response->assertJsonPath('data.submission.abilities.update', true);
        $response->assertJsonPath('data.submission.abilities.update_status', true);
        $response->assertJsonPath('data.submission.abilities.update_reviewers', false);
    }

    /**
     * The submitter's draft-only status ability is a CONDITIONAL grant: once the
     * submission leaves draft, update_status flips to false. This is the key win
     * over a role-based flag — the resolver evaluates the predicate against the
     * entity, and the client-facing flag tracks it.
     *
     * @return void
     */
    public function testSubmissionUpdateStatusFlipsWhenLeavingDraft(): void
    {
        $submitter = User::factory()->create();
        $this->actingAs($submitter);
        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($submitter, [], 'submitters')
            ->create(['status' => Submission::INITIALLY_SUBMITTED]);

        $response = $this->graphQL(
            'query getSubmission($id: ID!) {
                submission(id: $id) {
                    abilities {
                        update_status
                    }
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.submission.abilities.update_status', false);
    }
}
