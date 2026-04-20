<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\AvatarReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\ApiTestCase;

class AvatarReportMutationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('media-library.disk_name'));
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

        AvatarReport::create([
            'user_id' => $target->id,
            'reporter_user_id' => User::factory()->create()->id,
            'status' => AvatarReport::STATUS_PENDING,
        ]);
        $second = AvatarReport::create([
            'user_id' => $target->id,
            'reporter_user_id' => User::factory()->create()->id,
            'status' => AvatarReport::STATUS_PENDING,
        ]);

        $this->beAppAdmin();

        $this->graphQL(
            'mutation ($id: ID!) { resolveAvatarReportAndRemoveAvatar(id: $id) { id } }',
            ['id' => $second->id]
        );

        $pending = AvatarReport::where('user_id', $target->id)
            ->where('status', AvatarReport::STATUS_PENDING)
            ->count();
        $this->assertSame(0, $pending);
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
}
