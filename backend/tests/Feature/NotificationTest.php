<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SubmissionCreation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function testSubmissionCreationNotificationForAnIndividualUser()
    {
        $user = User::factory()->create();
        $notification_data = [
            'body' => 'A submission has been created.',
            'action' => 'Visit CCR',
            'url' => '/',
            'submission_id' => 1000,
        ];
        $user->notify(new SubmissionCreation($notification_data));
        $this->assertEquals(1, $user->notifications->count());
        $this->assertEquals("App\Notifications\SubmissionCreation", $user->notifications->first()->type);
    }

    /**
     * @return void
     */
    public function testSubmissionCreationNotificationForMultipleUsers()
    {
        $users = User::factory()->count(4)->create();
        $notification_data = [
            'body' => 'A submission has been created.',
            'action' => 'Visit CCR',
            'url' => '/',
            'submission_id' => 1000,
        ];
        Notification::send($users, new SubmissionCreation($notification_data));
        $users->map(function($user) {
            $this->assertEquals(1, $user->notifications->count());
            $this->assertEquals("App\Notifications\SubmissionCreation", $user->notifications->first()->type);
        });
    }
}
