<?php
declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Events\SubmissionCreated;
use App\Listeners\EmailUsersAboutCreatedSubmission;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\SubmissionCreated as NotificationsSubmissionCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubmissionCreatedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testThatNotificationsAreSent()
    {
        Notification::fake();
        Mail::fake();

        $user = User::factory()->create();
        $publication = Publication::factory()->create();
        $submission = Submission::factory()
            ->hasAttached(
                $user,
                [
                    'role_id' => 6,
                ]
            )
            ->create([
                'publication_id' => $publication->id,
            ]);
        $event = new SubmissionCreated($submission);
        $listener = new EmailUsersAboutCreatedSubmission();
        $listener->handle($event);

        Notification::assertSentTo($user, NotificationsSubmissionCreated::class);
        Mail::assertSent(NotificationsSubmissionCreated::class);
    }
}
