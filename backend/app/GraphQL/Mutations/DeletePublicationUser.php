<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\PublicationUser;
use Error;

class DeletePublicationUser
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     * @return \App\Models\PublicationUser
     */
    public function delete($_, array $args): PublicationUser
    {
        try {
            $publication_user = PublicationUser::where('user_id', $args['user_id'])
                ->where('role_id', $args['role_id'])
                ->where('publication_id', $args['publication_id'])->firstOrFail();
            $publication_user->forceDelete();

            return $publication_user;
        } catch (Error $error) {
            throw $error;
        }
    }
}
