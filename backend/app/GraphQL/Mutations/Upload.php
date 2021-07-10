<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

class Upload
{
    /**
     * Upload a file, store it on the server and return the path.
     *
     * @param  mixed  $_
     * @param  array<string, mixed>  $args
     * @return string|null
     */
    public function __invoke($_, array $args): ?string
    {
        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $args['file'];

        return $file->storePublicly('uploads');
    }
}
