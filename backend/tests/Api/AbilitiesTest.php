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
                        access_admin
                    }
                }
            }'
        );

        $response->assertJsonPath('data.currentUser.abilities', [
            'publication_create' => true,
            'user_view_any' => true,
            'user_update' => true,
            'user_manage_beta' => true,
            'access_admin' => true,
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
                        access_admin
                    }
                }
            }'
        );

        $response->assertJsonPath('data.currentUser.abilities', [
            'publication_create' => false,
            'user_view_any' => false,
            'access_admin' => false,
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

    /**
     * Export is granted to the review coordinator (and up the superset chain to
     * editor / publication admin) but withheld from the reviewer — mirroring the
     * role logic the client export gate used to hard-code. It is an unconditional
     * grant, so it holds regardless of submission status.
     *
     * @return void
     */
    public function testSubmissionExportGrantedToReviewCoordinatorNotReviewer(): void
    {
        $publication = Publication::factory()->create();

        $coordinator = User::factory()->create();
        $coordinatorSubmission = Submission::factory()
            ->for($publication)
            ->hasAttached($coordinator, [], 'reviewCoordinators')
            ->create(['status' => Submission::UNDER_REVIEW]);

        $this->actingAs($coordinator);
        $this->graphQL(
            'query getSubmission($id: ID!) {
                submission(id: $id) { abilities { export } }
            }',
            ['id' => $coordinatorSubmission->id]
        )->assertJsonPath('data.submission.abilities.export', true);

        $reviewer = User::factory()->create();
        $reviewerSubmission = Submission::factory()
            ->for($publication)
            ->hasAttached($reviewer, [], 'reviewers')
            ->create(['status' => Submission::UNDER_REVIEW]);

        $this->actingAs($reviewer);
        $this->graphQL(
            'query getSubmission($id: ID!) {
                submission(id: $id) { abilities { export } }
            }',
            ['id' => $reviewerSubmission->id]
        )->assertJsonPath('data.submission.abilities.export', false);
    }

    /**
     * The submitter holds export unconditionally — unlike its draft-only status
     * grant, export does not flip when the submission leaves draft.
     *
     * @return void
     */
    public function testSubmissionExportGrantedToSubmitterRegardlessOfStatus(): void
    {
        $submitter = User::factory()->create();
        $this->actingAs($submitter);
        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($submitter, [], 'submitters')
            ->create(['status' => Submission::ARCHIVED]);

        $this->graphQL(
            'query getSubmission($id: ID!) {
                submission(id: $id) { abilities { export } }
            }',
            ['id' => $submission->id]
        )->assertJsonPath('data.submission.abilities.export', true);
    }
}
