<?php
declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Events\SubmissionCreated;
use App\Listeners\NotifyUsersAboutCreatedSubmission;
use App\Models\Publication;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\SubmissionCreated as NotificationsSubmissionCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubmissionCreatedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testThatNotificationsAreSentToTheExpectedUsers()
    {
        Notification::fake();
        $submitter = User::factory()->create();
        $editor = User::factory()->create();
        $publication = Publication::factory()
            ->hasAttached(
                $editor,
                [
                    'role_id' => Role::EDITOR_ROLE_ID,
                ]
            )
            ->create();
        $submission = Submission::factory()
            ->hasAttached(
                $submitter,
                [
                    'role_id' => Role::SUBMITTER_ROLE_ID,
                ]
            )
            ->create([
                'publication_id' => $publication->id,
            ]);
        $event = new SubmissionCreated($submission);
        $listener = new NotifyUsersAboutCreatedSubmission();
        $listener->handle($event);
        Notification::assertSentTo($submitter, NotificationsSubmissionCreated::class);
        Notification::assertSentTo($editor, NotificationsSubmissionCreated::class);
    }
}
