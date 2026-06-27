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
                    status
                    reason
                    user { id }
                    reporter { id }
                }
            }
        ', ['userId' => $target->id, 'reason' => 'inappropriate']);

        $response->assertJsonPath('data.reportUserAvatar.status', 'PENDING');
        $response->assertJsonPath('data.reportUserAvatar.reason', 'inappropriate');
        $response->assertJsonPath('data.reportUserAvatar.user.id', (string)$target->id);
        $response->assertJsonPath('data.reportUserAvatar.reporter.id', (string)$reporter->id);

        $this->assertDatabaseHas('avatar_reports', [
            'user_id' => $target->id,
            'reporter_user_id' => $reporter->id,
            'status' => AvatarReport::STATUS_PENDING,
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

    public function testPendingDedupIndexBlocksDuplicatePendingReports(): void
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
            'status' => AvatarReport::STATUS_PENDING,
        ];
        AvatarReport::create($base);

        // A second pending report for the same (reporter, user, media) is
        // rejected at the database layer — the guarantee the resolver relies on
        // to resolve the read-then-write race.
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
        // rather than return the stale pending report for the old one.
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
            'status' => AvatarReport::STATUS_PENDING,
        ]);

        $admin = $this->beAppAdmin();

        $response = $this->graphQL('
            mutation ($id: ID!, $notes: String) {
                dismissAvatarReport(id: $id, notes: $notes) {
                    id status resolution_notes
                    resolver { id }
                }
            }
        ', ['id' => $report->id, 'notes' => 'reviewed — looks fine']);

        $response->assertJsonPath('data.dismissAvatarReport.status', 'DISMISSED');
        $response->assertJsonPath('data.dismissAvatarReport.resolver.id', (string)$admin->id);
        $response->assertJsonPath('data.dismissAvatarReport.resolution_notes', 'reviewed — looks fine');

        $this->assertNotNull($target->fresh()->getAvatarMedia(), 'Avatar should remain');
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
            'status' => AvatarReport::STATUS_PENDING,
        ]);

        $admin = $this->beAppAdmin();

        $response = $this->graphQL('
            mutation ($id: ID!) {
                resolveAvatarReportAndRemoveAvatar(id: $id) {
                    id status
                    resolver { id }
                }
            }
        ', ['id' => $report->id]);

        $response->assertJsonPath('data.resolveAvatarReportAndRemoveAvatar.status', 'REMOVED');
        $response->assertJsonPath(
            'data.resolveAvatarReportAndRemoveAvatar.resolver.id',
            (string)$admin->id
        );

        $this->assertNull($target->fresh()->getAvatarMedia(), 'Avatar should be removed');
    }

    public function testRemovingAlsoClosesOtherPendingReports(): void
    {
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        // Two reports against the same current avatar, filed via the API so
        // each holds its own private snapshot.
        $sibling = $this->reportViaApi(User::factory()->create(), $target);
        $resolved = $this->reportViaApi(User::factory()->create(), $target);
        $this->assertNotNull($sibling->getSnapshotMedia());

        $this->beAppAdmin();

        $this->graphQL(
            'mutation ($id: ID!) { resolveAvatarReportAndRemoveAvatar(id: $id) { id } }',
            ['id' => $resolved->id]
        );

        $pending = AvatarReport::where('user_id', $target->id)
            ->where('status', AvatarReport::STATUS_PENDING)
            ->count();
        $this->assertSame(0, $pending);

        $sibling->refresh();
        $this->assertSame(AvatarReport::STATUS_REMOVED, $sibling->status);
        $this->assertNull(
            $sibling->getSnapshotMedia(),
            'A bulk-closed sibling report has its snapshot purged too'
        );
    }

    public function testCannotResolveAlreadyResolvedReport(): void
    {
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        $report = AvatarReport::create([
            'user_id' => $target->id,
            'reporter_user_id' => User::factory()->create()->id,
            'status' => AvatarReport::STATUS_DISMISSED,
        ]);

        $this->beAppAdmin();

        $response = $this->graphQL(
            'mutation ($id: ID!) { dismissAvatarReport(id: $id) { id } }',
            ['id' => $report->id]
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
            'status' => AvatarReport::STATUS_PENDING,
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
    }

    public function testAdminCanListPendingReports(): void
    {
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);

        AvatarReport::create([
            'user_id' => $target->id,
            'reporter_user_id' => User::factory()->create()->id,
            'status' => AvatarReport::STATUS_PENDING,
        ]);
        AvatarReport::create([
            'user_id' => $target->id,
            'reporter_user_id' => User::factory()->create()->id,
            'status' => AvatarReport::STATUS_DISMISSED,
        ]);

        $this->beAppAdmin();

        $response = $this->graphQL(
            'query ($status: AvatarReportStatus) {
                avatarReports(status: $status) {
                    paginatorInfo { count }
                    data { id status }
                }
            }',
            ['status' => 'PENDING']
        );

        $response->assertJsonPath('data.avatarReports.paginatorInfo.count', 1);
        $response->assertJsonPath('data.avatarReports.data.0.status', 'PENDING');
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
            'status' => AvatarReport::STATUS_PENDING,
        ]);

        $this->beAppAdmin();

        $this->graphQL('
            mutation ($id: ID!, $block: Boolean) {
                resolveAvatarReportAndRemoveAvatar(id: $id, blockFutureUploads: $block) {
                    id status
                }
            }
        ', ['id' => $report->id, 'block' => true])
            ->assertJsonPath('data.resolveAvatarReportAndRemoveAvatar.status', 'REMOVED');

        $this->assertTrue(
            $target->fresh()->hasModerationFlag(ModerationFlag::AvatarUploadBlocked)
        );
    }

    public function testAdminCanBlockAndUnblockUserUploads(): void
    {
        $target = User::factory()->create();
        $this->beAppAdmin();

        $this->graphQL('
            mutation ($id: ID!, $blocked: Boolean!) {
                setUserAvatarUploadBlocked(userId: $id, blocked: $blocked) {
                    id
                    avatar_upload_blocked
                }
            }
        ', ['id' => $target->id, 'blocked' => true])
            ->assertJsonPath('data.setUserAvatarUploadBlocked.avatar_upload_blocked', true);

        $this->assertTrue(
            $target->fresh()->hasModerationFlag(ModerationFlag::AvatarUploadBlocked)
        );

        $this->graphQL('
            mutation ($id: ID!, $blocked: Boolean!) {
                setUserAvatarUploadBlocked(userId: $id, blocked: $blocked) {
                    id
                    avatar_upload_blocked
                }
            }
        ', ['id' => $target->id, 'blocked' => false])
            ->assertJsonPath('data.setUserAvatarUploadBlocked.avatar_upload_blocked', false);

        $this->assertFalse(
            $target->fresh()->hasModerationFlag(ModerationFlag::AvatarUploadBlocked)
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
            'query ($s: AvatarReportStatus) {
                avatarReports(status: $s) { data { id reported_avatar_url } }
            }',
            ['s' => 'PENDING']
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
                resolveAvatarReportAndRemoveAvatar(id: $id) { id status resolution_notes }
            }
        ', ['id' => $report->id])
            ->assertJsonPath('data.resolveAvatarReportAndRemoveAvatar.status', 'REMOVED');

        $current = $target->fresh()->getAvatarMedia();
        $this->assertNotNull($current, 'The replacement avatar must not be removed');
        $this->assertSame(
            (int)$newMedia->id,
            (int)$current->id,
            'The current (replacement) avatar should be untouched'
        );
        $this->assertStringContainsString(
            'left intact',
            (string)$report->fresh()->resolution_notes
        );
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
            'mutation ($id: ID!) { resolveAvatarReportAndRemoveAvatar(id: $id) { id status } }',
            ['id' => $stale->id]
        )->assertJsonPath('data.resolveAvatarReportAndRemoveAvatar.status', 'REMOVED');

        $this->assertNotNull(
            $target->fresh()->getAvatarMedia(),
            'Innocent replacement avatar must be left intact'
        );
        $this->assertSame(
            AvatarReport::STATUS_PENDING,
            $other->fresh()->status,
            'A stale resolve must NOT close sibling reports — nothing was removed'
        );
    }

    public function testResolveAndRemovePurgesSnapshot(): void
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
        $this->assertNull(
            $report->fresh()->getSnapshotMedia(),
            'Snapshot is purged the moment the report is resolved — no post-resolution retention'
        );
    }

    public function testDismissPurgesSnapshot(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();
        $this->giveUserAnAvatar($target);
        $report = $this->reportViaApi($reporter, $target);
        $this->assertNotNull($report->getSnapshotMedia());

        $this->beAppAdmin();
        $this->graphQL(
            'mutation ($id: ID!) { dismissAvatarReport(id: $id) { id status } }',
            ['id' => $report->id]
        )->assertJsonPath('data.dismissAvatarReport.status', 'DISMISSED');

        $this->assertNull(
            $report->fresh()->getSnapshotMedia(),
            'Dismissing a report should purge the retained snapshot immediately'
        );
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
