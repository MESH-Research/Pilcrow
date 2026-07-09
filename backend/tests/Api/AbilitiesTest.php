<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Auth\Abilities\AbilityExposure;
use App\Auth\Abilities\CommentAbility;
use App\Auth\Abilities\GlobalAbility;
use App\Auth\Abilities\PublicationAbility;
use App\Auth\Abilities\SubmissionAbility;
use App\Auth\Roles\ScopedRole;
use App\Models\InlineComment;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\DataProvider;
use Silber\Bouncer\BouncerFacade;
use Tests\ApiTestCase;

/**
 * The client-facing `abilities` arrays carry the GRANTED subset of the exposed
 * ability vocabulary, so the UI can gate navigation/controls without
 * re-implementing the role matrix. They are resolved through Bouncer (global)
 * and {@see \App\Auth\ScopedAbilityResolver} (scoped), the same engines the
 * policies use, so they cannot drift from real authorization.
 */
class AbilitiesTest extends ApiTestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * The GraphQL ability enums are generated from the PHP enums' Exposed cases
     * by the @abilityEnum directive, so a new exposed case appears in the schema
     * with no SDL edit. This locks each GraphQL enum's value set to exactly the
     * exposed names — if the directive breaks, an enum and its GraphQL
     * counterpart drift, or an unexposed case leaks, it fails here.
     *
     * @return array<string, array{0: string, 1: class-string}>
     */
    public static function abilityEnumProvider(): array
    {
        return [
            'UserAbility' => ['UserAbility', GlobalAbility::class],
            'PublicationAbility' => ['PublicationAbility', PublicationAbility::class],
            'SubmissionAbility' => ['SubmissionAbility', SubmissionAbility::class],
            'CommentAbility' => ['CommentAbility', CommentAbility::class],
        ];
    }

    /**
     * @param string $typeName
     * @param class-string $enum
     * @return void
     */
    #[DataProvider('abilityEnumProvider')]
    public function testAbilityEnumValuesAreGeneratedFromExposedCases(string $typeName, string $enum): void
    {
        $response = $this->graphQL(
            'query introspectType($name: String!) {
                __type(name: $name) {
                    kind
                    enumValues {
                        name
                        description
                    }
                }
            }',
            ['name' => $typeName]
        );

        $this->assertSame('ENUM', $response->json('data.__type.kind'));

        $values = collect($response->json('data.__type.enumValues'))
            ->keyBy('name');

        $exposed = AbilityExposure::exposed($enum);
        $this->assertSame(array_keys($exposed), $values->keys()->all());

        // The required Exposed description reaches introspection.
        foreach ($exposed as $exposedName => $exposure) {
            $this->assertSame($exposure['description'], $values[$exposedName]['description']);
        }
    }

    /**
     * The deprecated LegacyUpdate bridge is server-only: never exposed, never in
     * the schema vocabulary, never emitted by the resolver.
     *
     * @return void
     */
    public function testLegacyUpdateStaysServerOnly(): void
    {
        $this->assertArrayNotHasKey(
            AbilityExposure::exposedName(SubmissionAbility::LegacyUpdate),
            AbilityExposure::exposed(SubmissionAbility::class)
        );
    }

    /**
     * Conformance: every EXPOSED scoped ability is granted (conditionally or
     * not) by at least one role in the matrix — an exposed value no role can
     * ever hold is dead vocabulary, usually a case added to the enum but
     * forgotten in the matrix.
     *
     * @return void
     */
    public function testEveryExposedScopedAbilityIsGrantedSomewhere(): void
    {
        $grantedAbilities = [];
        foreach (ScopedRole::cases() as $role) {
            foreach ($role->grants() as $grant) {
                $grantedAbilities[$grant->ability::class . '::' . $grant->ability->name] = true;
            }
        }

        foreach ([SubmissionAbility::class, PublicationAbility::class, CommentAbility::class] as $enum) {
            foreach (AbilityExposure::exposed($enum) as $exposedName => $exposure) {
                $ability = $exposure['case'];
                $this->assertArrayHasKey(
                    $ability::class . '::' . $ability->name,
                    $grantedAbilities,
                    "Exposed {$ability->name} ({$exposedName}) is granted by no role."
                );
            }
        }
    }

    /**
     * Conformance (inverse): every scoped ability GRANTED by the role matrix
     * is #[Exposed] — a granted-but-unexposed ability authorizes server-side
     * but never reaches the wire, so the UI silently never offers the action.
     * Deliberately server-only cases must be allowlisted here, making the
     * exception explicit instead of a forgotten attribute.
     *
     * @return void
     */
    public function testEveryGrantedScopedAbilityIsExposed(): void
    {
        // Deliberately server-only: the deprecated LegacyUpdate bridge.
        $serverOnly = [SubmissionAbility::LegacyUpdate];

        foreach (ScopedRole::cases() as $role) {
            foreach ($role->grants() as $grant) {
                $ability = $grant->ability;
                $isServerOnly = in_array($ability, $serverOnly, true);
                if ($isServerOnly) {
                    continue;
                }
                $this->assertArrayHasKey(
                    AbilityExposure::exposedName($ability),
                    AbilityExposure::exposed($ability::class),
                    $ability::class . "::{$ability->name} is granted by role {$role->name} but not #[Exposed] — "
                    . 'add the attribute, or allowlist it above as deliberately server-only.'
                );
            }
        }
    }

    /**
     * An application administrator holds every global ability via Bouncer's
     * everything() wildcard, exposed on currentUser.abilities as the granted
     * array — including the derived admin_area union value.
     *
     * @return void
     */
    public function testGlobalAbilitiesGrantedToApplicationAdministrator(): void
    {
        $this->beAppAdmin();

        $response = $this->graphQL(
            'query {
                currentUser {
                    abilities
                }
            }'
        );

        $abilities = $response->json('data.currentUser.abilities');

        // Every exposed global ability, wildcard-granted — plus the derived union.
        $this->assertEqualsCanonicalizing(
            array_keys(AbilityExposure::exposed(GlobalAbility::class)),
            $abilities
        );
        $this->assertContains('admin_area', $abilities);
    }

    /**
     * A plain user holds no global abilities (the matrix grants them only to the
     * application administrator role at present): the granted array is empty, so
     * the client withholds admin-area access.
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
                    abilities
                }
            }'
        );

        $response->assertJsonPath('data.currentUser.abilities', []);
    }

    /**
     * admin_area is the UNION of the admin_* abilities, not a single "is admin"
     * grant: a user granted just one admin capability (here user.view-any, with
     * no publication.create) still gets the derived admin_area value. This is
     * the extension point — a future limited-admin role needs no client change
     * to appear in the admin area, yet is correctly withheld from non-admin
     * abilities.
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
                    abilities
                }
            }'
        );

        $this->assertEqualsCanonicalizing(
            ['admin_user_view_any', 'admin_area'],
            $response->json('data.currentUser.abilities')
        );
    }

    /**
     * A publication administrator can view and update their publication; the
     * granted array reflects the scoped resolver verdict for that entity.
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
                    abilities
                }
            }',
            ['id' => $publication->id]
        );

        $this->assertEqualsCanonicalizing(
            ['view', 'update'],
            $response->json('data.publication.abilities')
        );
    }

    /**
     * A submitter owns their draft: content edit, submit, view, and the
     * draft-bridge status change are granted; reviewer management is not, and a
     * draft is not reviewable so `review` is absent. The granted array mirrors
     * the matrix.
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
                    abilities
                }
            }',
            ['id' => $submission->id]
        );

        $abilities = $response->json('data.submission.abilities');

        $this->assertContains('view', $abilities);
        // Author owns the work while DRAFT — content edit and submit are on.
        $this->assertContains('update_content', $abilities);
        $this->assertContains('submit', $abilities);
        $this->assertContains('update_status', $abilities);
        $this->assertContains('update_submitters', $abilities);
        // A draft is not reviewable, so the comment-gate `review` is absent.
        $this->assertNotContains('review', $abilities);
        $this->assertNotContains('update_reviewers', $abilities);
    }

    /**
     * `review` — the reviewer's gate to the manuscript and comments — is a
     * CONDITIONAL grant held only while the submission is reviewable
     * (UNDER_REVIEW). It is the reviewer's single footprint: no content edit.
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
                    abilities
                }
            }',
            ['id' => $submission->id]
        );

        $abilities = $response->json('data.submission.abilities');
        $this->assertContains('review', $abilities);
        $this->assertNotContains('update_content', $abilities);

        $submission->update(['status' => Submission::REVISION_REQUESTED]);

        $response = $this->graphQL(
            'query getSubmission($id: ID!) {
                submission(id: $id) {
                    abilities
                }
            }',
            ['id' => $submission->id]
        );

        $this->assertNotContains('review', $response->json('data.submission.abilities'));
    }

    /**
     * The submitter's draft-only status ability is a CONDITIONAL grant: once the
     * submission leaves draft, update_status drops out of the granted array.
     * This is the key win over a role-based flag — the resolver evaluates the
     * predicate against the entity, and the client-facing array tracks it.
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
                    abilities
                }
            }',
            ['id' => $submission->id]
        );

        $this->assertNotContains('update_status', $response->json('data.submission.abilities'));
    }

    /**
     * Guests get an empty granted array — the resolver never consults the
     * engines without a viewer.
     *
     * @return void
     */
    public function testGuestGetsEmptyAbilities(): void
    {
        $publication = Publication::factory()->create(['is_publicly_visible' => true]);

        $response = $this->graphQL(
            'query getPublication($id: ID) {
                publication(id: $id) {
                    abilities
                }
            }',
            ['id' => $publication->id]
        );

        $response->assertJsonPath('data.publication.abilities', []);
    }

    /**
     * Comment update/delete is authorship-conditioned AND windowed: the author
     * (holding a scoped role on the parent submission) sees both granted while
     * the submission is under review, and loses both once it leaves the
     * reviewable window — the comment becomes part of the settled record.
     *
     * @return void
     */
    public function testCommentAbilitiesForAuthorTrackReviewableWindow(): void
    {
        $reviewer = User::factory()->create();
        $this->actingAs($reviewer);
        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($reviewer, [], 'reviewers')
            ->create(['status' => Submission::UNDER_REVIEW]);
        InlineComment::withoutEvents(fn() => InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'created_by' => $reviewer->id,
        ]));

        $query = 'query getSubmission($id: ID!) {
            submission(id: $id) {
                inline_comments { abilities }
            }
        }';

        $response = $this->graphQL($query, ['id' => $submission->id]);
        $this->assertEqualsCanonicalizing(
            ['update', 'delete'],
            $response->json('data.submission.inline_comments.0.abilities')
        );

        $submission->update(['status' => Submission::ACCEPTED_AS_FINAL]);

        $this->graphQL($query, ['id' => $submission->id])
            ->assertJsonPath('data.submission.inline_comments.0.abilities', []);
    }

    /**
     * A role holder who did NOT author the comment gets an empty granted array —
     * the authorship predicate fails — even though they can view the submission.
     *
     * @return void
     */
    public function testCommentAbilitiesDeniedToNonAuthor(): void
    {
        $reviewer = User::factory()->create();
        $author = User::factory()->create();
        $this->actingAs($reviewer);
        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->hasAttached($reviewer, [], 'reviewers')
            ->create(['status' => Submission::UNDER_REVIEW]);
        InlineComment::withoutEvents(fn() => InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'created_by' => $author->id,
        ]));

        $response = $this->graphQL(
            'query getSubmission($id: ID!) {
                submission(id: $id) {
                    inline_comments { abilities }
                }
            }',
            ['id' => $submission->id]
        );

        $response->assertJsonPath('data.submission.inline_comments.0.abilities', []);
    }

    /**
     * The application administrator role moderates: it holds update/delete on
     * any comment regardless of authorship, via the resolver's short-circuit.
     *
     * @return void
     */
    public function testCommentAbilitiesForApplicationAdministrator(): void
    {
        $this->beAppAdmin();
        $author = User::factory()->create();
        $submission = Submission::factory()
            ->for(Publication::factory()->create())
            ->create(['status' => Submission::UNDER_REVIEW]);
        InlineComment::withoutEvents(fn() => InlineComment::factory()->create([
            'submission_id' => $submission->id,
            'created_by' => $author->id,
        ]));

        $response = $this->graphQL(
            'query getSubmission($id: ID!) {
                submission(id: $id) {
                    inline_comments { abilities }
                }
            }',
            ['id' => $submission->id]
        );

        $this->assertEqualsCanonicalizing(
            ['update', 'delete'],
            $response->json('data.submission.inline_comments.0.abilities')
        );
    }
}
