<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Permission;
use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;

class UpdateUserAvatar
{
    /**
     * Max upload size in kilobytes (5 MB).
     */
    private const MAX_SIZE_KB = 5 * 1024;

    /**
     * Upload or replace a user's avatar.
     *
     * @param array{id: string, avatar: \Illuminate\Http\UploadedFile} $args
     */
    public function upload(null $_, array $args): User
    {
        $user = User::findOrFail($args['id']);

        if (!$user->hasPermissionTo(Permission::UPLOAD_AVATAR)) {
            throw new Error('This user is not permitted to upload an avatar.');
        }

        $file = $args['avatar'];

        Validator::make(
            ['avatar' => $file],
            ['avatar' => [
                'required',
                'file',
                'mimetypes:' . implode(',', User::AVATAR_MIME_TYPES),
                'max:' . self::MAX_SIZE_KB,
            ]]
        )->validate();

        // Re-encode the image to strip EXIF (which may contain GPS
        // coordinates or device info) and anything a crafted file might
        // carry beyond pixel data.
        $ext = strtolower($file->getClientOriginalExtension());
        $cleanPath = $this->stripMetadata($file->getRealPath(), $ext);

        try {
            $user->addMedia($cleanPath)
                ->usingFileName("avatar.{$ext}")
                ->toMediaCollection(User::AVATAR_COLLECTION);
        } catch (FileCannotBeAdded $e) {
            throw new Error('Unable to store avatar: ' . $e->getMessage());
        } finally {
            if (file_exists($cleanPath)) {
                unlink($cleanPath);
            }
        }

        return $user->refresh();
    }

    /**
     * Decode → re-encode an image using intervention/image, which drops
     * EXIF and any ancillary metadata. Returns a path to a temp file the
     * caller must clean up.
     */
    private function stripMetadata(string $sourcePath, string $extension): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($sourcePath);

        $encoded = match ($extension) {
            'jpg', 'jpeg' => $image->toJpeg(90),
            'webp' => $image->toWebp(90),
            default => $image->toPng(),
        };

        $tempPath = tempnam(sys_get_temp_dir(), 'avatar_');
        file_put_contents($tempPath, (string)$encoded);

        return $tempPath;
    }

    /**
     * Remove a user's avatar.
     *
     * @param array{id: string} $args
     */
    public function delete(null $_, array $args): User
    {
        $user = User::findOrFail($args['id']);
        $user->clearMediaCollection(User::AVATAR_COLLECTION);

        return $user->refresh();
    }
}
