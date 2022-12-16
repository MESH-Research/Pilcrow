<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Exceptions\ClientException;
use App\Models\SubmissionInvitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class VerifySubmissionInvite
{
    /**
     * Verify the validity of a submission invitation based on supplied arguments
     *
     * @param  null  $_
     * @param  array{uuid: string, token: string, expires:int|string}  $args
     * @return \App\Models\User
     * @throws \App\Exceptions\ClientException
     */
    public function verify($_, array $args): User
    {
        $invite = $this->processArgs($args);
        return $invite->invitee;
    }

    /**
     * Based on a supplied submission invitation uuid, token, and expiration, check that:
     * - a submission invitation for the supplied uuid exists
     * - the submission invitation hasn't already been accepted
     * - the submission invitation is not expired
     * - the token is not invalid
     *
     * @param  array{uuid: string, token: string, expires:int|string}  $args
     * @return \App\Models\SubmissionInvitation
     * @throws \App\Exceptions\ClientException
     */
    public function processArgs($args)
    {
        try {
            $invite = SubmissionInvitation::where('uuid', $args['uuid'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new ClientException('Not Found', 'invitationVerification', 'INVITATION_NOT_FOUND');
        }
        if ($invite->accepted_at && $invite->invitee->staged != true) {
            throw new ClientException('Already Accepted', 'invitationVerification', 'INVITATION_ALREADY_ACCEPTED');
        }
        $now = Carbon::now()->timestamp;
        if ($now > $args['expires']) {
            throw new ClientException('Token Expired', 'invitationVerification', 'INVITATION_TOKEN_EXPIRED');
        }
        if (!$invite->verifyToken($args['token'], $args['expires'])) {
            throw new ClientException('Invalid Token', 'invitationVerification', 'INVITATION_TOKEN_INVALID');
        }
        return $invite;
    }

}
