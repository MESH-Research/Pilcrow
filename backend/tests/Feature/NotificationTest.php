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
        $this->assertEquals($notification_data, $user->notifications->first()->data);
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
            'submission_id' => 1001,
        ];
        Notification::send($users, new SubmissionCreation($notification_data));
        $users->map(function ($user) use ($notification_data) {
            $this->assertEquals(1, $user->notifications->count());
            $this->assertEquals("App\Notifications\SubmissionCreation", $user->notifications->first()->type);
            $this->assertEquals($notification_data, $user->notifications->first()->data);
        });
    }

    /**
     * @return void
     */
    public function testMultipleSubmissionCreationNotificationsForAnIndividualUser()
    {
        $user = User::factory()->create();
        $notification_data_1 = [
            'body' => 'A submission has been created.',
            'action' => 'Visit CCR',
            'url' => '/',
            'submission_id' => 1002,
        ];
        $notification_data_2 = [
            'body' => 'A submission has been created.',
            'action' => 'Visit CCR',
            'url' => '/',
            'submission_id' => 1003,
        ];
        $user->notify(new SubmissionCreation($notification_data_1));
        $user->notify(new SubmissionCreation($notification_data_2));
        $this->assertEquals(2, $user->notifications->count());
        $this->assertEquals("App\Notifications\SubmissionCreation", $user->notifications->first()->type);
        $this->assertEquals("App\Notifications\SubmissionCreation", $user->notifications->last()->type);
        $this->assertEquals($notification_data_1, $user->notifications->first()->data);
        $this->assertEquals($notification_data_2, $user->notifications->last()->data);
    }

    /**
     * @return void
     */
    public function testMultipleSubmissionCreationNotificationsForMultipleUsers()
    {
        $users = User::factory()->count(4)->create();
        $notification_data_1 = [
            'body' => 'A submission has been created.',
            'action' => 'Visit CCR',
            'url' => '/',
            'submission_id' => 1004,
        ];
        $notification_data_2 = [
            'body' => 'A submission has been created.',
            'action' => 'Visit CCR',
            'url' => '/',
            'submission_id' => 1005,
        ];
        Notification::send($users, new SubmissionCreation($notification_data_1));
        Notification::send($users, new SubmissionCreation($notification_data_2));
        $users->map(function ($user) use ($notification_data_1, $notification_data_2) {
            $this->assertEquals(2, $user->notifications->count());
            $this->assertEquals("App\Notifications\SubmissionCreation", $user->notifications->first()->type);
            $this->assertEquals("App\Notifications\SubmissionCreation", $user->notifications->last()->type);
            $this->assertEquals($notification_data_1, $user->notifications->first()->data);
            $this->assertEquals($notification_data_2, $user->notifications->last()->data);
        });
    }

    /**
     * @return void
     */
    public function testMarkingASubmissionCreationNotificationAsReadForAnIndividualUser()
    {
        $user = User::factory()->create();
        $notification_data = [
            'body' => 'A submission has been created.',
            'action' => 'Visit CCR',
            'url' => '/',
            'submission_id' => 1006,
        ];
        $user->notify(new SubmissionCreation($notification_data));
        $notification = $user->notifications->first();
        $notification->markAsRead();
        $this->assertEquals(0, $user->unreadNotifications->count());
    }

    /**
     * @return void
     */
    public function testMarkingMultipleSubmissionCreationNotificationsAsReadForMultipleUsers()
    {
        $users = User::factory()->count(4)->create();
        $notification_data_1 = [
            'body' => 'A submission has been created.',
            'action' => 'Visit CCR',
            'url' => '/',
            'submission_id' => 1007,
        ];
        $notification_data_2 = [
            'body' => 'A submission has been created.',
            'action' => 'Visit CCR',
            'url' => '/',
            'submission_id' => 1008,
        ];
        Notification::send($users, new SubmissionCreation($notification_data_1));
        Notification::send($users, new SubmissionCreation($notification_data_2));

        $users->map(function ($user) {
            $user->notifications->map(function ($notification) {
                $notification->markAsRead();
            });
            $this->assertEquals(0, $user->unreadNotifications->count());
        });
    }
}
