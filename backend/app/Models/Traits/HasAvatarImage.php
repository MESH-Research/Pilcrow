<?php
declare(strict_types=1);

namespace App\Models\Traits;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * The generic mechanics of a model that has a single uploaded avatar image:
 * the media-library collection, its thumb/medium conversions, and the
 * `getAvatar()` URL bundle the GraphQL `Avatar` type consumes.
 *
 * This is deliberately just the image plumbing — NOT moderation. Who may upload
 * (the `uploadAvatar` gate), the upload-blocked flag, and the report/snapshot
 * system are a separate, user-specific concern and stay on the model.
 *
 * Reusable by any HasMedia model (e.g. a future publication icon). The
 * collection registration is split into a helper so it COMPOSES with a model's
 * other collections rather than owning `registerMediaCollections()`: the model
 * calls {@see registerAvatarMediaCollection()} from its own hook. Override
 * {@see avatarCollectionName()} / {@see avatarMimeTypes()} to reuse the
 * mechanics under a different collection (e.g. an "icon").
 *
 * The consuming model MUST use Spatie's InteractsWithMedia.
 *
 * @mixin \Spatie\MediaLibrary\InteractsWithMedia
 */
trait HasAvatarImage
{
    public const AVATAR_COLLECTION = 'avatar';
    public const AVATAR_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * The media-library collection the avatar lives in. Override to reuse the
     * mechanics under a different name.
     */
    protected function avatarCollectionName(): string
    {
        return static::AVATAR_COLLECTION;
    }

    /**
     * Mime types the avatar collection accepts.
     *
     * @return array<int, string>
     */
    protected function avatarMimeTypes(): array
    {
        return static::AVATAR_MIME_TYPES;
    }

    /**
     * Register the single-file avatar collection. Call from the model's
     * registerMediaCollections() so it composes with any other collections.
     */
    public function registerAvatarMediaCollection(): void
    {
        $this->addMediaCollection($this->avatarCollectionName())
            ->singleFile()
            ->acceptsMimeTypes($this->avatarMimeTypes());
    }

    /**
     * Register the avatar conversions. Call from the model's
     * registerMediaConversions().
     *
     * The 'thumb' conversion is used by AvatarImage; 'medium' is available for
     * larger displays. Conversions are left queueable so they run on a queue
     * worker when one is configured, falling back to inline under the sync
     * driver.
     */
    public function registerAvatarMediaConversions(): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 96, 96);

        $this->addMediaConversion('medium')
            ->fit(Fit::Crop, 256, 256);
    }

    /**
     * The avatar Media item if set.
     */
    public function getAvatarMedia(): ?Media
    {
        return $this->getFirstMedia($this->avatarCollectionName());
    }

    /**
     * The avatar as URLs for the original plus the thumb/medium conversions, or
     * null when none is uploaded so the client can fall back to a generated
     * placeholder. Resolves the GraphQL `Avatar` field.
     *
     * @return array<string, string>|null
     */
    public function getAvatar(): ?array
    {
        $media = $this->getAvatarMedia();
        if ($media === null) {
            return null;
        }

        return [
            'url' => $media->getFullUrl(),
            'thumb_url' => $this->avatarConversionUrl($media, 'thumb'),
            'medium_url' => $this->avatarConversionUrl($media, 'medium'),
        ];
    }

    /**
     * URL for a media conversion, falling back to the original file's URL while
     * the conversion has not been generated yet. Conversions may be queued, so
     * right after upload the derivatives can be momentarily absent; serving the
     * original avoids a broken image until the queue worker catches up.
     */
    private function avatarConversionUrl(Media $media, string $conversion): string
    {
        if (!$media->hasGeneratedConversion($conversion)) {
            return $media->getFullUrl();
        }

        return $media->getFullUrl($conversion);
    }
}
