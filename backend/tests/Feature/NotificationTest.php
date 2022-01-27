<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SubmissionCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * @param User $user
     * @param int $submission_id
     * @return array
     */
    private function getSampleNotificationData(User $user, $submission_id = 1000)
    {
        return [
            'submission' => [
                'id' => $submission_id,
                'title' => 'Test Submission from PHPUnit',
            ],
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
            ],
            'publication' => [
                'id' => 1,
                'name' => 'Test Publication from PHPUnit',
            ],
            'type' => 'submission.created',
            'action' => 'Visit CCR',
            'url' => '/',
            'body' => 'A submission has been created.',
        ];
    }

    /**
     * @return void
     */
    public function testSubmissionCreatedNotificationForAnIndividualUser()
    {
        $user = User::factory()->create();
        $notification_data = $this->getSampleNotificationData($user);
        $user->notify(new SubmissionCreated($notification_data));
        $this->assertEquals(1, $user->notifications->count());
        $this->assertEquals("App\Notifications\SubmissionCreated", $user->notifications->first()->type);
        $this->assertEquals($notification_data, $user->notifications->first()->data);
    }

    /**
     * @return void
     */
    public function testSubmissionCreatedNotificationForMultipleUsers()
    {
        $users = User::factory()->count(4)->create();
        $notification_data = $this->getSampleNotificationData($users->first());
        Notification::send($users, new SubmissionCreated($notification_data));
        $users->map(function ($user) use ($notification_data) {
            $this->assertEquals(1, $user->notifications->count());
            $this->assertEquals("App\Notifications\SubmissionCreated", $user->notifications->first()->type);
            $this->assertEquals($notification_data, $user->notifications->first()->data);
        });
    }

    /**
     * @return void
     */
    public function testMultipleSubmissionCreatedNotificationsForAnIndividualUser()
    {
        $user = User::factory()->create();
        $notification_data_1 = $this->getSampleNotificationData($user, 1002);
        $notification_data_2 = $this->getSampleNotificationData($user, 1003);
        $user->notify(new SubmissionCreated($notification_data_1));
        $user->notify(new SubmissionCreated($notification_data_2));
        $this->assertEquals(2, $user->notifications->count());
        $this->assertEquals("App\Notifications\SubmissionCreated", $user->notifications->first()->type);
        $this->assertEquals("App\Notifications\SubmissionCreated", $user->notifications->last()->type);
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
    public function testMultipleSubmissionCreatedNotificationsForMultipleUsers()
    {
        $users = User::factory()->count(4)->create();
        $notification_data_1 = $this->getSampleNotificationData($users->first(), 1004);
        $notification_data_2 = $this->getSampleNotificationData($users->first(), 1005);
        Notification::send($users, new SubmissionCreated($notification_data_1));
        Notification::send($users, new SubmissionCreated($notification_data_2));
        $users->map(function ($user) use ($notification_data_1, $notification_data_2) {
            $this->assertEquals(2, $user->notifications->count());
            $this->assertEquals("App\Notifications\SubmissionCreated", $user->notifications->first()->type);
            $this->assertEquals("App\Notifications\SubmissionCreated", $user->notifications->last()->type);
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
    public function testMarkingASubmissionCreatedNotificationAsReadForAnIndividualUser()
    {
        $user = User::factory()->create();
        $notification_data = $this->getSampleNotificationData($user, 1006);
        $user->notify(new SubmissionCreated($notification_data));
        $notification = $user->notifications->first();
        $notification->markAsRead();
        $this->assertEquals(0, $user->unreadNotifications->count());
    }

    /**
     * @return void
     */
    public function testMarkingMultipleSubmissionCreatedNotificationsAsReadForMultipleUsers()
    {
        $users = User::factory()->count(4)->create();
        $notification_data_1 = $this->getSampleNotificationData($users->first(), 1007);
        $notification_data_2 = $this->getSampleNotificationData($users->first(), 1008);
        Notification::send($users, new SubmissionCreated($notification_data_1));
        Notification::send($users, new SubmissionCreated($notification_data_2));

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
        $notification_data = $this->getSampleNotificationData($user_1, 1009);
        $user_1->notify(new SubmissionCreated($notification_data));
        $user_2->notify(new SubmissionCreated($notification_data));
        $response = $this->graphQL(
            'query GetUsers {
                userSearch {
                    data {
                        id
                        notifications (first: 10, page: 1) {
                            data {
                                data {
                                    type
                                    user {
                                        id
                                    }
                                    submission {
                                        id
                                    }
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
                                            'type' => 'submission.created',
                                            'user' => [
                                                'id' => (string)$user_1->id,
                                            ],
                                            'submission' => [
                                                'id' => '1009',
                                            ],
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

    /**
     * @return void
     */
    public function testMarkNotificationReadViaGraphqlEndpoint()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $notification_data = $this->getSampleNotificationData($user, 1010);
        $user->notify(new SubmissionCreated($notification_data));
        $this->graphQL(
            'mutation MarkNotificationRead ($notification_id: ID!) {
                markNotificationRead(id: $notification_id) {
                  read_at
                }
            }',
            [
                'notification_id' => $user->notifications->first()->id,
            ]
        );
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    /**
     * @return void
     */
    public function testMarkNotificationReadViaGraphqlEndpointIsProhibitedAsAnotherUser()
    {
        /** @var User $user_1 */
        $user_1 = User::factory()->create();
        $user_2 = User::factory()->create();
        $this->actingAs($user_1);
        $notification_data = $this->getSampleNotificationData($user_2, 1011);
        $user_2->notify(new SubmissionCreated($notification_data));
        $this->graphQL(
            'mutation MarkNotificationRead ($notification_id: ID!) {
                markNotificationRead(id: $notification_id) {
                  read_at
                }
            }',
            [
                'notification_id' => $user_2->notifications->first()->id,
            ]
        );
        $this->assertEquals(1, $user_2->unreadNotifications()->count());
    }

    /**
     * @return void
     */
    public function testMarkAllNotificationsReadViaGraphqlEndpoint()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);
        $notification_data_1 = $this->getSampleNotificationData($user, 1012);
        $user->notify(new SubmissionCreated($notification_data_1));
        $notification_data_2 = $this->getSampleNotificationData($user, 1013);
        $user->notify(new SubmissionCreated($notification_data_2));
        $this->graphQL(
            'mutation MarkAllNotificationsRead {
                markAllNotificationsRead
            }'
        );
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }

    /**
     * @return void
     */
    public function testMarkAllNotificationsReadViaGraphqlEndpointIsProhibitedAsAnotherUser()
    {
        /** @var User $user_1 */
        $user_1 = User::factory()->create();
        $user_2 = User::factory()->create();
        $this->actingAs($user_1);
        $notification_data_1 = $this->getSampleNotificationData($user_2, 1014);
        $user_2->notify(new SubmissionCreated($notification_data_1));
        $notification_data_2 = $this->getSampleNotificationData($user_2, 1015);
        $user_2->notify(new SubmissionCreated($notification_data_2));
        $this->graphQL(
            'mutation MarkAllNotificationsRead {
                markAllNotificationsRead
            }'
        );
        $this->assertEquals(2, $user_2->unreadNotifications()->count());
    }
}
