<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Throwable;

class UpdateUserAvatar
{
    /**
     * Max upload size in kilobytes (5 MB).
     */
    private const MAX_SIZE_KB = 5 * 1024;

    /**
     * Max decoded raster size in pixels (width × height). The byte-size limit
     * bounds the compressed file, not what it decodes to — a tiny file can
     * expand to a gigapixel raster and exhaust memory ("decompression bomb").
     * 24 MP comfortably covers any real avatar source while blocking abuse.
     */
    private const MAX_PIXELS = 24_000_000;

    /**
     * Upload or replace a user's avatar.
     *
     * @param array{id: string, avatar: \Illuminate\Http\UploadedFile} $args
     */
    public function upload(null $_, array $args): User
    {
        // Authorization (owner AND not moderator-blocked) is enforced by the
        // uploadAvatar gate on the GraphQL field — see AuthServiceProvider.
        $user = User::findOrFail($args['id']);

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
        // Bound the decoded raster before handing the file to GD. getimagesize
        // reads only the header, so this rejects decompression bombs without
        // allocating the full image.
        $dimensions = $this->readImageSize($sourcePath);
        if ($dimensions === false) {
            throw new Error("We couldn't process this image. Please upload a different file.");
        }
        if ($dimensions[0] * $dimensions[1] > self::MAX_PIXELS) {
            throw new Error('This image is too large to process. Please upload one with smaller dimensions.');
        }

        $manager = new ImageManager(new Driver());

        // Decoding is where a crafted-but-valid-mimetype file can blow up
        // (corrupt stream, unsupported variant). Catch it and surface a clean
        // error instead of a raw 500.
        try {
            $image = $manager->read($sourcePath);
        } catch (Throwable $e) {
            throw new Error("We couldn't process this image. Please upload a different file.");
        }

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
     * Read an image's pixel dimensions from its header only (no full decode),
     * swallowing the warning getimagesize emits on an unreadable/corrupt file
     * so the caller just sees a false return.
     *
     * @return array<int, int>|false
     */
    private function readImageSize(string $sourcePath): array|false
    {
        set_error_handler(static fn(): bool => true);

        try {
            return getimagesize($sourcePath);
        } finally {
            restore_error_handler();
        }
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
