<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Enums\ModerationFlag;
use App\Models\AvatarReport;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Models\Audit;
use Tests\ApiTestCase;

class AvatarReportMutationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('media-library.disk_name'));
        Storage::fake(AvatarReport::SNAPSHOT_DISK);
    }

    /**
     * Upload an avatar on behalf of $user so there's something to report.
     */
    private function giveUserAnAvatar(User $user): void
    {
        $file = UploadedFile::fake()->image('avatar.png', 400, 400);
        $user->addMedia($file)
            ->usingFileName('avatar.png')
            ->toMediaCollection(User::AVATAR_COLLECTION);
    }

    /**
     * File a report through the API so it captures media_id, the durable
     * reported UUID, and the private snapshot — exactly as production does.
     */
    private function reportViaApi(User $reporter, User $target): AvatarReport
    {
        $this->actingAs($reporter);
        $id = $this->graphQL(
            'mutation ($id: ID!) { reportUserAvatar(userId: $id) { id } }',
            ['id' => $target->id]
        )->json('data.reportUserAvatar.id');

        return AvatarReport::findOrFail($id);
    }

    /**
     * The most recent moderation audit of a given event recorded about a user.
     */
    private function latestAudit(User $user, string $event): ?Audit
    {
        /** @var Audit|null $audit */
        $audit = $user->audits()->where('event', $event)->latest('id')->first();

        return $audit;
    }

    public function testAuthenticatedUserCanReportAvatar(): void
    {
        /** @var User $reporter */
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        $this->actingAs($reporter);

        $response = $this->graphQL('
            mutation ($userId: ID!, $reason: String) {
                reportUserAvatar(userId: $userId, reason: $reason) {
                    id
                    reason
                    user { id }
                    reporter { id }
                }
            }
        ', ['userId' => $target->id, 'reason' => 'inappropriate']);

        $response->assertJsonPath('data.reportUserAvatar.reason', 'inappropriate');
        $response->assertJsonPath('data.reportUserAvatar.user.id', (string)$target->id);
        $response->assertJsonPath('data.reportUserAvatar.reporter.id', (string)$reporter->id);

        $this->assertDatabaseHas('avatar_reports', [
            'user_id' => $target->id,
            'reporter_user_id' => $reporter->id,
        ]);
    }

    public function testUnauthenticatedCannotReport(): void
    {
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        $response = $this->graphQL(
            'mutation ($id: ID!) { reportUserAvatar(userId: $id) { id } }',
            ['id' => $target->id]
        );

        $response->assertJsonPath('data.reportUserAvatar', null);
        $this->assertDatabaseCount('avatar_reports', 0);
    }

    public function testCannotReportUserWithoutAvatar(): void
    {
        /** @var User $reporter */
        $reporter = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($reporter);

        $response = $this->graphQL(
            'mutation ($id: ID!) { reportUserAvatar(userId: $id) { id } }',
            ['id' => $target->id]
        );

        $this->assertNotEmpty($response->json('errors'));
        $this->assertDatabaseCount('avatar_reports', 0);
    }

    public function testCannotReportSelf(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->giveUserAnAvatar($user);

        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation ($id: ID!) { reportUserAvatar(userId: $id) { id } }',
            ['id' => $user->id]
        );

        $this->assertNotEmpty($response->json('errors'));
        $this->assertDatabaseCount('avatar_reports', 0);
    }

    public function testReportingTwiceReturnsExistingPending(): void
    {
        /** @var User $reporter */
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        $this->actingAs($reporter);

        $first = $this->graphQL(
            'mutation ($id: ID!) { reportUserAvatar(userId: $id) { id } }',
            ['id' => $target->id]
        )->json('data.reportUserAvatar.id');

        $second = $this->graphQL(
            'mutation ($id: ID!) { reportUserAvatar(userId: $id) { id } }',
            ['id' => $target->id]
        )->json('data.reportUserAvatar.id');

        $this->assertEquals($first, $second);
        $this->assertDatabaseCount('avatar_reports', 1);
    }

    public function testRateLimitsExcessiveReporting(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        // Saturate the reporter's hourly budget directly so the test doesn't
        // have to file 15 real reports.
        $key = 'avatar-report:' . $reporter->id;
        for ($i = 0; $i < 15; $i++) {
            RateLimiter::hit($key, 3600);
        }

        $this->actingAs($reporter);
        $response = $this->graphQL(
            'mutation ($id: ID!) { reportUserAvatar(userId: $id) { id } }',
            ['id' => $target->id]
        );

        $this->assertNotEmpty($response->json('errors'));
        $this->assertNull($response->json('data.reportUserAvatar'));
        $this->assertDatabaseCount('avatar_reports', 0);
    }

    public function testPendingDedupIndexBlocksDuplicateReports(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);
        $media = $target->fresh()->getAvatarMedia();

        $base = [
            'user_id' => $target->id,
            'media_id' => $media->id,
            'reported_media_uuid' => $media->uuid,
            'reporter_user_id' => $reporter->id,
        ];
        AvatarReport::create($base);

        // A second report for the same (reporter, user, media) is rejected at
        // the database layer — the guarantee the resolver relies on to resolve
        // the read-then-write race.
        $this->expectException(QueryException::class);
        AvatarReport::create($base);
    }

    public function testReportingASwappedAvatarRecordsANewReport(): void
    {
        /** @var User $reporter */
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        $first = $this->reportViaApi($reporter, $target);

        // Target swaps to a different image; the single-file collection
        // replaces the reported media. Re-reporting must record the new image
        // rather than return the existing report for the old one.
        $this->giveUserAnAvatar($target);
        $newMedia = $target->fresh()->getAvatarMedia();

        $second = $this->reportViaApi($reporter, $target);

        $this->assertNotEquals($first->id, $second->id);
        $this->assertSame((int)$newMedia->id, (int)$second->media_id);
        $this->assertDatabaseCount('avatar_reports', 2);
    }

    public function testAdminCanDismissReport(): void
    {
        /** @var User $reporter */
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        /** @var AvatarReport $report */
        $report = AvatarReport::create([
            'user_id' => $target->id,
            'reporter_user_id' => $reporter->id,
            'reason' => 'looks off',
        ]);

        $this->beAppAdmin();

        $response = $this->graphQL('
            mutation ($id: ID!) {
                dismissAvatarReport(id: $id) { id }
            }
        ', ['id' => $report->id]);

        // Returns the reported user; the report is gone; the avatar remains.
        $response->assertJsonPath('data.dismissAvatarReport.id', (string)$target->id);
        $this->assertDatabaseMissing('avatar_reports', ['id' => $report->id]);
        $this->assertNotNull($target->fresh()->getAvatarMedia(), 'Avatar should remain');

        // Dismissal takes no action against the user, so nothing is audited.
        $this->assertCount(0, $target->fresh()->audits()->get());
    }

    public function testAdminCanResolveAndRemoveAvatar(): void
    {
        /** @var User $reporter */
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        /** @var AvatarReport $report */
        $report = AvatarReport::create([
            'user_id' => $target->id,
            'reporter_user_id' => $reporter->id,
        ]);

        $admin = $this->beAppAdmin();

        $response = $this->graphQL('
            mutation ($id: ID!) {
                resolveAvatarReportAndRemoveAvatar(id: $id) { id }
            }
        ', ['id' => $report->id]);

        $response->assertJsonPath(
            'data.resolveAvatarReportAndRemoveAvatar.id',
            (string)$target->id
        );
        $this->assertNull($target->fresh()->getAvatarMedia(), 'Avatar should be removed');
        $this->assertDatabaseMissing('avatar_reports', ['id' => $report->id]);

        $audit = $this->latestAudit($target, 'avatar_removed');
        $this->assertNotNull($audit, 'removal should be audited');
        $this->assertSame((int)$admin->id, (int)$audit->user_id);
    }

    public function testRemovingDeletesAllPendingReportsForTheUser(): void
    {
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        // Two reports against the same current avatar, filed via the API so
        // each holds its own private snapshot.
        $this->reportViaApi(User::factory()->create(), $target);
        $resolved = $this->reportViaApi(User::factory()->create(), $target);

        $this->beAppAdmin();

        $this->graphQL(
            'mutation ($id: ID!) { resolveAvatarReportAndRemoveAvatar(id: $id) { id } }',
            ['id' => $resolved->id]
        );

        // Removing the avatar moots every pending report against this user, so
        // they're all deleted; a single avatar_removed audit is the record.
        $this->assertSame(
            0,
            AvatarReport::where('user_id', $target->id)->count(),
            'all pending reports for the user are deleted on removal'
        );
        $this->assertCount(
            1,
            $target->fresh()->audits()->where('event', 'avatar_removed')->get()
        );
    }

    public function testDismissingAMissingReportSurfacesError(): void
    {
        $this->beAppAdmin();

        // The report was already resolved (and thus deleted); acting on it again
        // surfaces a clean error rather than a 500.
        $response = $this->graphQL(
            'mutation ($id: ID!) { dismissAvatarReport(id: $id) { id } }',
            ['id' => '999999']
        );

        $this->assertNotEmpty($response->json('errors'));
    }

    public function testNonAdminCannotDismissOrResolve(): void
    {
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);
        $report = AvatarReport::create([
            'user_id' => $target->id,
            'reporter_user_id' => User::factory()->create()->id,
        ]);

        /** @var User $regular */
        $regular = User::factory()->create();
        $this->actingAs($regular);

        $dismiss = $this->graphQL(
            'mutation ($id: ID!) { dismissAvatarReport(id: $id) { id } }',
            ['id' => $report->id]
        );
        $dismiss->assertJsonPath('data.dismissAvatarReport', null);

        $remove = $this->graphQL(
            'mutation ($id: ID!) { resolveAvatarReportAndRemoveAvatar(id: $id) { id } }',
            ['id' => $report->id]
        );
        $remove->assertJsonPath('data.resolveAvatarReportAndRemoveAvatar', null);

        $this->assertDatabaseHas('avatar_reports', ['id' => $report->id]);
    }

    public function testAdminCanListPendingReports(): void
    {
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        AvatarReport::create([
            'user_id' => $target->id,
            'reporter_user_id' => User::factory()->create()->id,
        ]);

        $this->beAppAdmin();

        $response = $this->graphQL('
            query {
                avatarReports {
                    paginatorInfo { count }
                    data { id }
                }
            }
        ');

        $response->assertJsonPath('data.avatarReports.paginatorInfo.count', 1);
    }

    public function testNonAdminCannotListReports(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL('
            { avatarReports { paginatorInfo { count } data { id } } }
        ');
        $response->assertJsonPath('data.avatarReports', null);
    }

    public function testResolveAndRemoveCanAlsoBlockFutureUploads(): void
    {
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);
        $report = AvatarReport::create([
            'user_id' => $target->id,
            'reporter_user_id' => User::factory()->create()->id,
        ]);

        $this->beAppAdmin();

        $this->graphQL('
            mutation ($id: ID!, $block: Boolean) {
                resolveAvatarReportAndRemoveAvatar(id: $id, blockFutureUploads: $block) {
                    id avatar_upload_blocked
                }
            }
        ', ['id' => $report->id, 'block' => true])
            ->assertJsonPath('data.resolveAvatarReportAndRemoveAvatar.avatar_upload_blocked', true);

        $this->assertTrue(
            $target->fresh()->hasModerationFlag(ModerationFlag::AvatarUploadBlocked)
        );
        $this->assertNotNull(
            $this->latestAudit($target, 'avatar_upload_blocked'),
            'blocking as part of a removal is audited'
        );
    }

    public function testAdminCanBlockAndUnblockUserUploads(): void
    {
        $target = User::factory()->create();
        $admin = $this->beAppAdmin();

        $this->graphQL('
            mutation ($id: ID!, $blocked: Boolean!) {
                setUserAvatarUploadBlocked(userId: $id, blocked: $blocked) {
                    id avatar_upload_blocked
                }
            }
        ', ['id' => $target->id, 'blocked' => true])
            ->assertJsonPath('data.setUserAvatarUploadBlocked.avatar_upload_blocked', true);

        $this->assertTrue(
            $target->fresh()->hasModerationFlag(ModerationFlag::AvatarUploadBlocked)
        );
        $blockAudit = $this->latestAudit($target, 'avatar_upload_blocked');
        $this->assertNotNull($blockAudit);
        $this->assertSame((int)$admin->id, (int)$blockAudit->user_id);

        $this->graphQL('
            mutation ($id: ID!, $blocked: Boolean!) {
                setUserAvatarUploadBlocked(userId: $id, blocked: $blocked) {
                    id avatar_upload_blocked
                }
            }
        ', ['id' => $target->id, 'blocked' => false])
            ->assertJsonPath('data.setUserAvatarUploadBlocked.avatar_upload_blocked', false);

        $this->assertFalse(
            $target->fresh()->hasModerationFlag(ModerationFlag::AvatarUploadBlocked)
        );
        $this->assertNotNull($this->latestAudit($target, 'avatar_upload_unblocked'));
    }

    public function testRepeatingTheSameBlockedStateDoesNotChurnTheAuditLog(): void
    {
        $target = User::factory()->create();
        $this->beAppAdmin();

        $block = fn() => $this->graphQL('
            mutation ($id: ID!, $blocked: Boolean!) {
                setUserAvatarUploadBlocked(userId: $id, blocked: $blocked) { id }
            }
        ', ['id' => $target->id, 'blocked' => true]);

        $block();
        $block();

        $this->assertCount(
            1,
            $target->fresh()->audits()->where('event', 'avatar_upload_blocked')->get(),
            'a no-op re-block should not add another audit entry'
        );
    }

    public function testReportingNonExistentUserSurfacesGraphqlError(): void
    {
        /** @var User $reporter */
        $reporter = User::factory()->create();
        $this->actingAs($reporter);

        $response = $this->graphQL(
            'mutation ($id: ID!) { reportUserAvatar(userId: $id) { id } }',
            ['id' => '999999']
        );

        $response->assertJsonPath('data.reportUserAvatar', null);
        $this->assertNotEmpty(
            $response->json('errors'),
            'findOrFail should surface as a GraphQL error, not a 500'
        );
        $this->assertDatabaseCount('avatar_reports', 0);
    }

    public function testSetBlockedOnNonExistentUserSurfacesGraphqlError(): void
    {
        $this->beAppAdmin();

        $response = $this->graphQL(
            'mutation ($id: ID!, $blocked: Boolean!) {
                setUserAvatarUploadBlocked(userId: $id, blocked: $blocked) { id }
            }',
            ['id' => '999999', 'blocked' => true]
        );

        $response->assertJsonPath('data.setUserAvatarUploadBlocked', null);
        $this->assertNotEmpty($response->json('errors'));
    }

    public function testNonAdminCannotSetBlockedState(): void
    {
        $target = User::factory()->create();
        /** @var User $regular */
        $regular = User::factory()->create();
        $this->actingAs($regular);

        $response = $this->graphQL('
            mutation ($id: ID!) {
                setUserAvatarUploadBlocked(userId: $id, blocked: true) {
                    id avatar_upload_blocked
                }
            }
        ', ['id' => $target->id]);

        $response->assertJsonPath('data.setUserAvatarUploadBlocked', null);
        $this->assertFalse(
            $target->fresh()->hasModerationFlag(ModerationFlag::AvatarUploadBlocked),
            'Target should not be blocked by an unauthorized request'
        );
    }

    public function testReportCapturesReportedMediaAndSnapshot(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);
        $reportedMedia = $target->fresh()->getAvatarMedia();

        $report = $this->reportViaApi($reporter, $target);

        $this->assertSame((int)$reportedMedia->id, (int)$report->media_id);
        $this->assertSame($reportedMedia->uuid, $report->reported_media_uuid);
        $this->assertNotNull(
            $report->getSnapshotMedia(),
            'Reporting should retain a private snapshot of the reported image'
        );
        $this->assertSame(
            AvatarReport::SNAPSHOT_DISK,
            $report->getSnapshotMedia()->disk
        );
    }

    public function testReportedAvatarUrlExposedToModerator(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);
        $report = $this->reportViaApi($reporter, $target);

        $this->beAppAdmin();

        $this->graphQL(
            'query { avatarReports { data { id reported_avatar_url } } }'
        )->assertJsonPath(
            'data.avatarReports.data.0.reported_avatar_url',
            route('avatar-report.snapshot', ['avatarReport' => $report->id])
        );
    }

    public function testResolveAndRemoveLeavesAChangedAvatarIntact(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);
        $report = $this->reportViaApi($reporter, $target);

        // Target swaps their avatar after the report; the reported image is
        // hard-deleted (single-file collection) and replaced with a new one.
        $this->giveUserAnAvatar($target);
        $newMedia = $target->fresh()->getAvatarMedia();

        $this->beAppAdmin();
        $this->graphQL('
            mutation ($id: ID!) {
                resolveAvatarReportAndRemoveAvatar(id: $id) { id }
            }
        ', ['id' => $report->id])
            ->assertJsonPath('data.resolveAvatarReportAndRemoveAvatar.id', (string)$target->id);

        $current = $target->fresh()->getAvatarMedia();
        $this->assertNotNull($current, 'The replacement avatar must not be removed');
        $this->assertSame(
            (int)$newMedia->id,
            (int)$current->id,
            'The current (replacement) avatar should be untouched'
        );

        // A stale resolve removes nothing and takes no action against the user,
        // so it discards the report without recording anything.
        $this->assertDatabaseMissing('avatar_reports', ['id' => $report->id]);
        $this->assertCount(0, $target->fresh()->audits()->get());
    }

    public function testResolvingAStaleReportLeavesOtherReportsPending(): void
    {
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        // Two reports against the current (reported) avatar.
        $stale = $this->reportViaApi(User::factory()->create(), $target);
        $other = $this->reportViaApi(User::factory()->create(), $target);

        // Target swaps their avatar, so the first report is now stale (the
        // reported image is gone; the current one is a possibly-innocent
        // replacement the other report may concern).
        $this->giveUserAnAvatar($target);

        $this->beAppAdmin();
        $this->graphQL(
            'mutation ($id: ID!) { resolveAvatarReportAndRemoveAvatar(id: $id) { id } }',
            ['id' => $stale->id]
        );

        $this->assertNotNull(
            $target->fresh()->getAvatarMedia(),
            'Innocent replacement avatar must be left intact'
        );
        // A stale resolve must NOT delete sibling reports — nothing was removed.
        $this->assertDatabaseMissing('avatar_reports', ['id' => $stale->id]);
        $this->assertDatabaseHas('avatar_reports', ['id' => $other->id]);
    }

    public function testResolveAndRemoveDeletesReportAndSnapshot(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);
        $report = $this->reportViaApi($reporter, $target);
        $this->assertNotNull($report->getSnapshotMedia());

        $this->beAppAdmin();
        $this->graphQL(
            'mutation ($id: ID!) { resolveAvatarReportAndRemoveAvatar(id: $id) { id } }',
            ['id' => $report->id]
        );

        $this->assertNull($target->fresh()->getAvatarMedia(), 'Reported avatar removed');
        $this->assertDatabaseMissing('avatar_reports', ['id' => $report->id]);
        $this->assertDatabaseMissing('media', [
            'model_type' => AvatarReport::class,
            'model_id' => $report->id,
        ]);
    }

    public function testDismissDeletesReportAndSnapshot(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);
        $report = $this->reportViaApi($reporter, $target);
        $this->assertNotNull($report->getSnapshotMedia());

        $this->beAppAdmin();
        $this->graphQL(
            'mutation ($id: ID!) { dismissAvatarReport(id: $id) { id } }',
            ['id' => $report->id]
        );

        $this->assertDatabaseMissing('avatar_reports', ['id' => $report->id]);
        $this->assertDatabaseMissing('media', [
            'model_type' => AvatarReport::class,
            'model_id' => $report->id,
        ]);
    }

    public function testModerationHistoryReadableByModerator(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);
        $report = AvatarReport::create([
            'user_id' => $target->id,
            'reporter_user_id' => $reporter->id,
        ]);

        $this->beAppAdmin();
        $this->graphQL(
            'mutation ($id: ID!) { resolveAvatarReportAndRemoveAvatar(id: $id) { id } }',
            ['id' => $report->id]
        );

        $response = $this->graphQL(
            'query ($id: ID!) {
                user(id: $id) { id moderationHistory { event } }
            }',
            ['id' => $target->id]
        );

        $response->assertJsonPath(
            'data.user.moderationHistory.0.event',
            'avatar_removed'
        );
    }

    public function testModerationHistoryHiddenFromNonModerator(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // A user cannot read their own moderation file; the field is gated.
        $response = $this->graphQL('{ currentUser { id moderationHistory { event } } }');

        $this->assertNotEmpty($response->json('errors'));
        $this->assertNull($response->json('data.currentUser.moderationHistory'));
    }

    public function testSnapshotRouteIsModeratorGated(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);
        $report = $this->reportViaApi($reporter, $target);
        $url = route('avatar-report.snapshot', ['avatarReport' => $report->id]);

        // A non-moderator is forbidden.
        $this->actingAs(User::factory()->create());
        $this->get($url)->assertForbidden();

        // A moderator gets the streamed image.
        $this->beAppAdmin();
        $this->get($url)->assertOk();
    }
}
