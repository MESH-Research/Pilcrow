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
     * Test that submissions can be filtered by publication.
     *
     * @return void
     */
    public function test_submissions_filters_by_publication(): void
    {
        $this->beAppAdmin();
        $user = User::factory()->create();
        $publicationA = Publication::factory()->create();
        $publicationB = Publication::factory()->create();

        $expected = Submission::factory()
            ->for($publicationA)
            ->hasAttached($user, [], 'submitters')
            ->create(['title' => 'In Publication A']);

        Submission::factory()
            ->for($publicationB)
            ->hasAttached($user, [], 'submitters')
            ->create(['title' => 'In Publication B']);

        $response = $this->graphQL(
            'query ($id: ID!, $first: Int!, $publication: [ID!]) {
                user(id: $id) {
                    submissions(first: $first, publication: $publication) {
                        data {
                            submission {
                                id
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
                'publication' => [(string)$publicationA->id],
            ]
        );

        $response->assertJsonPath('data.user.submissions.paginatorInfo.total', 1);
        $response->assertJsonPath(
            'data.user.submissions.data.0.submission.id',
            (string)$expected->id
        );
    }

    /**
     * Test that submissions can be searched by submission title.
     *
     * @return void
     */
    public function test_submissions_search_by_title(): void
    {
        $this->beAppAdmin();
        $user = User::factory()->create();
        $publication = Publication::factory()->create();

        Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'submitters')
            ->create(['title' => 'Quantum entanglement study']);

        Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'submitters')
            ->create(['title' => 'Unrelated essay']);

        $response = $this->graphQL(
            'query ($id: ID!, $first: Int!, $search: String) {
                user(id: $id) {
                    submissions(first: $first, search: $search) {
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
                'search' => 'Quantum',
            ]
        );

        $response->assertJsonPath('data.user.submissions.paginatorInfo.total', 1);
        $response->assertJsonPath(
            'data.user.submissions.data.0.submission.title',
            'Quantum entanglement study'
        );
    }

    /**
     * Test that submissions can be ordered by a related submission column.
     *
     * @return void
     */
    public function test_submissions_order_by_submission_title(): void
    {
        $this->beAppAdmin();
        $user = User::factory()->create();
        $publication = Publication::factory()->create();

        Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'submitters')
            ->create(['title' => 'Banana']);

        Submission::factory()
            ->for($publication)
            ->hasAttached($user, [], 'submitters')
            ->create(['title' => 'Apple']);

        $response = $this->graphQL(
            'query ($id: ID!, $first: Int!, $orderBy: [SubmissionAssignmentOrderBy!]) {
                user(id: $id) {
                    submissions(first: $first, orderBy: $orderBy) {
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
                'orderBy' => [['column' => 'TITLE', 'order' => 'ASC']],
            ]
        );

        $response->assertJsonPath('data.user.submissions.paginatorInfo.total', 2);
        $response->assertJsonPath(
            'data.user.submissions.data.0.submission.title',
            'Apple'
        );
        $response->assertJsonPath(
            'data.user.submissions.data.1.submission.title',
            'Banana'
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
