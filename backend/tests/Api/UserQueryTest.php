<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\ApiTestCase;

class UserQueryTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * @return array
     */
    public static function profileMetadataProvider(): array
    {
        return [
            [
                [
                    'academic_profiles' => [
                        'orcid_id' => 'https://orcid.org/members/regular_user',
                        'humanities_commons' => 'https://hcommons.org/members/regularuser',
                    ],
                    'social_media' => [
                        'google' => 'regularuser',
                        'twitter' => 'regularuser',
                        'facebook' => 'regularuser',
                        'instagram' => 'regularuser',
                        'linkedin' => 'regularuser',
                    ],
                    'position_title' => 'Regular User',
                    'specialization' => 'Regular',
                    'affiliation' => 'Regular Users',
                    'biography' => 'I am a regular user.',
                    'websites' => [
                        'https://github.com',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    #[DataProvider('profileMetadataProvider')]
    public function testThatUserDetailsCanBeQueried(array $profile_metadata): void
    {
        $this->beAppAdmin();
        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'regularuser@meshresearch.net',
            'username' => 'regularuser',
            'profile_metadata' => $profile_metadata,
        ]);
        $response = $this->graphQL(
            'query getUser ($id: ID!) {
                user (id: $id) {
                    name
                    email
                    username
                    profile_metadata {
                        academic_profiles {
                            orcid_id
                            humanities_commons
                        }
                        social_media {
                            google
                            twitter
                            facebook
                            instagram
                            linkedin
                        }
                        position_title
                        specialization
                        affiliation
                        biography
                        websites
                    }
                }
            }',
            ['id' => $user->id]
        );
        $response->assertJson([
            'data' => [
                'user' => [
                    'name' => 'Regular User',
                    'email' => 'regularuser@meshresearch.net',
                    'username' => 'regularuser',
                    'profile_metadata' => $profile_metadata,
                ],
            ],
        ]);
    }

    public function testGuestCannotCallUserQuery(): void
    {
        $target = User::factory()->create(['email' => 'leak@meshresearch.net']);

        $response = $this->graphQL(
            'query getUser ($id: ID!) {
                user (id: $id) { id username email }
            }',
            ['id' => $target->id]
        );

        $this->assertNull($response->json('data.user'));
        $this->assertNotEmpty($response->json('errors'));
    }

    public function testNonAdminCannotCallUserQuery(): void
    {
        $viewer = User::factory()->create();
        $this->actingAs($viewer);

        $target = User::factory()->create(['email' => 'private@meshresearch.net']);

        $response = $this->graphQL(
            'query getUser ($id: ID!) {
                user (id: $id) { id username email }
            }',
            ['id' => $target->id]
        );

        $this->assertNull($response->json('data.user'));
        $this->assertNotEmpty($response->json('errors'));
    }

    public function testNonAdminCannotCallUserQueryEvenForSelf(): void
    {
        $self = User::factory()->create(['email' => 'self@meshresearch.net']);
        $this->actingAs($self);

        $response = $this->graphQL(
            'query getUser ($id: ID!) {
                user (id: $id) { id username email }
            }',
            ['id' => $self->id]
        );

        // user(id) is admin-only. Self should use currentUser instead.
        $this->assertNull($response->json('data.user'));
        $this->assertNotEmpty($response->json('errors'));
    }

    public function testSubmitterCannotReadReviewerEmailsThroughSubmission(): void
    {
        $publication = Publication::factory()->create();
        $submitter = User::factory()->create(['email' => 'submitter@meshresearch.net']);
        $reviewer = User::factory()->create(['email' => 'reviewer@meshresearch.net']);
        $coordinator = User::factory()->create(['email' => 'coordinator@meshresearch.net']);
        $editor = User::factory()->create(['email' => 'editor@meshresearch.net']);

        $publication->editors()->save($editor);

        $submission = Submission::factory()
            ->for($publication)
            ->hasAttached($submitter, [], 'submitters')
            ->hasAttached($reviewer, [], 'reviewers')
            ->hasAttached($coordinator, [], 'reviewCoordinators')
            ->create();

        $this->actingAs($submitter);

        $response = $this->graphQL(
            'query peekEmails ($id: ID!) {
                submission (id: $id) {
                    reviewers { id email }
                    review_coordinators { id email }
                    submitters { id email }
                }
            }',
            ['id' => $submission->id]
        );

        $reviewers = collect($response->json('data.submission.reviewers'));
        $coordinators = collect($response->json('data.submission.review_coordinators'));
        $submitters = collect($response->json('data.submission.submitters'));

        // Reviewer / coordinator emails redacted to the submitter
        $this->assertNull($reviewers->firstWhere('id', (string)$reviewer->id)['email']);
        $this->assertNull($coordinators->firstWhere('id', (string)$coordinator->id)['email']);

        // Submitter still sees their own email
        $this->assertSame(
            'submitter@meshresearch.net',
            $submitters->firstWhere('id', (string)$submitter->id)['email']
        );
    }

    public function testEmailIsVisibleToSelfViaCurrentUser(): void
    {
        $user = User::factory()->create(['email' => 'self@meshresearch.net']);
        $this->actingAs($user);

        $response = $this->graphQL(
            'query { currentUser { email } }'
        );

        $response->assertJson([
            'data' => [
                'currentUser' => [
                    'email' => 'self@meshresearch.net',
                ],
            ],
        ]);
    }
}
