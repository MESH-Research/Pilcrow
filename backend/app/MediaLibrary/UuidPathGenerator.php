<?php
declare(strict_types=1);

namespace App\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Stores media items under their stable UUID rather than the sequential
 * `media.id`. Non-enumerable URLs reduce the blast radius of a harvester
 * that tries to iterate `/storage/1/...`, `/storage/2/...` while still
 * serving the files statically and cache-friendly.
 *
 * Falls back to the integer id if a media row somehow lacks a UUID, so
 * pre-existing rows from an older install keep resolving instead of 404
 * ing. Call `php artisan media-library:regenerate --force` after a
 * filesystem migration if paths need to be rebuilt.
 */
class UuidPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->slug($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->slug($media) . '/conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->slug($media) . '/responsive-images/';
    }

    private function slug(Media $media): string
    {
        return $media->uuid ?: (string)$media->getKey();
    }
}
