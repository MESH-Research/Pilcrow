<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Permission;
use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Validator;
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

        if ($user->hasPermissionTo(Permission::AVATAR_UPLOAD_REVOKED)) {
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

        try {
            $user->addMedia($file->getRealPath())
                ->usingFileName('avatar.' . $file->getClientOriginalExtension())
                ->toMediaCollection(User::AVATAR_COLLECTION);
        } catch (FileCannotBeAdded $e) {
            throw new Error('Unable to store avatar: ' . $e->getMessage());
        }

        return $user->refresh();
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
