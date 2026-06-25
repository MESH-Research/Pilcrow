<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Enums\ModerationFlag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\ApiTestCase;

class UserAvatarMutationTest extends ApiTestCase
{
    use RefreshDatabase;

    private const UPLOAD_MUTATION = '
        mutation uploadUserAvatar($id: ID!, $avatar: Upload!) {
            uploadUserAvatar(id: $id, avatar: $avatar) {
                id
                avatar {
                    url
                    thumb_url
                    medium_url
                }
            }
        }
    ';

    private const DELETE_MUTATION = '
        mutation deleteUserAvatar($id: ID!) {
            deleteUserAvatar(id: $id) {
                id
                avatar { url }
            }
        }
    ';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('media-library.disk_name'));
    }

    public function testUserCanUploadOwnAvatar(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.png', 400, 400);

        $response = $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $user->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => $file]
        );

        $response->assertJsonPath('data.uploadUserAvatar.id', (string)$user->id);
        $response->assertJsonPath('data.uploadUserAvatar.avatar.url', fn($url) => str_contains($url, 'avatar.png'));

        $this->assertNotNull($user->fresh()->getAvatarMedia());
    }

    public function testUploadingNewAvatarReplacesOld(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $first = UploadedFile::fake()->image('first.png', 400, 400);
        $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $user->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => $first]
        )->assertJsonPath('data.uploadUserAvatar.id', (string)$user->id);

        $second = UploadedFile::fake()->image('second.png', 400, 400);
        $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $user->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => $second]
        )->assertJsonPath('data.uploadUserAvatar.id', (string)$user->id);

        $user = $user->fresh();
        $this->assertCount(1, $user->getMedia(User::AVATAR_COLLECTION));
    }

    public function testUserCanDeleteOwnAvatar(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.png', 400, 400);
        $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $user->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => $file]
        );

        $this->assertNotNull($user->fresh()->getAvatarMedia());

        $response = $this->graphQL(self::DELETE_MUTATION, ['id' => $user->id]);
        $response->assertJsonPath('data.deleteUserAvatar.avatar', null);

        $this->assertNull($user->fresh()->getAvatarMedia());
    }

    public function testUnauthenticatedUserCannotUpload(): void
    {
        $user = User::factory()->create();

        $response = $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $user->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => UploadedFile::fake()->image('avatar.png', 400, 400)]
        );

        $response->assertJsonPath('data.uploadUserAvatar', null);
    }

    public function testUserCannotUploadAvatarForAnother(): void
    {
        /** @var User $me */
        $me = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($me);

        $response = $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $other->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => UploadedFile::fake()->image('avatar.png', 400, 400)]
        );

        $response->assertJsonPath('data.uploadUserAvatar', null);
        $this->assertNull($other->fresh()->getAvatarMedia());
    }

    public function testAppAdminCanUploadForAnother(): void
    {
        $this->beAppAdmin();
        $other = User::factory()->create();

        $response = $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $other->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => UploadedFile::fake()->image('avatar.png', 400, 400)]
        );

        $response->assertJsonPath('data.uploadUserAvatar.id', (string)$other->id);
        $this->assertNotNull($other->fresh()->getAvatarMedia());
    }

    public function testDisallowsNonImageMimeType(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('hack.pdf', 100, 'application/pdf');

        $response = $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $user->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => $file]
        );

        $this->assertNotEmpty($response->json('errors'));
        $response->assertJsonPath('data.uploadUserAvatar', null);
        $this->assertNull($user->fresh()->getAvatarMedia());
    }

    public function testDisallowsOversizedFile(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // 6 MB image exceeds the 5 MB limit
        $file = UploadedFile::fake()->create('huge.png', 6 * 1024, 'image/png');

        $response = $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $user->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => $file]
        );

        $this->assertNotEmpty($response->json('errors'));
        $response->assertJsonPath('data.uploadUserAvatar', null);
        $this->assertNull($user->fresh()->getAvatarMedia());
    }

    public function testBlockedUserCannotUpload(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->setModerationFlag(ModerationFlag::AvatarUploadBlocked);
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.png', 400, 400);

        $response = $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $user->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => $file]
        );

        $this->assertNotEmpty($response->json('errors'));
        $response->assertJsonPath('data.uploadUserAvatar', null);
        $this->assertNull($user->fresh()->getAvatarMedia());
    }

    public function testAvatarUploadBlockedFieldReflectsPermission(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL(
            '{ currentUser { id avatar_upload_blocked } }'
        );
        $response->assertJsonPath('data.currentUser.avatar_upload_blocked', false);

        $user->setModerationFlag(ModerationFlag::AvatarUploadBlocked);

        $response = $this->graphQL(
            '{ currentUser { id avatar_upload_blocked } }'
        );
        $response->assertJsonPath('data.currentUser.avatar_upload_blocked', true);
    }

    public function testAvatarFieldIsNullWhenNoAvatarSet(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL(
            '{ currentUser { id avatar { url } } }'
        );

        $response->assertJsonPath('data.currentUser.avatar', null);
    }

    public function testUserCannotDeleteAnotherUsersAvatar(): void
    {
        /** @var User $me */
        $me = User::factory()->create();
        $other = User::factory()->create();

        // Pre-seed an avatar for the victim via direct medialibrary call
        // (bypasses the policy so we're only testing delete auth).
        $file = UploadedFile::fake()->image('avatar.png', 400, 400);
        $other->addMedia($file)
            ->usingFileName('avatar.png')
            ->toMediaCollection(User::AVATAR_COLLECTION);

        $this->actingAs($me);

        $response = $this->graphQL(self::DELETE_MUTATION, ['id' => $other->id]);

        $response->assertJsonPath('data.deleteUserAvatar', null);
        $this->assertNotNull(
            $other->fresh()->getAvatarMedia(),
            'victim avatar should survive an unauthorized delete'
        );
    }

    public function testUnauthenticatedCannotDeleteAvatar(): void
    {
        $other = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.png', 400, 400);
        $other->addMedia($file)
            ->usingFileName('avatar.png')
            ->toMediaCollection(User::AVATAR_COLLECTION);

        $response = $this->graphQL(self::DELETE_MUTATION, ['id' => $other->id]);

        $response->assertJsonPath('data.deleteUserAvatar', null);
        $this->assertNotNull($other->fresh()->getAvatarMedia());
    }

    public function testUploadedAvatarPathUsesUuidNotId(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.png', 400, 400);
        $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $user->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => $file]
        );

        $media = $user->fresh()->getAvatarMedia();
        $this->assertNotNull($media);
        $this->assertNotEmpty($media->uuid);
        $this->assertStringContainsString($media->uuid, $media->getPath());
        $this->assertStringNotContainsString("/{$media->id}/", $media->getPath());
    }
}
