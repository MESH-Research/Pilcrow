<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;

final class AcceptSubmissionInvite
{
    /**
     * Accept an invitation to a submission for an invitee and update their user details
     *
     * @param  null  $_
     * @param  array{uuid: string, token: string, expires:int|string, user:array}  $args
     * @return \App\Models\User
     * @throws \App\Exceptions\ClientException
     */
    public function accept($_, $args): User
    {
        $verify = new VerifySubmissionInvite();
        $invite = $verify->processArgs($args);
        $user_details = [
            'name' => $args['name'],
            'username' => $args['username'],
            'password' => $args['password'],
        ];
        return $invite->acceptInvite($user_details);
    }
}
