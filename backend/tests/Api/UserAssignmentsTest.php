<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class UserAssignmentsTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * Test that submissions returns assignments for a user.
     *
     * @return void
     */
    public function test_submissions_returns_user_assignments(): void
    {
        $this->beAppAdmin();
        $user = User::factory()->create();
        $publication = Publication::factory()->create();
        $submission = Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'submitters')
            ->create();

        $response = $this->graphQL(
            'query ($id: ID!, $first: Int!, $page: Int) {
                user(id: $id) {
                    submissions(first: $first, page: $page) {
                        data {
                            role
                            submission {
                                id
                                title
                            }
                        }
                        paginatorInfo {
                            total
                            currentPage
                        }
                    }
                }
            }',
            [
                'id' => $user->id,
                'first' => 10,
                'page' => 1,
            ]
        );

        $response->assertJsonPath('data.user.submissions.paginatorInfo.total', 1);
        $response->assertJsonPath(
            'data.user.submissions.data.0.role',
            'submitter'
        );
        $response->assertJsonPath(
            'data.user.submissions.data.0.submission.id',
            (string)$submission->id
        );
    }

    /**
     * Test that submissions can be filtered by role.
     *
     * @return void
     */
    public function test_submissions_filters_by_role(): void
    {
        $this->beAppAdmin();
        $user = User::factory()->create();
        $publication = Publication::factory()->create();

        Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'submitters')
            ->create(['title' => 'Submitted']);

        Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'reviewers')
            ->create(['title' => 'Reviewing']);

        $response = $this->graphQL(
            'query ($id: ID!, $first: Int!, $roles: [SubmissionUserRoles!]) {
                user(id: $id) {
                    submissions(first: $first, roles: $roles) {
                        data {
                            role
                        }
                        paginatorInfo {
                            total
                        }
                    }
                }
            }',
            [
                'id' => $user->id,
                'first' => 10,
                'roles' => ['reviewer'],
            ]
        );

        $response->assertJsonPath('data.user.submissions.paginatorInfo.total', 1);
        $response->assertJsonPath(
            'data.user.submissions.data.0.role',
            'reviewer'
        );
    }

    /**
     * Test that submissions can be filtered by status.
     *
     * @return void
     */
    public function test_submissions_filters_by_status(): void
    {
        $this->beAppAdmin();
        $user = User::factory()->create();
        $publication = Publication::factory()->create();

        Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'submitters')
            ->create(['title' => 'Draft', 'status' => 0]);

        Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'submitters')
            ->create(['title' => 'Submitted', 'status' => 1]);

        $response = $this->graphQL(
            'query ($id: ID!, $first: Int!, $status: [SubmissionStatus!]) {
                user(id: $id) {
                    submissions(first: $first, status: $status) {
                        data {
                            submission {
                                title
                            }
                        }
                        paginatorInfo {
                            total
                        }
                    }
                }
            }',
            [
                'id' => $user->id,
                'first' => 10,
                'status' => ['INITIALLY_SUBMITTED'],
            ]
        );

        $response->assertJsonPath('data.user.submissions.paginatorInfo.total', 1);
        $response->assertJsonPath(
            'data.user.submissions.data.0.submission.title',
            'Submitted'
        );
    }

    /**
     * Test that publications returns assignments for a user.
     *
     * @return void
     */
    public function test_publications_returns_user_assignments(): void
    {
        $this->beAppAdmin();
        $user = User::factory()->create();
        $publication = Publication::factory()
            ->hasAttached($user, [], 'editors')
            ->create();

        $response = $this->graphQL(
            'query ($id: ID!, $first: Int!) {
                user(id: $id) {
                    publications(first: $first) {
                        data {
                            role
                            publication {
                                id
                                name
                            }
                        }
                        paginatorInfo {
                            total
                        }
                    }
                }
            }',
            [
                'id' => $user->id,
                'first' => 10,
            ]
        );

        $response->assertJsonPath('data.user.publications.paginatorInfo.total', 1);
        $response->assertJsonPath(
            'data.user.publications.data.0.role',
            'editor'
        );
        $response->assertJsonPath(
            'data.user.publications.data.0.publication.id',
            (string)$publication->id
        );
    }

    /**
     * Test that publications can be filtered by role.
     *
     * @return void
     */
    public function test_publications_filters_by_role(): void
    {
        $this->beAppAdmin();
        $user = User::factory()->create();

        Publication::factory()
            ->hasAttached($user, [], 'editors')
            ->create(['name' => 'Editor Pub']);

        Publication::factory()
            ->hasAttached($user, [], 'publicationAdmins')
            ->create(['name' => 'Admin Pub']);

        $response = $this->graphQL(
            'query ($id: ID!, $first: Int!, $roles: [PublicationRole!]) {
                user(id: $id) {
                    publications(first: $first, roles: $roles) {
                        data {
                            role
                            publication {
                                name
                            }
                        }
                        paginatorInfo {
                            total
                        }
                    }
                }
            }',
            [
                'id' => $user->id,
                'first' => 10,
                'roles' => ['publication_admin'],
            ]
        );

        $response->assertJsonPath('data.user.publications.paginatorInfo.total', 1);
        $response->assertJsonPath(
            'data.user.publications.data.0.role',
            'publication_admin'
        );
    }
}
