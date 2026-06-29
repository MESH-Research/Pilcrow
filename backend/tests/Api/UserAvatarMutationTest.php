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

    public function testUploadsAndReencodesAJpeg(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // A jpeg exercises the toJpeg() re-encode arm (the EXIF-strip path is
        // format-specific). The original carries metadata; what we store must be
        // the re-encoded copy.
        $file = UploadedFile::fake()->image('avatar.jpg', 400, 400);

        $response = $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $user->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => $file]
        );

        $response->assertJsonPath('data.uploadUserAvatar.id', (string)$user->id);
        $media = $user->fresh()->getAvatarMedia();
        $this->assertNotNull($media);
        $this->assertSame('avatar.jpg', $media->file_name);
    }

    public function testUploadsAndReencodesAWebp(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // Covers the toWebp() re-encode arm.
        $file = UploadedFile::fake()->image('avatar.webp', 400, 400);

        $response = $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $user->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => $file]
        );

        $response->assertJsonPath('data.uploadUserAvatar.id', (string)$user->id);
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

    public function testAppAdminCannotUploadForAnother(): void
    {
        // Uploading an avatar is self-service only. Moderators clear avatars,
        // they never replace them — so even an app admin cannot upload for
        // another user. The uploadAvatar gate has no moderator disjunct.
        $this->beAppAdmin();
        $other = User::factory()->create();

        $response = $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $other->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => UploadedFile::fake()->image('avatar.png', 400, 400)]
        );

        $response->assertJsonPath('data.uploadUserAvatar', null);
        $this->assertNull($other->fresh()->getAvatarMedia());
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

    public function testRejectsImageExceedingPixelCap(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // A few bytes on disk but its PNG header declares a 6000×5000 (30 MP)
        // raster — exactly the decompression bomb the pre-decode cap exists to
        // stop. Hand-crafted (not UploadedFile::fake()->image, which would
        // allocate the 30 MP raster in this test process).
        $ihdr = pack('N', 6000) . pack('N', 5000) . "\x08\x02\x00\x00\x00";
        $chunk = pack('N', 13) . 'IHDR' . $ihdr . pack('N', crc32('IHDR' . $ihdr));
        $path = tempnam(sys_get_temp_dir(), 'bomb');
        file_put_contents($path, "\x89PNG\r\n\x1a\n" . $chunk);
        $file = new UploadedFile($path, 'bomb.png', 'image/png', null, true);

        $response = $this->multipartGraphQL(
            ['query' => self::UPLOAD_MUTATION, 'variables' => ['id' => $user->id, 'avatar' => null]],
            ['0' => ['variables.avatar']],
            ['0' => $file]
        );

        $this->assertNotEmpty($response->json('errors'));
        $response->assertJsonPath('data.uploadUserAvatar', null);
        $this->assertNull($user->fresh()->getAvatarMedia());
    }

    public function testCorruptImageReturnsCleanError(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        // Valid PNG signature (so it passes the mimetypes rule) but no image
        // data — GD can't decode it. Must surface a clean error, not a 500.
        $path = tempnam(sys_get_temp_dir(), 'corrupt');
        file_put_contents($path, "\x89PNG\r\n\x1a\n" . str_repeat("\0", 64));
        $file = new UploadedFile($path, 'corrupt.png', 'image/png', null, true);

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

    public function testCanUploadAvatarFieldReflectsGate(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL('{ currentUser { id can_upload_avatar } }');
        $response->assertJsonPath('data.currentUser.can_upload_avatar', true);

        $user->setModerationFlag(ModerationFlag::AvatarUploadBlocked);

        $response = $this->graphQL('{ currentUser { id can_upload_avatar } }');
        $response->assertJsonPath('data.currentUser.can_upload_avatar', false);
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

    public function testModeratorCanClearABlockedUsersAvatar(): void
    {
        // The deleteAvatar gate admits an avatar moderator (not just the
        // owner), so an admin can take down another user's avatar — even one
        // already blocked from re-uploading.
        $other = User::factory()->create();
        $other->setModerationFlag(ModerationFlag::AvatarUploadBlocked);
        $file = UploadedFile::fake()->image('avatar.png', 400, 400);
        $other->addMedia($file)
            ->usingFileName('avatar.png')
            ->toMediaCollection(User::AVATAR_COLLECTION);

        $this->beAppAdmin();

        $response = $this->graphQL(self::DELETE_MUTATION, ['id' => $other->id]);

        $response->assertJsonPath('data.deleteUserAvatar.id', (string)$other->id);
        $this->assertNull(
            $other->fresh()->getAvatarMedia(),
            'a moderator should be able to clear another user\'s avatar'
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
