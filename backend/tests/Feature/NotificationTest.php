<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SubmissionCreation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use MakesGraphQLRequests;
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
        $notification_1 = $user->notifications
            ->where('notifiable_type', "App\Models\User")
            ->where('notifiable_id', $user->id)
            ->where('data', $notification_data_1)
            ->first();
        $this->assertEquals($notification_data_1, $notification_1->data);
        $notification_2 = $user->notifications
            ->where('notifiable_type', "App\Models\User")
            ->where('notifiable_id', $user->id)
            ->where('data', $notification_data_2)
            ->first();
        $this->assertEquals($notification_data_2, $notification_2->data);
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
            $notification_1 = $user->notifications
                ->where('notifiable_type', "App\Models\User")
                ->where('notifiable_id', $user->id)
                ->where('data', $notification_data_1)
                ->first();
            $this->assertEquals($notification_data_1, $notification_1->data);
            $notification_2 = $user->notifications
                ->where('notifiable_type', "App\Models\User")
                ->where('notifiable_id', $user->id)
                ->where('data', $notification_data_2)
                ->first();
            $this->assertEquals($notification_data_2, $notification_2->data);
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

    /**
     * @return void
     */
    public function testNotificationsCanOnlyBeQueriedForOneself()
    {
        /** @var User $user_1 */
        $user_1 = User::factory()->create();
        $user_2 = User::factory()->create();
        $this->actingAs($user_1);
        $notification_data = [
            'body' => 'A submission has been created.',
            'action' => 'Visit CCR',
            'url' => '/',
            'submission_id' => 1000,
        ];
        $user_1->notify(new SubmissionCreation($notification_data));
        $user_2->notify(new SubmissionCreation($notification_data));
        $response = $this->graphQL(
            'query GetUsers {
                userSearch {
                    data {
                        id
                        notifications (first: 10, page: 1) {
                            data {
                                data {
                                    body
                                    submission_id
                                }
                            }
                        }
                    }
                }
            }'
        );
        $expected_data = [
            'data' => [
                'userSearch' => [
                    'data' => [
                        [
                            'id' => (string)$user_1->id,
                            'notifications' => [
                                'data' => [
                                    [
                                        'data' => [
                                            'body' => 'A submission has been created.',
                                            'submission_id' => '1000',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'id' => (string)$user_2->id,
                            'notifications' => [
                                'data' => [ ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $response->assertJson($expected_data);
    }
}
