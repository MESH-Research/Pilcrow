<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Auth\Roles\ScopedRole;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\ApiTestCase;

class SubmissionTest extends ApiTestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testSubmissionsHaveAManyToManyRelationshipWithUsers()
    {
        $this->beAppAdmin();

        $publication = Publication::factory()->create([
            'name' => 'Test Publication #2',
        ]);

        [$submitter, $reviewer, $reviewCoordinator] = User::factory()->count(3)->create();

        $submission = Submission::factory()
            ->hasAttached($submitter, [], 'submitters')
            ->hasAttached($reviewer, [], 'reviewers')
            ->hasAttached($reviewCoordinator, [], 'reviewCoordinators')
            ->for($publication)
            ->create();

        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                submission(id: $id) {
                    id
                    title
                    created_by {
                        id
                    }
                    updated_by {
                        id
                    }
                    reviewers {
                        id
                    }
                    review_coordinators {
                        id
                    }
                    submitters {
                        id
                    }
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJson(fn(AssertableJson $json) => $json
            ->has('data', fn($json) => $json->has('submission', fn($json) => $json->has('reviewers', 1, fn($json) => $json->where('id', (string)$reviewer->id))
                ->has('submitters', 1, fn($json) => $json->where('id', (string)$submitter->id))
                ->has('review_coordinators', 1, fn($json) => $json->where('id', (string)$reviewCoordinator->id))
                ->etc())));
    }

    /**
     * @return void
     */
    #[DataProvider('submissionRolesProvider')]
    public function testAllSubmissionRolesCanViewSubmission($role)
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $submission = Submission::factory()
            ->hasAttached($user, [], Str::camel($role))
            ->create([
                'title' => 'Test Submission',
            ]);
        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                submission (id: $id) {
                    id
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.submission.id', (string)$submission->id);
    }

    public function testPublicationAdminCanViewSubmission()
    {
        /** @var User $publicationAdmin */
        $publicationAdmin = User::factory()->create();
        $this->actingAs($publicationAdmin);

        $publication = Publication::factory()
            ->hasAttached($publicationAdmin, ['role' => ScopedRole::PublicationAdmin->toSlug()])
            ->create();

        $submission = Submission::factory()
            ->for($publication)
            ->create([
                'title' => 'Test Submission',
            ]);

        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                submission (id: $id) {
                    id
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.submission.id', (string)$submission->id);
    }

    public function testOtherUsersCannotViewSubmission()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $submission = Submission::factory()
            ->create([
                'title' => 'Test Submission',
            ]);
        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                 submission (id: $id) {
                     id
                 }
             }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('errors.0.message', 'UNAUTHORIZED');
    }

    public function testGuestsCannotViewSubmission()
    {
        $submission = Submission::factory()
            ->create([
                'title' => 'Test Submission',
            ]);
        $response = $this->graphQL(
            'query GetSubmission($id: ID!) {
                 submission (id: $id) {
                     id
                 }
             }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('errors.0.message', 'This action is unauthorized.');
    }

    /**
     * @return void
     */
    public function testSubmissionsCanBeQueriedForAPublication()
    {
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #3',
        ]);

        $submission = Submission::factory()
            ->hasAttached(User::factory()->create(), [], 'submitters')
            ->for($publication)
            ->create([
                'title' => 'Test Submission',
            ]);

        $response = $this->graphQL(
            'query GetSubmissionsByPublication($id: ID!) {
                publication (id: $id) {
                    id
                    name
                    submissions {
                        id
                        title
                    }
                }
            }',
            ['id' => $publication->id]
        );
        $expected_data = [
            'publication' => [
                'id' => (string)$publication->id,
                'name' => 'Test Publication #3',
                'submissions' => [
                    [
                        'id' => (string)$submission->id,
                        'title' => 'Test Submission',
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testSubmissionsCanBeQueriedForAUser()
    {
        $this->beAppAdmin();
        $publication = Publication::factory()->create([
            'name' => 'Test Publication #4',
        ]);
        $user = User::factory()->create([
            'name' => 'Test User #1 With Submission',
        ]);
        $submission = Submission::factory()
            ->hasAttached($user, [], 'submitters')
            ->for($publication)
            ->create([
                'title' => 'Test Submission #5 for Test User #1 With Submission',
            ]);

        $response = $this->graphQL(
            'query GetSubmissionsByUser($id: ID!) {
                user (id: $id) {
                    id
                    name
                    submissions {
                        data {
                            role
                            submission {
                                id
                                title
                            }
                        }
                    }
                }
            }',
            ['id' => $user->id]
        );
        $expected_data = [
            'user' => [
                'id' => (string)$user->id,
                'name' => 'Test User #1 With Submission',
                'submissions' => [
                    'data' => [
                        [
                            'role' => 'submitter',
                            'submission' => [
                                'id' => (string)$submission->id,
                                'title' => 'Test Submission #5 for Test User #1 With Submission',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * @return void
     */
    public function testFileUpload(): void
    {
        $publication = Publication::factory()->create(['is_accepting_submissions' => true]);
        $user = User::factory()->create();
        $submission = Submission::factory()
            ->hasAttached($user, [], 'submitters')
            ->for($publication)
            ->create();
        $this->actingAs($user);

        $operations = [
            'operationName' => 'UpdateSubmissionContentWithFile',
            'query' => '
                mutation UpdateSubmissionContentWithFile (
                    $submission_id: ID!
                    $file_upload: Upload!
                ) {
                    updateSubmissionContentWithFile(
                        input: {
                            submission_id: $submission_id,
                            file_upload: $file_upload
                        }
                    ) {
                        id
                        content {
                            data
                        }
                    }
                }
            ',
            'variables' => [
                'submission_id' => $submission->id,
                'file_upload' => null,
            ],
        ];
        $map = [
            '0' => ['variables.file_upload'],
        ];
        $file = [
            '0' => UploadedFile::fake()->create('test.txt', 'File contents'),
        ];

        $response = $this->multipartGraphQL($operations, $map, $file);
        $response->assertJsonPath('data.updateSubmissionContentWithFile.id', (string)$submission->id);
        $response->assertJsonPath('data.updateSubmissionContentWithFile.content.data', "<p>File contents</p>\n");
    }

    public function testEndnoteImport()
    {
        $stubPath = __DIR__ . '/../stubs/footnote_test.docx';
        $upload = new UploadedFile($stubPath, 'footnote_test.docx', null, null, true);

        $publication = Publication::factory()->create(['is_accepting_submissions' => true]);
        $user = User::factory()->create();
        $submission = Submission::factory()
            ->hasAttached($user, [], 'submitters')
            ->for($publication)
            ->create();
        $this->actingAs($user);

        $operations = [
            'operationName' => 'UpdateSubmissionContentWithFile',
            'query' => '
                mutation UpdateSubmissionContentWithFile (
                    $submission_id: ID!
                    $file_upload: Upload!
                ) {
                    updateSubmissionContentWithFile(
                        input: {
                            submission_id: $submission_id,
                            file_upload: $file_upload
                        }
                    ) {
                        id
                        content {
                            data
                        }
                    }
                }
            ',
            'variables' => [
                'submission_id' => $submission->id,
                'file_upload' => null,
            ],
        ];
        $map = [
            '0' => ['variables.file_upload'],
        ];
        $file = [
            '0' => $upload,
        ];
        $expected = <<<END
        <p>This is some text<a href="#fn1" id="fnref1" role="doc-noteref">1</a></p>
        <p>This is some more text<a href="#fn2" id="fnref2" role="doc-noteref">2</a></p>
        <section role="doc-endnotes">
        <hr>
        <ol>
        <li id="fn1"><p>This text belongs here<a href="#fnref1" role="doc-backlink">↩︎</a></p></li>
        <li id="fn2"><p>Another footnote<a href="#fnref2" role="doc-backlink">↩︎</a></p></li>
        </ol>
        </section>

        END;

        $response = $this->multipartGraphQL($operations, $map, $file);

        $response->assertJsonPath('data.updateSubmissionContentWithFile.content.data', $expected);
    }

    /**
     * @return void
     */
    public function testSubmissionRejection()
    {
        $publication = Publication::factory()->create(['is_accepting_submissions' => false]);
        /**
         * @var User $user
         */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation CreateSubmissionDraft (
                $title: String!
                $publication_id: ID!
                $user_id: ID!
            ) {
                createSubmissionDraft(
                    input: {
                        title: $title,
                        publication_id: $publication_id,
                        submitters: { connect: [$user_id] },
                    }
                ) {
                    title
                }
            }',
            [
                'title' => '    Test Submission    ',
                'publication_id' => $publication->id,
                'user_id' => $user->id,
            ],
        );

        $response
            ->assertJsonPath('errors.0.message', 'This action is unauthorized.');
    }

    /**
     * @return array
     */
    public static function submissionRolesProvider(): array
    {
        return [
            //Role
            'submitter' => ['submitters'],
            'reviewer' => ['reviewers'],
            'review_coordinator' => ['review_coordinators'],
        ];
    }

    /**
     * @param string $role
     * @return void
     */
    #[DataProvider('submissionRolesProvider')]
    public function testMyRoleFields(string $role): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $submission = Submission::factory()->create();
        $camelized = Str::camel($role);
        $submission->$camelized()->attach($user);
        $graphql = '
            query GetSubmission($id: ID!) {
                submission(id: $id) {
                    my_role
                    effective_role
                }
            }
        ';

        $response = $this->graphQL($graphql, ['id' => $submission->id]);
        $response
            ->assertJsonPath('data.submission.my_role', Str::singular($role))
            ->assertJsonPath('data.submission.effective_role', Str::singular($role));
    }

    public function testAdminGetsEffectiveRole()
    {
        $this->beAppAdmin();
        $submission = Submission::factory()->create();
        $gql = '
            query GetSubmission($id: ID!) {
                submission(id: $id) {
                    my_role
                    effective_role
                }
            }
        ';

        $this->graphQL($gql, ['id' => $submission->id])
            ->assertJsonPath('data.submission.my_role', null)
            ->assertJsonPath('data.submission.effective_role', 'review_coordinator');
    }

    public function testPublicationRoleGetsEffectiveRole()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $submission = Submission::factory()->create();
        $submission->publication->publicationAdmins()->attach($user);
        $gql = '
            query GetSubmission($id: ID!) {
                submission(id: $id) {
                    my_role
                    effective_role
                }
            }
        ';

        $this->graphQL($gql, ['id' => $submission->id])
            ->assertJsonPath('data.submission.my_role', null)
            ->assertJsonPath('data.submission.effective_role', 'review_coordinator');
    }

    public function testCreatedByFieldIsSet()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $publication = Publication::factory()->create(['is_accepting_submissions' => true]);

        $response = $this->graphQL(
            'mutation CreateSubmissionDraft (
                    $title: String!
                    $publication_id: ID!
                    $user_id: ID!
                ) {
                    createSubmissionDraft(
                        input: {
                            title: $title,
                            publication_id: $publication_id,
                            submitters: { connect: [$user_id] },
                        }
                    ) {
                        title
                        created_by {
                            id
                        }
                        updated_by {
                            id
                        }
                    }
                }
            ',
            [
                'title' => '    Test Submission    ',
                'publication_id' => $publication->id,
                'user_id' => $user->id,
            ],
        );

        $response
            ->assertJsonPath('data.createSubmissionDraft.created_by.id', (string)$user->id);
        $response
            ->assertJsonPath('data.createSubmissionDraft.updated_by.id', (string)$user->id);
    }

    public function testSubmissionContentUpload()
    {
        $user = $this->beSubmitter();
        $submission = $user->submissions->first();

        $response = $this->graphQL(
            'mutation UpdateSubmissionContent ($submission_id: ID!, $content: String!) {
                updateSubmissionContent(
                    input: {
                        id: $submission_id
                        content: $content
                    }
                ) {
                    id
                    content {
                      data
                    }
                }
            }',
            [
                'submission_id' => $submission->id,
                'content' => 'Test submission content',
            ]
        );
        $expected_data = [
            'updateSubmissionContent' => [
                'id' => (string)$submission->id,
                'content' => [
                    'data' => '<p>Test submission content</p>',
                ],
            ],
        ];
        $response->assertJsonPath('data', $expected_data);
    }

    /**
     * A publication administrator should see only submissions in publications
     * where they hold that role, and submissions in unrelated publications must
     * not cause the whole query to fail.
     *
     * @return void
     */
    public function testPublicationAdminCanQuerySubmissionsAcrossPublications()
    {
        /** @var User $admin */
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $ownPublication = Publication::factory()
            ->hasAttached($admin, [], 'publicationAdmins')
            ->create();
        $ownSubmission = Submission::factory()
            ->for($ownPublication)
            ->create(['title' => 'Own publication submission']);

        $otherPublication = Publication::factory()->create();
        Submission::factory()
            ->for($otherPublication)
            ->create(['title' => 'Other publication submission']);

        $response = $this->graphQL(
            'query GetSubmissions {
                submissions {
                    data {
                        id
                        title
                    }
                    paginatorInfo {
                        total
                    }
                }
            }'
        );

        $this->assertNull(
            $response->json('errors'),
            'Expected no authorization errors but got: ' . json_encode($response->json('errors'))
        );
        $response->assertJsonPath('data.submissions.paginatorInfo.total', 1);
        $response->assertJsonPath('data.submissions.data.0.id', (string)$ownSubmission->id);
    }

    /**
     * An application administrator should see every submission, regardless of
     * publication membership.
     *
     * @return void
     */
    public function testApplicationAdminSeesAllSubmissions()
    {
        $this->beAppAdmin();

        $pubA = Publication::factory()->create();
        $pubB = Publication::factory()->create();
        Submission::factory()->for($pubA)->create();
        Submission::factory()->for($pubB)->create();

        $response = $this->graphQL(
            'query GetSubmissions {
                submissions {
                    data { id }
                    paginatorInfo { total }
                }
            }'
        );

        $this->assertNull(
            $response->json('errors'),
            'Expected no authorization errors but got: ' . json_encode($response->json('errors'))
        );
        $response->assertJsonPath('data.submissions.paginatorInfo.total', 2);
    }

    /**
     * An editor should see only submissions in publications where they hold
     * the editor role.
     *
     * @return void
     */
    public function testEditorSeesSubmissionsOnlyInOwnPublications()
    {
        /** @var User $editor */
        $editor = User::factory()->create();
        $this->actingAs($editor);

        $ownPublication = Publication::factory()
            ->hasAttached($editor, [], 'editors')
            ->create();
        $ownSubmission = Submission::factory()
            ->for($ownPublication)
            ->create(['title' => 'Editor publication submission']);

        $otherPublication = Publication::factory()->create();
        Submission::factory()
            ->for($otherPublication)
            ->create(['title' => 'Unrelated publication submission']);

        $response = $this->graphQL(
            'query GetSubmissions {
                submissions {
                    data { id title }
                    paginatorInfo { total }
                }
            }'
        );

        $this->assertNull(
            $response->json('errors'),
            'Expected no authorization errors but got: ' . json_encode($response->json('errors'))
        );
        $response->assertJsonPath('data.submissions.paginatorInfo.total', 1);
        $response->assertJsonPath('data.submissions.data.0.id', (string)$ownSubmission->id);
    }

    /**
     * The submissions query can be filtered by status.
     *
     * @return void
     */
    public function testSubmissionsCanBeFilteredByStatus()
    {
        $this->beAppAdmin();
        $publication = Publication::factory()->create();

        $accepted = Submission::factory()
            ->for($publication)
            ->create(['title' => 'Accepted', 'status' => 6]);

        Submission::factory()
            ->for($publication)
            ->create(['title' => 'Draft', 'status' => 0]);

        $response = $this->graphQL(
            'query ($status: [SubmissionStatus!]) {
                submissions(status: $status) {
                    data { id title }
                    paginatorInfo { total }
                }
            }',
            ['status' => ['ACCEPTED_AS_FINAL']]
        );

        $response->assertJsonPath('data.submissions.paginatorInfo.total', 1);
        $response->assertJsonPath('data.submissions.data.0.id', (string)$accepted->id);
    }

    /**
     * The submissions query can be filtered by publication.
     *
     * @return void
     */
    public function testSubmissionsCanBeFilteredByPublication()
    {
        $this->beAppAdmin();
        $publicationA = Publication::factory()->create();
        $publicationB = Publication::factory()->create();

        $expected = Submission::factory()
            ->for($publicationA)
            ->create(['title' => 'In Publication A']);

        Submission::factory()
            ->for($publicationB)
            ->create(['title' => 'In Publication B']);

        $response = $this->graphQL(
            'query ($publication: [ID!]) {
                submissions(publication: $publication) {
                    data { id }
                    paginatorInfo { total }
                }
            }',
            ['publication' => [(string)$publicationA->id]]
        );

        $response->assertJsonPath('data.submissions.paginatorInfo.total', 1);
        $response->assertJsonPath('data.submissions.data.0.id', (string)$expected->id);
    }

    /**
     * The submissions query can be filtered by the authenticated user's roles.
     *
     * @return void
     */
    public function testSubmissionsCanBeFilteredByMyRoles()
    {
        $admin = $this->beAppAdmin();
        $publication = Publication::factory()->create();

        $reviewing = Submission::factory()
            ->for($publication)
            ->hasAttached($admin, [], 'reviewers')
            ->create(['title' => 'Reviewing']);

        Submission::factory()
            ->for($publication)
            ->hasAttached($admin, [], 'submitters')
            ->create(['title' => 'Submitting']);

        $response = $this->graphQL(
            'query ($my_roles: [SubmissionUserRoles!]) {
                submissions(my_roles: $my_roles) {
                    data { id }
                    paginatorInfo { total }
                }
            }',
            ['my_roles' => ['reviewer']]
        );

        $response->assertJsonPath('data.submissions.paginatorInfo.total', 1);
        $response->assertJsonPath('data.submissions.data.0.id', (string)$reviewing->id);
    }
}
