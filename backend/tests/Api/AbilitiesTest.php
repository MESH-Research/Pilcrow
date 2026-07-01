<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Auth\Abilities\GlobalAbility;
use App\Auth\Abilities\PublicationAbility;
use App\Auth\Abilities\SubmissionAbility;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\DataProvider;
use Silber\Bouncer\BouncerFacade;
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
     * The ability GraphQL types are generated from the enums by the AbilityFields
     * directive, so a new ability case appears in the schema with no SDL edit.
     * This locks each type's field set to exactly the snake_case enum cases — if
     * the directive breaks or an enum and its type drift, it fails here rather
     * than silently dropping a flag the client relies on.
     *
     * @return array<string, array{0: string, 1: class-string, 2: array<int, string>}>
     */
    public static function abilityTypeProvider(): array
    {
        return [
            // UserAbilities also carries the manually-declared `admin_area` union
            // flag alongside the generated case fields.
            'UserAbilities' => ['UserAbilities', GlobalAbility::class, ['admin_area']],
            'PublicationAbilities' => ['PublicationAbilities', PublicationAbility::class, []],
            'SubmissionAbilities' => ['SubmissionAbilities', SubmissionAbility::class, []],
        ];
    }

    /**
     * @param string $typeName
     * @param class-string $enum
     * @param array<int, string> $extraFields manually-declared fields that coexist
     *   with the generated case fields
     * @return void
     */
    #[DataProvider('abilityTypeProvider')]
    public function testAbilityTypeFieldsAreGeneratedFromTheEnum(string $typeName, string $enum, array $extraFields): void
    {
        $response = $this->graphQL(
            'query introspectType($name: String!) {
                __type(name: $name) {
                    fields {
                        name
                        type {
                            kind
                            ofType {
                                name
                            }
                        }
                    }
                }
            }',
            ['name' => $typeName]
        );

        $fields = $response->json('data.__type.fields');
        $fieldNames = array_column($fields, 'name');
        sort($fieldNames);

        $expected = array_map(
            static fn($case) => Str::snake($case->name),
            $enum::cases()
        );
        $expected = array_merge($expected, $extraFields);
        sort($expected);

        $this->assertSame($expected, $fieldNames);

        // Every generated field is a non-null Boolean.
        foreach ($fields as $field) {
            $this->assertSame('NON_NULL', $field['type']['kind']);
            $this->assertSame('Boolean', $field['type']['ofType']['name']);
        }
    }

    /**
     * An application administrator holds every global ability via Bouncer's
     * everything() wildcard, exposed on currentUser.abilities. The admin-area
     * capabilities surface as `admin_*` flags (generated from the `Admin`-prefixed
     * enum cases); the client treats holding any of them as admin-area access.
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
                        admin_user_view_any
                        admin_user_update
                        admin_user_manage_beta
                        admin_area
                    }
                }
            }'
        );

        $response->assertJsonPath('data.currentUser.abilities', [
            'publication_create' => true,
            'admin_user_view_any' => true,
            'admin_user_update' => true,
            'admin_user_manage_beta' => true,
            'admin_area' => true,
        ]);
    }

    /**
     * A plain user holds no global abilities (the matrix grants them only to the
     * application administrator role at present), so it surfaces no `admin_*`
     * flag and the client withholds admin-area access.
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
                        admin_user_view_any
                        admin_area
                    }
                }
            }'
        );

        $response->assertJsonPath('data.currentUser.abilities', [
            'publication_create' => false,
            'admin_user_view_any' => false,
            'admin_area' => false,
        ]);
    }

    /**
     * admin_area is the UNION of the admin_* abilities, not a single "is admin"
     * flag: a user granted just one admin capability (here user.view-any, with no
     * publication.create) still gets admin-area access. This is the extension
     * point — a future limited-admin role needs no client change to appear in the
     * admin area, yet is correctly withheld from non-admin abilities.
     *
     * @return void
     */
    public function testAdminAreaIsGrantedByAnySingleAdminAbility(): void
    {
        $user = User::factory()->create();
        BouncerFacade::allow($user)->to(GlobalAbility::AdminUserViewAny->value);
        BouncerFacade::refresh();
        $this->actingAs($user);

        $response = $this->graphQL(
            'query {
                currentUser {
                    abilities {
                        publication_create
                        admin_user_view_any
                        admin_user_update
                        admin_area
                    }
                }
            }'
        );

        $response->assertJsonPath('data.currentUser.abilities', [
            'publication_create' => false,
            'admin_user_view_any' => true,
            'admin_user_update' => false,
            'admin_area' => true,
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
                        update_content
                        submit
                        review
                        update_status
                        update_reviewers
                    }
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.submission.abilities.view', true);
        // Author owns the work while DRAFT — content edit and submit are on.
        $response->assertJsonPath('data.submission.abilities.update_content', true);
        $response->assertJsonPath('data.submission.abilities.submit', true);
        // A draft is not reviewable, so the comment-gate `review` is off.
        $response->assertJsonPath('data.submission.abilities.review', false);
        $response->assertJsonPath('data.submission.abilities.update_status', true);
        $response->assertJsonPath('data.submission.abilities.update_reviewers', false);
    }

    /**
     * `review` — the reviewer's gate to the manuscript and comments — is a
     * CONDITIONAL grant held only while the submission is reviewable
     * (UNDER_REVIEW). It is the reviewer's single footprint: no `update`.
     *
     * @return void
     */
    public function testSubmissionReviewAbilityForReviewerTracksReviewable(): void
    {
        $reviewer = User::factory()->create();
        $this->actingAs($reviewer);
        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($reviewer, [], 'reviewers')
            ->create(['status' => Submission::UNDER_REVIEW]);

        $response = $this->graphQL(
            'query getSubmission($id: ID!) {
                submission(id: $id) {
                    abilities {
                        review
                        update_content
                    }
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.submission.abilities.review', true);
        $response->assertJsonPath('data.submission.abilities.update_content', false);

        $submission->update(['status' => Submission::REVISION_REQUESTED]);

        $response = $this->graphQL(
            'query getSubmission($id: ID!) {
                submission(id: $id) {
                    abilities {
                        review
                    }
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.submission.abilities.review', false);
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
